<?php
declare(strict_types=1);

namespace AzkorSoft\Cfdi\PAC\Providers;

use AzkorSoft\Cfdi\PAC\PacInterface;
use AzkorSoft\Cfdi\PAC\TimbradoResult;
use AzkorSoft\Cfdi\Exceptions\CfdiException;
use AzkorSoft\Cfdi\Utils\Logger;

final class SwPac implements PacInterface
{
    private string $url;
    private ?string $token = null;

    private ?string $user = null;
    private ?string $password = null;

    public function __construct(
        ?string $token = null,
        ?string $user = null,
        ?string $password = null,
        bool $produccion = false
    ) {
        $this->url = $produccion
            ? 'https://services.sw.com.mx'
            : 'https://services.test.sw.com.mx';

        if ($token) {
            $this->token = $token;
        } elseif ($user && $password) {
            $this->user = $user;
            $this->password = $password;
        } else {
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

        Logger::info('Autenticando con SW (usuario/contraseña)');

        $endpoint = $this->url . '/security/authenticate';

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
            Logger::error('Error CURL autenticando SW', [
                'error' => $curlError
            ]);
            throw new CfdiException(
                'Error de conexión al autenticar con SW'
            );
        }

        $data = json_decode($response, true);

        if (!isset($data['status']) || $data['status'] !== 'success') {
            Logger::error('Error autenticando con SW', [
                'response' => $data
            ]);
            throw new CfdiException(
                $data['message'] ?? 'No se pudo autenticar con SW'
            );
        }

        $this->token = $data['data']['token'];

        Logger::info('Token SW obtenido correctamente');
    }

    public function timbrar(string $xml): TimbradoResult
    {
        $this->authenticate();

        Logger::info('Iniciando timbrado CFDI con SW');

        $endpoint = $this->url . '/cfdi33/timbrar/v4';

        $payload = json_encode([
            'xml' => base64_encode($xml)
        ]);

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->token,
                'Content-Type: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            Logger::error('Error CURL en timbrado', [
                'error' => $curlError
            ]);
            throw new CfdiException(
                'Error de conexión con SW: ' . $curlError
            );
        }

        $data = json_decode($response, true);

        if (!isset($data['status']) || $data['status'] !== 'success') {
            Logger::error('Error al timbrar CFDI', [
                'response' => $data
            ]);
            throw new CfdiException(
                $data['message'] ?? 'Error al timbrar CFDI'
            );
        }

        Logger::info('CFDI timbrado correctamente', [
            'uuid' => $data['data']['uuid']
        ]);

        return new TimbradoResult(
            uuid: $data['data']['uuid'],
            xmlTimbrado: base64_decode($data['data']['cfdi']),
            fechaTimbrado: $data['data']['fechaTimbrado'],
            selloSat: $data['data']['selloSAT'],
            noCertificadoSat: $data['data']['noCertificadoSAT']
        );
    }
}




/*
declare(strict_types=1);

namespace AzkorSoft\Cfdi\PAC\Providers;

use AzkorSoft\Cfdi\PAC\PacInterface;
use AzkorSoft\Cfdi\PAC\TimbradoResult;
use AzkorSoft\Cfdi\Exceptions\CfdiException;
use AzkorSoft\Cfdi\Utils\Logger;

final class SwPac implements PacInterface
{
    private string $url;
    private string $token;

    public function __construct(string $token, bool $produccion = false)
    {
        $this->token = $token;
        $this->url = $produccion
            ? 'https://services.sw.com.mx'
            : 'https://services.test.sw.com.mx';
    }

    public function timbrar(string $xml): TimbradoResult
    {
        Logger::info('Iniciando timbrado CFDI con SW');

        $endpoint = $this->url . '/cfdi33/timbrar/v4';

        $payload = json_encode([
            'xml' => base64_encode($xml)
        ]);

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->token,
                'Content-Type: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            Logger::error('Error CURL en timbrado', ['error' => $curlError]);
            throw new CfdiException('Error de conexión con SW: ' . $curlError);
        }

        $data = json_decode($response, true);

        if (!isset($data['status']) || $data['status'] !== 'success') {
            Logger::error('Error al timbrar CFDI', [
                'response' => $data
            ]);

            throw new CfdiException(
                $data['message'] ?? 'Error desconocido al timbrar CFDI'
            );
        }

        Logger::info('CFDI timbrado correctamente', [
            'uuid' => $data['data']['uuid']
        ]);

        return new TimbradoResult(
            uuid: $data['data']['uuid'],
            xmlTimbrado: base64_decode($data['data']['cfdi']),
            fechaTimbrado: $data['data']['fechaTimbrado'],
            selloSat: $data['data']['selloSAT'],
            noCertificadoSat: $data['data']['noCertificadoSAT']
        );
    }
}
*/


/**********
// declare(strict_types=1);

namespace AzkorSoft\Cfdi\PAC\Providers;

use AzkorSoft\Cfdi\PAC\PacInterface;
use AzkorSoft\Cfdi\PAC\TimbradoResult;
use RuntimeException;

final class SwPac implements PacInterface
{
    private string $url;
    private string $token;

    public function __construct(string $token, bool $produccion = false)
    {
        $this->token = $token;
        $this->url = $produccion
            ? 'https://services.sw.com.mx'
            : 'https://services.test.sw.com.mx';
    }

    public function timbrar(string $xml): TimbradoResult
    {
        $endpoint = $this->url . '/cfdi33/timbrar/v4';

        $payload = json_encode([
            'xml' => base64_encode($xml)
        ], JSON_THROW_ON_ERROR);

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->token,
                'Content-Type: application/json'
            ]
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            throw new RuntimeException(
                'Error CURL SW: ' . curl_error($ch)
            );
        }

        curl_close($ch);

        $data = json_decode($response, true);

        if (!isset($data['status']) || $data['status'] !== 'success') {
            throw new RuntimeException(
                $data['message'] ?? 'Error desconocido al timbrar CFDI'
            );
        }

        $timbrado = $data['data'];

        return new TimbradoResult(
            $timbrado['uuid'],
            base64_decode($timbrado['cfdi']),
            $timbrado['fechaTimbrado'],
            $timbrado['selloSAT'],
            $timbrado['noCertificadoSAT']
        );
    }
}
**/