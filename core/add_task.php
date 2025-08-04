<?php
// Archivo: /core/add_task.php (Versión AJAX con Asignación)
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
require_once '../includes/functions.php'; // Incluimos functions para reutilizar lógica si es necesario
session_start();

$response = ['success' => false, 'message' => 'Error desconocido.'];

// 1. Verificación de seguridad y sesión
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Sesión no válida. Por favor, inicie sesión de nuevo.';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método no permitido.';
    echo json_encode($response);
    exit();
}

// 2. Recopilación y validación de datos del formulario
$titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
$prioridad = isset($_POST['prioridad']) ? $_POST['prioridad'] : 'Media';
$project_id = isset($_POST['project_id']) ? (int)$_POST['project_id'] : 0;
$creador_id = $_SESSION['user_id'];
$estado = 'Por hacer'; // Todas las tareas nuevas empiezan en este estado

// =========================================================================
// INICIO DE LA ACTUALIZACIÓN: Manejo del campo 'asignado_id'
// =========================================================================
// Si 'asignado_id' se envía y no está vacío, lo convertimos a entero.
// Si no, lo dejamos como NULL para que la base de datos lo acepte.
$asignado_id = !empty($_POST['asignado_id']) ? (int)$_POST['asignado_id'] : NULL;
// =========================================================================
// FIN DE LA ACTUALIZACIÓN
// =========================================================================


if (empty($titulo) || $project_id === 0) {
    $response['message'] = 'El título y el proyecto son obligatorios.';
} else {
    // 3. Preparación y ejecución de la consulta SQL
    // La consulta ahora incluye el campo 'asignado_id'
    $sql = "INSERT INTO tareas (titulo, prioridad, estado, creador_id, proyecto_id, asignado_id) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    // El 'bind_param' ahora es "sssiii" para coincidir con los 6 campos
    $stmt->bind_param("sssiii", $titulo, $prioridad, $estado, $creador_id, $project_id, $asignado_id);

    if ($stmt->execute()) {
        $new_task_id = $stmt->insert_id;
        
        // 4. Preparamos una respuesta JSON completa y útil
        $response['success'] = true;
        $response['message'] = '¡Tarea creada con éxito!';

        // Para devolver el nombre del asignado, hacemos una consulta rápida
        $asignado_nombre = 'Sin asignar';
        if ($asignado_id !== NULL) {
            $user_sql = "SELECT nombre FROM usuarios WHERE id = ? LIMIT 1";
            $user_stmt = $mysqli->prepare($user_sql);
            $user_stmt->bind_param("i", $asignado_id);
            $user_stmt->execute();
            $user_result = $user_stmt->get_result();
            if ($user_row = $user_result->fetch_assoc()) {
                $asignado_nombre = $user_row['nombre'];
            }
            $user_stmt->close();
        }
        
        // Devolvemos todos los datos necesarios para que JavaScript construya la tarjeta
        $response['task'] = [
            'id' => $new_task_id,
            'titulo' => htmlspecialchars($titulo),
            'prioridad' => htmlspecialchars($prioridad),
            'estado' => $estado,
            'asignado_nombre' => htmlspecialchars($asignado_nombre),
            'sprint_id' => null // Las tareas nuevas no están en un sprint por defecto
        ];

    } else {
        $response['message'] = 'Error al guardar la tarea en la base de datos: ' . $stmt->error;
    }
    $stmt->close();
}

$mysqli->close();
echo json_encode($response);
?>
