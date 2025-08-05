<?php
// Archivo: /dashboard.php (Versión corregida con botón y modal para crear proyectos)

include 'includes/header.php';
require_once 'includes/functions.php';
require_once 'includes/db_connect.php';

// Verificamos si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Obtenemos los proyectos para el usuario actual
$projects = getProjectsForUser($mysqli, $_SESSION['user_id'], $_SESSION['user_rol']);
?>

<div class="dashboard-container">

<div class="dashboard-header">
    <h1 class="dashboard-title">
        <img src="assets/images/logotipo.png" alt="GestorTasks Logo" class="dashboard-logo">
        <br>
        Panel de Proyectos
    </h1>
    <!-- Nuevo contenedor para los botones -->
    <div class="header-actions">
        <?php
        // Lógica de permisos para el botón de crear proyecto
        if ($_SESSION['user_rol'] === 'Scrum Master' || $_SESSION['user_rol'] === 'Product Owner'):
        ?>
            <button id="show-project-modal-btn" class="btn"><i class="fas fa-plus"></i> Crear Proyecto</button>
        <?php endif; ?>
        
        <!-- Botón de cerrar sesión -->
        <a href="logout.php" class="btn btn-secondary">Cerrar Sesión</a>
    </div>
</div>



    
    <div class="projects-grid">
        <?php if (!empty($projects)): ?>
            <?php foreach ($projects as $project): ?>
                <?php
                // Lógica para calcular el estado de la fecha (sin cambios)
                $date_status_class = '';
                $date_status_icon = 'fa-calendar-alt';
                $today = new DateTime(); 
                $endDate = $project['fecha_fin'] ? new DateTime($project['fecha_fin']) : null;

                if ($project['estado'] === 'Activo' && $endDate) {
                    if ($today->setTime(0, 0, 0) > $endDate) {
                        $date_status_class = 'status-delayed';
                        $date_status_icon = 'fa-exclamation-triangle';
                    } else {
                        $date_status_class = 'status-on-time';
                        $date_status_icon = 'fa-check-circle';
                    }
                }
                ?>

                <a href="project_view.php?id=<?php echo $project['id']; ?>" class="project-card-link">
                    <div class="project-card">
                        <div class="project-card-header">
                            <span class="project-status status-<?php echo strtolower(htmlspecialchars($project['estado'])); ?>">
                                <?php echo htmlspecialchars($project['estado']); ?>
                            </span>
                        </div>
                        <div class="project-card-body">
                            <h3><?php echo htmlspecialchars($project['nombre']); ?></h3>
                            <p><?php echo htmlspecialchars($project['descripcion']); ?></p>
                        </div>
                        <div class="project-card-footer <?php echo $date_status_class; ?>">
                            <span>
                                <i class="fas <?php echo $date_status_icon; ?>"></i> 
                                <?php echo date("d/m/Y", strtotime($project['fecha_inicio'])); ?> - 
                                <?php echo $project['fecha_fin'] ? date("d/m/Y", strtotime($project['fecha_fin'])) : 'N/A'; ?>
                            </span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-projects-message">
                <!-- Mensaje actualizado para ser más general -->
                <p>No hay proyectos para mostrar. Si tienes permisos, puedes crear uno nuevo.</p>
            </div>
        <?php endif; ?>
    </div>
</div>


<div id="add-project-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <br>
            <h2 class="dashboard-subtitle">Nuevo Proyecto 📋</h2>
            <button id="close-project-modal-btn" class="close-btn">&times;</button>
        </div>
        <div class="modal-body">
            <form id="add-project-form" action="core/add_project.php" method="POST">
                <div class="form-group">
                    <label for="project-name">Nombre del Proyecto</label>
                    <br>
                    <input type="text" id="project-name" name="nombre" class="form-control" required>
                </div>
                <br>
                <div class="form-group">
                    <label for="project-desc">Descripción</label>
                    <br>
                    <textarea id="project-desc" name="descripcion" class="form-control" rows="3"></textarea>
                </div>
                <br>
                <div class="form-group-inline">
                    <div class="form-group">
                        <label for="project-start">Fecha de Inicio</label>
                        <br>
                        <input type="date" id="project-start" name="fecha_inicio" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="project-end">Fecha de Fin (Opcional)</label>
                        <br>
                        <input type="date" id="project-end" name="fecha_fin" class="form-control">
                    </div>
                </div>
                <br>
                <br>
                <div class="form-actions">
                    <button type="submit" class="btn">Guardar Proyecto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
