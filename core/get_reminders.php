<?php
// Archivo: /core/get_reminders.php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
session_start();

// Verificamos la sesión del usuario
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit();
}

// Usamos nuestra nueva función para obtener los recordatorios
$reminders = getUpcomingRemindersForUser($mysqli, $_SESSION['user_id']);

// Comprobamos si ya hemos mostrado este recordatorio en esta sesión
// para no ser repetitivos.
if (isset($_SESSION['reminders_shown']) && $_SESSION['reminders_shown'] === true) {
    echo json_encode(['success' => true, 'reminders' => null]);
    exit();
}

// Si hay recordatorios, los devolvemos y marcamos como "mostrados"
if (!empty($reminders['tasks']) || !empty($reminders['sprints'])) {
    $_SESSION['reminders_shown'] = true; // Marcamos para no volver a mostrar
    echo json_encode(['success' => true, 'reminders' => $reminders]);
} else {
    // No hay recordatorios pendientes
    echo json_encode(['success' => true, 'reminders' => null]);
}

$mysqli->close();
?>
