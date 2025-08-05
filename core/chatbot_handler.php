<?php
// Archivo: /core/chatbot_handler.php (Versión 2.2 - Definitiva y Funcional)
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['reply' => 'Lo siento, tu sesión ha expirado.']);
    exit();
}

// --- Recolección de datos y contexto ---
$userInput = isset($_POST['message']) ? strtolower(trim($_POST['message'])) : '';
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['user_rol'];
$lastProjectId = isset($_SESSION['last_project_context']) ? $_SESSION['last_project_context'] : null;

// --- Definición de Intenciones ---
$intents = [
    'listar_proyectos' => ['keywords' => ['proyectos', 'proyecto', 'proyeto'], 'handler' => 'handleListProjects'],
    'listar_mis_tareas' => ['keywords' => ['mis tareas', 'mis asignaciones', 'que tengo que hacer'], 'handler' => 'handleMyTasks'],
    'listar_tareas_proyecto' => ['keywords' => ['tareas del proyecto', 'ver tareas', 'muestrame las tareas', 'tareas'], 'handler' => 'handleProjectTasks'],
    'saludo' => ['keywords' => ['hola', 'buenos dias', 'buenas tardes'], 'handler' => 'handleGreeting'],
    'listar_sprints' => ['keywords' => ['sprints del proyecto', 'ver sprints', 'muestrame los sprints'], 'handler' => 'handleListSprints'],
    'contar_tareas' => ['keywords' => ['cuantas tareas hay', 'contar tareas', 'total de tareas'], 'handler' => 'handleCountTasks'],
];

// --- Función para encontrar la mejor intención (sin cambios) ---
function findBestIntent($userInput, $intents) {
    $bestMatch = ['intent' => null, 'score' => 8];
    foreach ($intents as $intentName => $intentData) {
        foreach ($intentData['keywords'] as $keyword) {
            $distance = levenshtein($keyword, $userInput);
            if ($distance < $bestMatch['score']) {
                $bestMatch['score'] = $distance;
                $bestMatch['intent'] = $intentName;
            }
        }
    }
    return $bestMatch['score'] < 5 ? $bestMatch['intent'] : null;
}

// --- Manejadores (Handlers) ---

function handleListProjects($mysqli, $userId, $userRole) {
    $projects = getProjectsForUser($mysqli, $userId, $userRole);
    if (!empty($projects)) {
        $reply = '¡Claro! Aquí tienes los proyectos que te corresponden:<table><tr><th>ID</th><th>Nombre</th><th>Estado</th></tr>';
        foreach ($projects as $project) {
            $reply .= '<tr><td>' . $project['id'] . '</td><td>' . htmlspecialchars($project['nombre']) . '</td><td>' . htmlspecialchars($project['estado']) . '</td></tr>';
        }
        $reply .= '</table>';
        return $reply;
    }
    return 'No he encontrado ningún proyecto asignado a tu usuario.';
}

function handleMyTasks($mysqli, $userId) {
    $sql = "SELECT t.titulo, t.estado, p.nombre AS proyecto_nombre FROM tareas t JOIN proyectos p ON t.proyecto_id = p.id WHERE t.asignado_id = ? ORDER BY p.id";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $reply = 'Estas son todas tus tareas asignadas:<table><tr><th>Tarea</th><th>Estado</th><th>Proyecto</th></tr>';
        while ($row = $result->fetch_assoc()) {
            $reply .= '<tr><td>' . htmlspecialchars($row['titulo']) . '</td><td>' . htmlspecialchars($row['estado']) . '</td><td>' . htmlspecialchars($row['proyecto_nombre']) . '</td></tr>';
        }
        $reply .= '</table>';
        return $reply;
    }
    return 'No tienes ninguna tarea asignada en este momento.';
}

// CORREGIDO: Esta función ahora siempre consulta la BD para datos en tiempo real.
function handleProjectTasks($mysqli, $userInput, $lastProjectId) {
    $projectId = null;
    if (preg_match('/(\d+)/', $userInput, $matches)) {
        $projectId = $matches[1];
    } elseif ($lastProjectId) {
        $projectId = $lastProjectId;
    }

    if ($projectId) {
        $_SESSION['last_project_context'] = $projectId;
        $tasks = getTasksByProjectId($mysqli, $projectId); // Consulta en tiempo real
        if (!empty($tasks)) {
            $reply = 'Entendido. Aquí están las tareas para el proyecto ' . $projectId . ':<table><tr><th>Tarea</th><th>Estado</th><th>Prioridad</th></tr>';
            foreach ($tasks as $task) {
                $reply .= '<tr><td>' . htmlspecialchars($task['titulo']) . '</td><td>' . htmlspecialchars($task['estado']) . '</td><td>' . htmlspecialchars($task['prioridad']) . '</td></tr>';
            }
            $reply .= '</table>';
            return $reply;
        }
        return 'No encontré tareas para el proyecto ' . $projectId . '.';
    }
    return 'Por favor, especifica el ID del proyecto. Por ejemplo: "tareas del proyecto 1".';
}

function handleGreeting() {
    return '¡Hola! Soy tu asistente Scrum. Puedes preguntarme por tus proyectos, tus tareas o las tareas de un proyecto específico.';
}

function handleListSprints($mysqli, $userInput, $lastProjectId) {
    $projectId = null;
    if (preg_match('/(\d+)/', $userInput, $matches)) {
        $projectId = $matches[1];
    } elseif ($lastProjectId) {
        $projectId = $lastProjectId;
    }

    if ($projectId) {
        $_SESSION['last_project_context'] = $projectId;
        $sprints = getSprintsByProjectId($mysqli, $projectId);
        if (!empty($sprints)) {
            $reply = 'He encontrado los siguientes sprints para el proyecto ' . $projectId . ':<table><tr><th>Nombre</th><th>Estado</th><th>Fechas</th></tr>';
            foreach ($sprints as $sprint) {
                $fechas = date("d/m/y", strtotime($sprint['fecha_inicio'])) . ' - ' . date("d/m/y", strtotime($sprint['fecha_fin']));
                $reply .= '<tr><td>' . htmlspecialchars($sprint['nombre']) . '</td><td>' . htmlspecialchars($sprint['estado']) . '</td><td>' . $fechas . '</td></tr>';
            }
            $reply .= '</table>';
            return $reply;
        }
        return 'No encontré sprints para el proyecto ' . $projectId . '.';
    }
    return 'Por favor, especifica el ID del proyecto. Por ejemplo: "sprints del proyecto 1".';
}

function handleCountTasks($mysqli) {
    $sql = "SELECT estado, COUNT(*) as count FROM tareas GROUP BY estado";
    $result = $mysqli->query($sql);
    if ($result && $result->num_rows > 0) {
        $total = 0;
        $statusCounts = [];
        while($row = $result->fetch_assoc()) {
            $statusCounts[$row['estado']] = $row['count'];
            $total += $row['count'];
        }
        $reply = 'Actualmente hay un total de ' . $total . ' tareas, distribuidas así:<ul>';
        if (isset($statusCounts['Por hacer'])) $reply .= '<li><i class="fas fa-list-ul"></i> **Por hacer:** ' . $statusCounts['Por hacer'] . '</li>';
        if (isset($statusCounts['En progreso'])) $reply .= '<li><i class="fas fa-tasks"></i> **En progreso:** ' . $statusCounts['En progreso'] . '</li>';
        if (isset($statusCounts['Bloqueada'])) $reply .= '<li><i class="fas fa-ban"></i> **Bloqueada:** ' . $statusCounts['Bloqueada'] . '</li>';
        if (isset($statusCounts['Hecha'])) $reply .= '<li><i class="fas fa-check-double"></i> **Hecha:** ' . $statusCounts['Hecha'] . '</li>';
        $reply .= '</ul>';
        return $reply;
    }
    return 'No hay tareas en el sistema para contar.';
}

// =================================================================
// INICIO DE LA LÓGICA PRINCIPAL RESTAURADA
// =================================================================
$reply = '';
$intent = findBestIntent($userInput, $intents);

if ($intent) {
    $handler = $intents[$intent]['handler'];
    
    // Este bloque switch es esencial. Llama a cada función con los parámetros correctos.
    switch ($handler) {
        case 'handleListProjects':
            $reply = $handler($mysqli, $userId, $userRole);
            break;
        case 'handleMyTasks':
            $reply = $handler($mysqli, $userId);
            break;
        case 'handleProjectTasks':
        case 'handleListSprints':
            $reply = $handler($mysqli, $userInput, $lastProjectId);
            break;
        case 'handleGreeting':
            $reply = $handler();
            break;
        case 'handleCountTasks':
            $reply = $handler($mysqli);
            break;
        default:
            $reply = 'Error: No se encontró un manejador para esta intención.';
            break;
    }
} else {
    $reply = 'Lo siento, no he entendido tu consulta. Puedes probar con "proyectos", "mis tareas" o "contar tareas".';
}
// =================================================================
// FIN DE LA LÓGICA PRINCIPAL RESTAURADA
// =================================================================

echo json_encode(['reply' => $reply]);
$mysqli->close();
?>
