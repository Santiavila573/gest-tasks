<?php
// Archivo: /core/manage_members.php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
session_start();

// --- Seguridad y Permisos ---
if (!isset($_SESSION['user_id']) || !isset($_GET['action']) || !isset($_GET['project_id']) || !isset($_GET['user_id'])) {
    header('Location: ../dashboard.php');
    exit();
}

$action = $_GET['action'];
$projectId = $_GET['project_id'];
$userId = $_GET['user_id'];

$project = getProjectById($mysqli, $projectId);

// Verificamos que el usuario actual tenga permiso para gestionar este proyecto
if ($project['propietario_id'] != $_SESSION['user_id'] && $_SESSION['user_rol'] !== 'Scrum Master') {
    header('Location: ../manage_project.php?id=' . $projectId . '&error=No tienes permiso.');
    exit();
}

// --- Lógica de Acciones ---
if ($action === 'add') {
    $sql = "INSERT INTO proyecto_usuarios (proyecto_id, usuario_id) VALUES (?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ii", $projectId, $userId);
    $stmt->execute();
    $stmt->close();
} elseif ($action === 'remove') {
    // No se puede quitar al propietario del proyecto
    if ($userId == $project['propietario_id']) {
        header('Location: ../manage_project.php?id=' . $projectId . '&error=No se puede quitar al propietario del proyecto.');
        exit();
    }
    $sql = "DELETE FROM proyecto_usuarios WHERE proyecto_id = ? AND usuario_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ii", $projectId, $userId);
    $stmt->execute();
    $stmt->close();
}

header('Location: ../manage_project.php?id=' . $projectId);
exit();
?>
