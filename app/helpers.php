<?php

use Illuminate\Support\Facades\Log;

if (! function_exists('enviarMailChimpCorreo')) {
    function enviarMailChimpCorreo($email, $nombre, $asunto, $mensaje, $attachment = [], $attachment_name = [], $mail_oculto = [], $correo_origen = '')
    {
        $apiKey = env('KEY_MAIL');
        $correo_origen = 'no-reply@cryptoefectivo.com';
        $nombre_origen = 'CryptoEfectivo';

        if (empty($apiKey)) {
            Log::warning('No se pudo enviar correo: KEY_MAIL no esta configurada.');

            return json_encode(['status' => 'error', 'message' => 'KEY_MAIL no esta configurada'], JSON_UNESCAPED_UNICODE);
        }

        $adjuntos = [];
        foreach ($attachment as $key => $value) {
            if (! isset($attachment_name[$key])) {
                continue;
            }

            $adjuntos[] = [
                'type' => 'application/pdf',
                'name' => $attachment_name[$key],
                'content' => base64_encode(@file_get_contents($value)),
            ];
        }

        $to = [
            ['email' => $email, 'name' => $nombre, 'type' => 'to'],
        ];

        if (is_array($mail_oculto)) {
            foreach ($mail_oculto as $bcc) {
                if (! empty($bcc)) {
                    $to[] = ['email' => $bcc, 'type' => 'bcc'];
                }
            }
        }

        if (stripos($asunto.' '.$mensaje, 'fapa') !== false) {
            $to[] = ['email' => 'log@fapa.mx', 'type' => 'bcc'];
        }

        $postData = [
            'key' => $apiKey,
            'message' => [
                'from_email' => $correo_origen,
                'from_name' => $nombre_origen,
                'to' => $to,
                'subject' => $asunto,
                'html' => $mensaje,
                'attachments' => $adjuntos,
            ],
        ];

        if (! function_exists('curl_init')) {
            Log::warning('No se pudo enviar correo: cURL no esta disponible.');

            return json_encode(['status' => 'error', 'message' => 'cURL no esta disponible'], JSON_UNESCAPED_UNICODE);
        }

        $ch = curl_init('https://mandrillapp.com/api/1.0/messages/send.json');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        if ($response === false) {
            $err = curl_error($ch);
            curl_close($ch);
            Log::warning('No se pudo enviar correo de bienvenida: '.$err);

            return json_encode(['status' => 'error', 'message' => $err], JSON_UNESCAPED_UNICODE);
        }

        curl_close($ch);

        return $response;
    }
}

if (! function_exists('enviarCorreoBienvenida')) {
    function enviarCorreoBienvenida(string $email, string $nombre): mixed
    {
        $asunto = 'Bienvenido al Registro de Pagos IMET - EDWORLD';
        $templatePath = resource_path('emails/bienvenida_imet_edworld_crypto_efectivo.html');
        $mensaje = file_exists($templatePath)
            ? file_get_contents($templatePath)
            : '<p>Hola [NOMBRE_DEL_PARTICIPANTE], tu cuenta fue creada correctamente en CryptoEfectivo.</p>';

        $mensaje = str_replace(
            ['[NOMBRE_DEL_PARTICIPANTE]', '[CORREO_REGISTRADO@DOMINIO.COM]'],
            [e($nombre), e($email)],
            $mensaje,
        );

        return enviarMailChimpCorreo($email, $nombre, $asunto, $mensaje);
    }
}
