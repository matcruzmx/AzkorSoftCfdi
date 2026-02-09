<?php
declare(strict_types=1);

namespace AzkorSoft\Cfdi\PAC\Providers;

use AzkorSoft\Cfdi\PAC\PacInterface;
use AzkorSoft\Cfdi\PAC\TimbradoResult;
use AzkorSoft\Cfdi\Exceptions\CfdiException;
use AzkorSoft\Cfdi\Utils\Logger;
use Matcruz\AzkorSoft\Helpers\RegisterLogs;


final class SwPac implements PacInterface
{
	private string $url;
    private ?string $token;
    private ?string $user;
    private ?string $password;

    public function __construct(
        string $url,
        ?string $token = null,
        ?string $user = null,
        ?string $password = null,
        bool $produccion = false
    ) {
        $this->url = $url;
        $this->token = $token;
        $this->user = $user;
        $this->password = $password;

        if (!$token && (!$user || !$password)) {
            throw new CfdiException(
                'Debe proporcionar token o usuario y contraseña para SW'
            );
        }
    }



    /**
     * Obtiene token si no existe
     */
    private function authenticate(): void
    {
        if ($this->token !== null) {
            return;
        }

        RegisterLogs::logCFDi('Autenticando con SW (usuario/contraseña)');

        $endpoint = $this->url . '/v2/security/authenticate';

        $payload = json_encode([
            'user'     => $this->user,
            'password' => $this->password
        ]);

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            RegisterLogs::logCFDi('Error CURL autenticando SW', [
                'error' => $curlError
            ]);
            throw new CfdiException(
                'Error de conexión al autenticar con SW'
            );
        }

        $data = json_decode($response, true);

        if (!isset($data['status']) || $data['status'] !== 'success') {
            RegisterLogs::logCFDi('Error autenticando con SW', [
                'response' => $data
            ]);
            throw new CfdiException(
                $data['message'] ?? 'No se pudo autenticar con SW'
            );
        }

        $this->token = $data['data']['token'];

        RegisterLogs::logCFDi('Token SW obtenido correctamente');
    }

	public function timbrar(string $xml): TimbradoResult
	{
		$this->authenticate();

		RegisterLogs::logCFDi('Iniciando timbrado CFDI con SW');

		$endpoint = $this->url . '/cfdi33/stamp/v4';

		// Crear archivo temporal
		$tmp = tempnam(sys_get_temp_dir(), 'cfdi_');
		file_put_contents($tmp, $xml);


		RegisterLogs::logCFDi($tmp);

		$postFields = [
			'xml' => new \CURLFile(
				$tmp,
				'application/xml',
				'cfdi.xml'
			)
		];

		$ch = curl_init($endpoint);
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $postFields,
			CURLOPT_HTTPHEADER => [
				'Authorization: Bearer ' . $this->token
				// ⚠️ NO pongas Content-Type
			]
		]);

		$response = curl_exec($ch);
		$curlError = curl_error($ch);
		curl_close($ch);

		unlink($tmp);

		if ($curlError) {
			RegisterLogs::logCFDi('Error CURL en timbrado', ['error' => $curlError]);
			throw new CfdiException('Error de conexión con SW: ' . $curlError);
		}

		$data = json_decode($response, true);

		if (!isset($data['status']) || $data['status'] !== 'success') {
			RegisterLogs::logCFDi('Error al timbrar CFDI', ['response' => $data]);
			throw new CfdiException(
				$data['message'] ?? 'Error al timbrar CFDI'
			);
		}

		RegisterLogs::logCFDi('CFDI timbrado correctamente', [
			'uuid' => $data['data']['uuid']
		]);
		RegisterLogs::logCFDi('CFDI timbrado correctamente', [
			'timbradoResult' => $data
		]);
		RegisterLogs::logCFDi('XML', [
			'data cfdi' => $data['data']['cfdi']
		]);

		return new TimbradoResult(
			uuid: $data['data']['uuid'],
			xmlTimbrado: $data['data']['cfdi'], 
			fechaTimbrado: $data['data']['fechaTimbrado'],
			selloSat: $data['data']['selloSAT'],
			noCertificadoSat: $data['data']['noCertificadoSAT']
		);
	}

   
}


