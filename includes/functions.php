<?php
/**
 * Archivo: /includes/functions.php
 * Descripción: Contiene todas las funciones reutilizables de la aplicación,
 *              para interactuar con la base de datos y realizar operaciones comunes.
 */

// Evita que el archivo sea accedido directamente.
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Acceso directo no permitido.');
}

// =========================================================================
// FUNCIONES DE PROYECTOS
// =========================================================================

/**
 * Obtiene todos los proyectos de la base de datos.
 * (Función original, mantenida por si se necesita en un futuro para un admin global).
 *
 * @param object $mysqli Conexión a la base de datos.
 * @return array Lista de todos los proyectos.
 */
function getAllProjects($mysqli) {
    $projects = [];
    $sql = "SELECT id, nombre, descripcion, fecha_inicio, fecha_fin, estado FROM proyectos ORDER BY fecha_inicio DESC";
    $result = $mysqli->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $projects[] = $row;
        }
    }
    return $projects;
}

/**
 * Obtiene los proyectos para un usuario específico, aplicando la lógica de roles.
 * - Scrum Master y Product Owner ven todos los proyectos.
 * - Developer solo ve los proyectos a los que está asignado.
 *
 * @param object $mysqli Conexión a la base de datos.
 * @param int $user_id ID del usuario.
 * @param string $user_role Rol del usuario.
 * @return array Lista de proyectos correspondientes al usuario.
 */
function getProjectsForUser($mysqli, $user_id, $user_role) {
    $projects = [];
    
    if ($user_role === 'Developer') {
        // Los Developers solo ven los proyectos a los que están asignados
        $sql = "SELECT p.* FROM proyectos p
                JOIN proyecto_usuarios pu ON p.id = pu.proyecto_id
                WHERE pu.usuario_id = ?
                ORDER BY p.fecha_inicio DESC";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("i", $user_id);
    } else {
        // Scrum Masters y Product Owners ven todos los proyectos
        $sql = "SELECT * FROM proyectos ORDER BY fecha_inicio DESC";
        $stmt = $mysqli->prepare($sql);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $projects[] = $row;
        }
    }
    $stmt->close();
    return $projects;
}


/**
 * Obtiene los detalles de un proyecto específico por su ID.
 *
 * @param object $mysqli Conexión a la base de datos.
 * @param int $project_id ID del proyecto.
 * @return array|null Detalles del proyecto o null si no se encuentra.
 */
function getProjectById($mysqli, $project_id) {
    $sql = "SELECT * FROM proyectos WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $project = $result->fetch_assoc();
    $stmt->close();
    return $project;
}

// =========================================================================
// FUNCIONES DE SPRINTS Y TAREAS
// =========================================================================

/**
 * Obtiene todos los sprints de un proyecto específico.
 *
 * @param object $mysqli Conexión a la base de datos.
 * @param int $project_id ID del proyecto.
 * @return array Lista de sprints.
 */
function getSprintsByProjectId($mysqli, $project_id) {
    $sprints = [];
    $sql = "SELECT * FROM sprints WHERE proyecto_id = ? ORDER BY fecha_inicio ASC";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $sprints[] = $row;
        }
    }
    $stmt->close();
    return $sprints;
}

/**
 * Obtiene todas las tareas de un proyecto, incluyendo el nombre del asignado.
 *
 * @param object $mysqli Conexión a la base de datos.
 * @param int $project_id ID del proyecto.
 * @return array Lista de tareas con detalles del asignado.
 */
function getTasksByProjectId($mysqli, $project_id) {
    $tasks = [];
    // Usamos LEFT JOIN para que las tareas sin asignar también aparezcan
    $sql = "SELECT t.*, u.nombre AS asignado_nombre 
            FROM tareas t 
            LEFT JOIN usuarios u ON t.asignado_id = u.id 
            WHERE t.proyecto_id = ? 
            ORDER BY t.id ASC";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $tasks[] = $row;
        }
    }
    $stmt->close();
    return $tasks;
}

// =========================================================================
// FUNCIONES DE USUARIOS Y GESTIÓN DE MIEMBROS
// =========================================================================

/**
 * Obtiene todos los usuarios que son miembros de un proyecto específico.
 *
 * @param object $mysqli Conexión a la base de datos.
 * @param int $project_id ID del proyecto.
 * @return array Lista de miembros del proyecto (id, nombre, rol).
 */
function getProjectMembers($mysqli, $project_id) {
    $members = [];
    // La consulta une 'usuarios' con 'proyecto_usuarios' para obtener los detalles
    // de los usuarios que están vinculados al ID del proyecto.
    $sql = "SELECT u.id, u.nombre, u.rol 
            FROM usuarios u
            JOIN proyecto_usuarios pu ON u.id = pu.usuario_id
            WHERE pu.proyecto_id = ?";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $members[] = $row;
        }
    }
    $stmt->close();
    return $members;
}

/**
 * Obtiene todos los usuarios que NO son miembros de un proyecto específico.
 *
 * @param object $mysqli Conexión a la base de datos.
 * @param int $project_id ID del proyecto.
 * @return array Lista de usuarios que no están en el proyecto.
 */
function getNonProjectMembers($mysqli, $project_id) {
    $users = [];
    $sql = "SELECT id, nombre, rol FROM usuarios
            WHERE id NOT IN (SELECT usuario_id FROM proyecto_usuarios WHERE proyecto_id = ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    $stmt->close();
    return $users;
}

// =========================================================================
// FUNCIONES DE RECORDATORIOS PROACTIVOS (CHATBOT)
// =========================================================================

/**
 * Obtiene un resumen de tareas y sprints que están a punto de vencer
 * para un usuario específico. Es más inteligente, ya que si una tarea
 * no tiene fecha propia, usa la fecha de fin de su sprint.
 *
 * @param object $mysqli Conexión a la base de datos.
 * @param int $userId ID del usuario que ha iniciado sesión.
 * @return array Un array con las tareas y sprints que necesitan un recordatorio.
 */
function getUpcomingRemindersForUser($mysqli, $userId) {
    $reminders = [
        'tasks' => [],
        'sprints' => []
    ];

    // Rango de fechas: desde hoy hasta dentro de 2 días.
    $today = (new DateTime())->format('Y-m-d');
    $limit_date = (new DateTime())->modify('+2 days')->format('Y-m-d');

    // --- 1. Buscar TAREAS asignadas al usuario que vencen pronto ---
    // COALESCE elige la primera fecha que no sea NULL:
    // 1. La fecha propia de la tarea (fecha_estimada).
    // 2. Si es NULL, la fecha de fin del sprint de la tarea.
    $sql_tasks = "SELECT 
                    t.titulo,
                    p.nombre AS proyecto_nombre,
                    COALESCE(t.fecha_estimada, s.fecha_fin) AS fecha_vencimiento
                  FROM tareas t
                  JOIN proyectos p ON t.proyecto_id = p.id
                  LEFT JOIN sprints s ON t.sprint_id = s.id
                  WHERE 
                    t.asignado_id = ?
                    AND t.estado NOT IN ('Hecha')
                    AND COALESCE(t.fecha_estimada, s.fecha_fin) BETWEEN ? AND ?";
    
    $stmt_tasks = $mysqli->prepare($sql_tasks);
    $stmt_tasks->bind_param("iss", $userId, $today, $limit_date);
    $stmt_tasks->execute();
    $result_tasks = $stmt_tasks->get_result();
    
    if ($result_tasks) {
        while ($row = $result_tasks->fetch_assoc()) {
            $reminders['tasks'][] = $row;
        }
    }
    $stmt_tasks->close();

    // --- 2. Buscar SPRINTS de proyectos en los que el usuario participa ---
    $sql_sprints = "SELECT s.nombre, s.fecha_fin, p.nombre AS proyecto_nombre
                    FROM sprints s
                    JOIN proyecto_usuarios pu ON s.proyecto_id = pu.proyecto_id
                    JOIN proyectos p ON s.proyecto_id = p.id
                    WHERE 
                        pu.usuario_id = ?
                        AND s.estado NOT IN ('Finalizado')
                        AND s.fecha_fin BETWEEN ? AND ?";

    $stmt_sprints = $mysqli->prepare($sql_sprints);
    $stmt_sprints->bind_param("iss", $userId, $today, $limit_date);
    $stmt_sprints->execute();
    $result_sprints = $stmt_sprints->get_result();

    if ($result_sprints) {
        while ($row = $result_sprints->fetch_assoc()) {
            $reminders['sprints'][] = $row;
        }
    }
    $stmt_sprints->close();

    return $reminders;
}

?>
