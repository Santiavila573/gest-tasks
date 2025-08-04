<?php
// Archivo: /core/update_task_status.php

// Indicamos que la respuesta será en formato JSON
header('Content-Type: application/json');

require_once '../includes/db_connect.php';
session_start(); // Iniciamos sesión para verificar que el usuario esté logueado

// Verificamos que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Acceso no autorizado']);
    exit();
}

// Verificamos que los datos necesarios lleguen por POST
if (isset($_POST['task_id']) && isset($_POST['new_status'])) {
    $taskId = $_POST['task_id'];
    $newStatus = $_POST['new_status'];

    // Validamos los datos
    if (!is_numeric($taskId)) {
        echo json_encode(['success' => false, 'error' => 'ID de tarea inválido']);
        exit();
    }

    // Lista de estados permitidos para seguridad
    $allowed_statuses = ['porhacer', 'enprogreso', 'bloqueada', 'hecha'];
    if (!in_array($newStatus, $allowed_statuses)) {
        echo json_encode(['success' => false, 'error' => 'Estado no válido']);
        exit();
    }
    
    // El estado en la BD tiene espacios ('En progreso'), lo formateamos
    $dbStatus = ucfirst(str_replace(['porhacer', 'enprogreso'], ['Por hacer', 'En progreso'], $newStatus));

    // Preparamos la consulta para actualizar la base de datos
    $stmt = $mysqli->prepare("UPDATE tareas SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $dbStatus, $taskId);

    if ($stmt->execute()) {
        // Si la actualización fue exitosa
        echo json_encode(['success' => true]);
    } else {
        // Si hubo un error en la consulta
        echo json_encode(['success' => false, 'error' => 'Error al actualizar la base de datos']);
    }
    
    $stmt->close();
    $mysqli->close();

} else {
    // Si no se recibieron los datos esperados
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
}
?>
