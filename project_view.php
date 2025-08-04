<?php
// Archivo: /project_view.php (Versión mejorada)

include 'includes/header.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// --- Verificación de Seguridad y Obtención de Datos ---
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}
$project_id = $_GET['id'];

// Obtenemos todos los datos necesarios para la página
$project = getProjectById($mysqli, $project_id);
$sprints = getSprintsByProjectId($mysqli, $project_id);
$tasks = getTasksByProjectId($mysqli, $project_id);

if (!$project) {
    header('Location: dashboard.php?error=Proyecto no encontrado');
    exit();
}
?>

<div class="view-container">
    <!-- Encabezado del Proyecto -->
    <div class="view-header">
        <div>
            <a href="dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Volver a Proyectos</a>
            <h1><?php echo htmlspecialchars($project['nombre']); ?></h1>
            <p><?php echo htmlspecialchars($project['descripcion']); ?></p>
        </div>
        <div class="header-details">
            <span class="project-status status-<?php echo strtolower(htmlspecialchars($project['estado'])); ?>">
                <?php echo htmlspecialchars($project['estado']); ?>
            </span>
            <div class="project-dates">
                <i class="fas fa-calendar-alt"></i>
                <?php echo date("d M, Y", strtotime($project['fecha_inicio'])); ?> - 
                <?php echo $project['fecha_fin'] ? date("d M, Y", strtotime($project['fecha_fin'])) : 'Sin fecha final'; ?>
            </div>
        </div>
    </div>

    <!-- Sección de Sprints -->
    <div class="section">
        <h2>Sprints del Proyecto</h2>
        <div class="sprints-list">
            <?php if (!empty($sprints)): ?>
                <?php foreach ($sprints as $sprint): ?>
                    <div class="sprint-item">
                        <div class="sprint-info">
                            <h4><?php echo htmlspecialchars($sprint['nombre']); ?></h4>
                            <p class="sprint-dates">
                                <i class="fas fa-flag-checkered"></i> 
                                <?php echo date("d/m/Y", strtotime($sprint['fecha_inicio'])); ?> a 
                                <?php echo date("d/m/Y", strtotime($sprint['fecha_fin'])); ?>
                            </p>
                        </div>
                        <span class="sprint-status status-<?php echo strtolower(htmlspecialchars($sprint['estado'])); ?>">
                            <?php echo htmlspecialchars($sprint['estado']); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay sprints definidos para este proyecto.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sección del Tablero de Tareas -->
    <div class="section">
        <h2>Tablero de Tareas</h2>
        <div class="add-task-container">
            <form id="add-task-form" action="core/add_task.php" method="POST">
                <h4>Nueva Tarea</h4>
                <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                <div class="form-group">
                    <label for="task-title">Título de la Tarea</label>
                    <input type="text" id="task-title" name="titulo" class="form-control" required>
                </div>
                <div class="form-group-inline">
                    <div class="form-group">
                        <label for="task-priority">Prioridad</label>
                        <select id="task-priority" name="prioridad" class="form-control" required>
                            <option value="Baja">Baja</option>
                            <option value="Media" selected>Media</option>
                            <option value="Alta">Alta</option>
                        </select>
                    </div>
                    <!-- Campo de Asignación -->
                    <div class="form-group">
                        <label for="task-assignee">Asignar a:</label>
                        <select id="task-assignee" name="asignado_id" class="form-control">
                            <option value="">-- Sin asignar --</option>
                            <?php 
                                $project_members = getProjectMembers($mysqli, $project_id);
                                foreach ($project_members as $member):
                            ?>
                                <option value="<?php echo $member['id']; ?>">
                                    <?php echo htmlspecialchars($member['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <br>
                <br>
                <div class="form-actions">
                    <button type="submit" class="btn">Guardar Tarea</button>
                    <br>
                    <br>
                    <button type="button" id="cancel-task-btn" class="btn btn-secondary">Cancelar</button>
                </div>
            </form>
        </div>
        <div class="kanban-board">
            <?php
            $columns = ['Por hacer', 'En progreso', 'Bloqueada', 'Hecha'];
            foreach ($columns as $column):
            ?>
                <div class="kanban-column">
                    <div class="column-header">
                        <h3>
                            <?php
                            if ($column === 'Por hacer') echo '<i class="fas fa-list-ul"></i>';
                            if ($column === 'En progreso') echo '<i class="fas fa-tasks"></i>';
                            if ($column === 'Bloqueada') echo '<i class="fas fa-ban"></i>';
                            if ($column === 'Hecha') echo '<i class="fas fa-check-double"></i>';
                            echo $column;
                            ?>
                        </h3>
                    </div>
                    <div class="column-tasks" data-column-status="<?php echo strtolower(str_replace(' ', '', $column)); ?>">
                        <?php
                        foreach ($tasks as $task) {
                            $task_status_normalized = strtolower(str_replace(' ', '', $task['estado']));
                            $column_status_normalized = strtolower(str_replace(' ', '', $column));

                            if ($task_status_normalized === $column_status_normalized) {
                                ?>
                                <div class="task-card priority-<?php echo strtolower(htmlspecialchars($task['prioridad'])); ?>" data-task-id="<?php echo $task['id']; ?>">
                                    <p><?php echo htmlspecialchars($task['titulo']); ?></p>
                                    <div class="task-footer">
                                        <span class="task-assignee">
                                            <i class="fas fa-user-circle"></i>
                                            <?php echo $task['asignado_nombre'] ? htmlspecialchars($task['asignado_nombre']) : 'Sin asignar'; ?>
                                        </span>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

