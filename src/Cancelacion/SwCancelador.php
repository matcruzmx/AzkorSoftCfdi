<?php

namespace AzkorSoft\Cfdi\Cancelacion;

class SwCancelador
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

    public function cancelar(
        string $uuid,
        string $rfcEmisor,
        string $motivo,
        ?string $uuidSustitucion = null
    ): CancelacionResponse {
        if ($motivo === '01' && !$uuidSustitucion) {
            throw new \InvalidArgumentException(
                'El motivo 01 requiere UUID de sustitución'
            );
        }

        $endpoint = $this->url . '/cfdi33/cancel/v4';

        $payload = [
            'rfc' => $rfcEmisor,
            'uuids' => [[
                'uuid' => $uuid,
                'motivo' => $motivo,
                'folioSustitucion' => $uuidSustitucion
            ]]
        ];

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->token,
                'Content-Type: application/json'
            ]
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return new CancelacionResponse(false, mensaje: $error);
        }

        $data = json_decode($response, true);

        if (!isset($data['status']) || $data['status'] !== 'success') {
            return new CancelacionResponse(
                false,
                mensaje: $data['message'] ?? 'Error al cancelar CFDI'
            );
        }

        return new CancelacionResponse(
            true,
            base64_decode($data['data']['acuse'])
        );
    }
}
