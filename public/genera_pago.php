<?php
/*
 * Archivo: genera_pago_10hf.php
 * Descripcion:
 * Formulario para capturar datos de generador.payments y generar
 * un archivo descargable con extension .10hf que contiene un JSON.
 *
 * Compatible con PHP 5.4+
 */

date_default_timezone_set('America/Mexico_City');

function limpiar($valor) {
    return isset($valor) ? trim($valor) : '';
}

function h($valor) {
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

function generarIdPago() {
    /*
     * Genera un ID numerico automatico.
     * Formato basado en fecha/hora + numero aleatorio.
     */
    return (int)(date('YmdHis') . mt_rand(100, 999));
}

function generarHashUnico() {
    /*
     * Hash unico para identificar el pago.
     * Compatible con PHP 5.4.
     */
    $base = uniqid('', true) . '|' . mt_rand(100000, 999999) . '|' . microtime(true);
    return hash('sha256', $base);
}

function encriptar($cadena) {
    $clave = "Encr10h-.$=2023SecretoMuajaaja";
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encriptado = openssl_encrypt($cadena, 'aes-256-cbc', $clave, 0, $iv);

    if ($iv === false || $encriptado === false) {
        return false;
    }

    return base64_encode($iv . $encriptado);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount     = limpiar($_POST['amount']);
    $method     = limpiar($_POST['method']);
    $status     = limpiar($_POST['status']);
    $reference  = limpiar($_POST['reference']);
    $paid_at    = limpiar($_POST['paid_at']);
    $created_at = limpiar($_POST['created_at']);
    $updated_at = limpiar($_POST['updated_at']);

    /*
     * Los IDs ya no aparecen en el formulario.
     * Se generan automaticamente en el JSON.
     */
    $data = array(
        'id'         => generarIdPago(),
        'user_id'    => 0,
        'course_id'  => 0,
        'amount'     => ($amount === '') ? 0 : (float)$amount,
        'method'     => $method,
        'status'     => $status,
        'reference'  => $reference,
        'paid_at'    => $paid_at,
        'created_at' => $created_at,
        'updated_at' => $updated_at,
        'unica'      => generarHashUnico()
    );

    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    if ($json === false) {
        die('Error al generar el JSON.');
    }

    $contenidoArchivo = encriptar($json);

    if ($contenidoArchivo === false) {
        die('Error al encriptar el JSON.');
    }

    $nombreArchivo = 'payment_' . date('Ymd_His') . '.10hf';

    header('Content-Type: text/plain; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
    header('Content-Length: ' . strlen($contenidoArchivo));
    header('Pragma: no-cache');
    header('Expires: 0');

    echo $contenidoArchivo;
    exit;
}

$fechaActual = date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generar archivo .10hf de pago</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 30px;
        }

        .contenedor {
            max-width: 650px;
            margin: auto;
            background: #ffffff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.08);
        }

        h1 {
            margin-top: 0;
            font-size: 24px;
            color: #222;
        }

        .nota {
            background: #eef5ff;
            border-left: 4px solid #2f80ed;
            padding: 10px 12px;
            margin-bottom: 20px;
            color: #333;
            font-size: 14px;
        }

        label {
            display: block;
            margin-top: 14px;
            font-weight: bold;
            color: #333;
        }

        input, select {
            width: 100%;
            box-sizing: border-box;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        button {
            margin-top: 22px;
            width: 100%;
            background: #111827;
            color: #ffffff;
            border: none;
            padding: 12px;
            border-radius: 6px;
            font-size: 15px;
            cursor: pointer;
        }

        button:hover {
            background: #000000;
        }
    </style>
</head>
<body>

<div class="contenedor">
    <h1>Generar archivo de pago .10hf</h1>

    <div class="nota">
        Los campos <strong>id</strong>, <strong>user_id</strong>, <strong>course_id</strong> y <strong>unica</strong> no aparecen en el formulario.
        Se generan automaticamente dentro del JSON y el archivo se descarga encriptado.
    </div>

    <form method="post" action="">
        <label for="amount">Amount</label>
        <input type="number" step="0.01" name="amount" id="amount" placeholder="Ej. 1500.00" required>

        <label for="method">Method</label>
        <input type="text" name="method" id="method" placeholder="Ej. transferencia, efectivo, tarjeta" required>

        <label for="status">Status</label>
        <select name="status" id="status" required>
            <option value="">Selecciona un estatus</option>
            <option value="pending">pending</option>
            <option value="paid">paid</option>
            <option value="cancelled">cancelled</option>
            <option value="failed">failed</option>
        </select>

        <label for="reference">Reference</label>
        <input type="text" name="reference" id="reference" placeholder="Referencia del pago">

        <label for="paid_at">Paid At</label>
        <input type="text" name="paid_at" id="paid_at" value="<?php echo h($fechaActual); ?>" placeholder="YYYY-MM-DD HH:MM:SS">

        <label for="created_at">Created At</label>
        <input type="text" name="created_at" id="created_at" value="<?php echo h($fechaActual); ?>" placeholder="YYYY-MM-DD HH:MM:SS">

        <label for="updated_at">Updated At</label>
        <input type="text" name="updated_at" id="updated_at" value="<?php echo h($fechaActual); ?>" placeholder="YYYY-MM-DD HH:MM:SS">

        <button type="submit">Generar y descargar archivo .10hf</button>
    </form>
</div>

</body>
</html>
