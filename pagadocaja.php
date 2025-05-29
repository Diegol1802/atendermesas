<?php
include 'config.php';

if (!isset($_GET['id'])) {
    die("No se especificó el ID del pedido.");
}

$id = intval($_GET['id']);

// Paso 1: Obtener HTML remoto
$url = "https://relamticket.cl/comida/pedido.php?id=$id";
$html = @file_get_contents($url);
if ($html === false) {
    die("No se pudo obtener el contenido de la URL.");
}

// Paso 2: Extraer monto buscando <tr class="total-row"> y luego <td class="price"> dentro
$doc = new DOMDocument();
libxml_use_internal_errors(true);
$doc->loadHTML($html);
libxml_clear_errors();

$totalPedido = 0;

$trs = $doc->getElementsByTagName('tr');

foreach ($trs as $tr) {
    if ($tr->getAttribute('class') === 'total-row') {
        // Encontramos la fila total-row, ahora buscar td con clase price dentro de esta fila
        $tds = $tr->getElementsByTagName('td');
        foreach ($tds as $td) {
            if ($td->getAttribute('class') === 'price') {
                $text = $td->textContent;
                // Limpiar texto para obtener número válido
                $num = preg_replace('/[^\d,\.]/', '', $text);
                $num = str_replace('.', '', $num); // eliminar separadores de miles
                $num = str_replace(',', '.', $num); // corregir decimal si es coma
                $totalPedido = floatval($num);
                break 2; // salir de ambos foreach
            }
        }
    }
}

if ($totalPedido <= 0) {
    die("No se pudo obtener un monto válido.");
}

// Paso 3: Actualizar base de datos
$stmt = $conn->prepare("UPDATE pedidos SET pagado = 1, monto_pagado = ? WHERE id = ?");
$stmt->bind_param("di", $totalPedido, $id);

if ($stmt->execute()) {
    echo "Pedido ID $id actualizado como pagado con monto $totalPedido.";
} else {
    echo "Error al actualizar pedido: " . $stmt->error;
}

$stmt->close();
$conn->close();
