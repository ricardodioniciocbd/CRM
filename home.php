<?php
date_default_timezone_set('America/Mexico_City');

// Función para enviar mensajes de WhatsApp
function enviarWhatsApp($token, $to, $body) {
    $params = array(
        'token' => $token,
        'to' => $to,
        'body' => $body
    );

    $ultramsgCurl = curl_init();
    curl_setopt_array($ultramsgCurl, array(
        CURLOPT_URL => "https://api.ultramsg.com/instance106245/messages/chat",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "", 
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => http_build_query($params),
        CURLOPT_HTTPHEADER => array(
            "content-type: application/x-www-form-urlencoded"
        ),
    ));

    $response = curl_exec($ultramsgCurl);
    $err = curl_error($ultramsgCurl);
    curl_close($ultramsgCurl);

    return [$err, $response];
}

// Archivos de control
$archivoLineas = 'lineas_procesadas.txt';
$resetFile = 'last_reset.txt';

// Reset diario a las 10:00 AM
$now = new DateTime();
$resetTime = (new DateTime())->setTime(10, 0);

if (file_exists($resetFile)) {
    $lastReset = DateTime::createFromFormat('Y-m-d H:i:s', file_get_contents($resetFile));
    if ($lastReset && $now >= $resetTime && $lastReset->format('Y-m-d') < $now->format('Y-m-d')) {
        file_put_contents($archivoLineas, '');
        file_put_contents($resetFile, $now->format('Y-m-d H:i:s'));
    }
} else {
    file_put_contents($resetFile, $now->format('Y-m-d H:i:s'));
}

// Leer líneas procesadas
$lineasProcesadas = file_exists($archivoLineas) 
    ? file($archivoLineas, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) 
    : [];

// Obtener todos los contratos
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://erp.plazashoppingcenter.store/htdocs/api/index.php/contracts",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['DOLAPIKEY: web123456789']
]);

echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Contratos Activos</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .contract-card {
            background: white;
            padding: 20px;
            margin: 10px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .status {
            padding: 5px 10px;
            border-radius: 5px;
            color: white;
        }
        .pagado { background: #4CAF50; }
        .pagar { background: #FF5722; }
        .no-pagado { background: #F44336; }
        .desconocido { background: #9E9E9E; }
        .error-msg { color: #D32F2F; margin-top: 5px; }
        .success-msg { color: #388E3C; margin-top: 5px; }
        .info-msg { color: #1976D2; margin-top: 5px; }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>Plaza Shopping Center</h2>
    <ul>
        <li><a href="home.php"><i class="fas fa-home"></i> Inicio</a></li>
        <li><a href="cambiar.php"><i class="fas fa-key"></i> Cambiar Contraseña</a></li>
        <li><a href="cerrar_sesion.php"><i class="fas fa-unlock"></i> Cerrar Sesión</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="header">
        <h1><i class="fas fa-chart-line"></i> Contratos Activos</h1>
    </div>
    <div class="contracts-container">';

if (($result = curl_exec($curl)) === false) {
    echo "<div class='error'>Error al obtener datos: " . curl_error($curl) . "</div>";
} else {
    $contracts = json_decode($result, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "<div class='error'>Error al decodificar JSON</div>";
    } else {
        foreach ($contracts as $data) {
            if ($data["nbofservicesopened"] >= 1) {
                $recentLine = null;
                
                // Buscar la última línea del contrato
                foreach ($data["lines"] as $line) {
                    $dateStart = DateTime::createFromFormat('U', $line["date_start"]);
                    $dateEnd = DateTime::createFromFormat('U', $line["date_end"]);
                    
                    if ($dateStart && $dateEnd) {
                        if (!$recentLine || $dateStart > $recentLine["dateStart"]) {
                            $recentLine = [
                                'id' => $line["id"],
                                'dateStart' => $dateStart,
                                'dateEnd' => $dateEnd
                            ];
                        }
                    }
                }
                
                if (!$recentLine) continue;

                // Calcular días restantes
                $hoy = new DateTime();
                $finContrato = clone $recentLine['dateEnd'];
                $finContrato->setTime(0, 0);
                $hoy->setTime(0, 0);
                
                $diferencia = $hoy->diff($finContrato);
                $diasRestantes = $diferencia->days * ($diferencia->invert ? -1 : 1);

                // Determinar mensaje
                $mensaje = null;
                if ($diasRestantes == 1) {
                    $mensaje = "Estimado cliente del {$data['ref_customer']}, le recordamos que su renta vence el día de mañana.";
                } elseif ($diasRestantes <= 0) {
                    $mensaje = "Estimado cliente del {$data['ref_customer']}, le recordamos que su renta está vencida. Por favor, pase a pagar. Gracias.";
                }

                // Procesar envío
                $errorMensaje = '';
                $mensajeEnviado = false;
                if ($mensaje && !in_array($recentLine['id'], $lineasProcesadas)) {
                    $telefono = $data["array_options"]["options_numero_de_telefono_"] ?? '';
                    
                    if (!empty($telefono)) {
                        list($err, $response) = enviarWhatsApp(
                            'ekcyr2opsuz1wo6z',
                            $telefono,
                            $mensaje
                        );
                        
                        if (!$err) {
                            file_put_contents($archivoLineas, $recentLine['id'] . PHP_EOL, FILE_APPEND);
                            $lineasProcesadas[] = $recentLine['id'];
                            $mensajeEnviado = true;
                        } else {
                            $errorMensaje = "<div class='error-msg'><i class='fas fa-exclamation-triangle'></i> Error al enviar: $err</div>";
                        }
                    } else {
                        $errorMensaje = "<div class='error-msg'><i class='fas fa-phone-slash'></i> Teléfono no registrado</div>";
                    }
                }

                // Mostrar tarjeta
                echo "<div class='contract-card'>";
                echo "<h2><i class='fas fa-store'></i> {$data['ref_customer']}</h2>";
                echo "<p><i class='fas fa-hashtag'></i> Referencia: {$data['ref']}</p>";
                echo "<p><i class='fas fa-calendar-alt'></i> Inicio: " . $recentLine['dateStart']->format('d-m-Y') . "</p>";
                echo "<p><i class='fas fa-calendar-times'></i> Fin: " . $recentLine['dateEnd']->format('d-m-Y') . "</p>";

                // Mostrar estado
                $estadoClass = "desconocido";
                if ($diasRestantes < 0) {
                    $estado = "VENCIDO HACE " . abs($diasRestantes) . " DÍAS";
                    $estadoClass = "no-pagado";
                } elseif ($diasRestantes == 0) {
                    $estado = "VENCIDO HOY";
                    $estadoClass = "pagar";
                } elseif ($diasRestantes == 1) {
                    $estado = "VENCE MAÑANA";
                    $estadoClass = "pagar";
                } else {
                    $estado = "VIGENTE ($diasRestantes días restantes)";
                    $estadoClass = "pagado";
                }

                echo "<p class='status $estadoClass'><i class='fas fa-info-circle'></i> $estado</p>";
                
                if ($mensajeEnviado) {
                    echo "<div class='success-msg'><i class='fas fa-check-circle'></i> Mensaje enviado!</div>";
                }
                if ($errorMensaje) {
                    echo $errorMensaje;
                }
                if (in_array($recentLine['id'], $lineasProcesadas)) {
                    echo "<div class='info-msg'><i class='fas fa-info-circle'></i> Mensaje ya enviado</div>";
                }
                
                echo "</div>";
            }
        }
    }
}

echo '</div></div></body></html>';
curl_close($curl);
?>