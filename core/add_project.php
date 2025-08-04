<?php
// Archivo: /core/add_project.php

require_once '../includes/db_connect.php';
session_start();

// --- Verificación de Seguridad y Permisos ---
// 1. El usuario debe estar logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// 2. Solo los roles de gestión pueden crear proyectos
if ($_SESSION['user_rol'] !== 'Scrum Master' && $_SESSION['user_rol'] !== 'Product Owner') {
    header('Location: ../dashboard.php?error=No tienes permiso para realizar esta acción.');
    exit();
}

// 3. Solo procesamos si los datos vienen por método POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // --- Recolección y Limpieza de Datos ---
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $fecha_inicio = $_POST['fecha_inicio'];
    // La fecha de fin es opcional, así que puede ser nula
    $fecha_fin = !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : NULL;
    $propietario_id = $_SESSION['user_id']; // El usuario que crea el proyecto es el propietario

    // --- Validación de Datos ---
    if (empty($nombre) || empty($fecha_inicio)) {
        header('Location: ../dashboard.php?error=El nombre y la fecha de inicio son obligatorios.');
        exit();
    }

    // --- Lógica de Base de Datos con Transacción ---
    // Una transacción asegura que todas las operaciones se completen con éxito, o ninguna lo hará.
    // Esto previene proyectos "huérfanos" sin un miembro asignado.
    $mysqli->begin_transaction();

    try {
        // 1. Insertar el nuevo proyecto en la tabla 'proyectos'
        $sql_project = "INSERT INTO proyectos (nombre, descripcion, fecha_inicio, fecha_fin, propietario_id) VALUES (?, ?, ?, ?, ?)";
        $stmt_project = $mysqli->prepare($sql_project);
        $stmt_project->bind_param("ssssi", $nombre, $descripcion, $fecha_inicio, $fecha_fin, $propietario_id);
        $stmt_project->execute();
        
        // Obtenemos el ID del proyecto que acabamos de crear
        $new_project_id = $stmt_project->insert_id;
        $stmt_project->close();

        // 2. Asignar automáticamente al creador como miembro en la tabla 'proyecto_usuarios'
        $sql_assign = "INSERT INTO proyecto_usuarios (proyecto_id, usuario_id) VALUES (?, ?)";
        $stmt_assign = $mysqli->prepare($sql_assign);
        $stmt_assign->bind_param("ii", $new_project_id, $propietario_id);
        $stmt_assign->execute();
        $stmt_assign->close();

        // Si ambas operaciones fueron exitosas, confirmamos los cambios en la BD
        $mysqli->commit();
        header('Location: ../dashboard.php?success=Proyecto creado con éxito.');

    } catch (mysqli_sql_exception $exception) {
        // Si algo falló, revertimos todos los cambios para no dejar datos corruptos
        $mysqli->rollback();
        header('Location: ../dashboard.php?error=Error al crear el proyecto. Inténtalo de nuevo.');
    }

    $mysqli->close();

} else {
    // Si alguien intenta acceder a este archivo directamente, lo redirigimos
    header('Location: ../dashboard.php');
}
?>
