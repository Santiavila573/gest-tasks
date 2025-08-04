<?php
// Archivo: /manage_project.php (Versión Corregida)

include 'includes/header.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// --- Verificación de Seguridad y Obtención de Datos ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] === 'Developer') {
    // Solo Scrum Masters y Product Owners pueden gestionar miembros
    header('Location: dashboard.php?error=No tienes permisos para acceder a esta página.');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}
$project_id = $_GET['id'];

$project = getProjectById($mysqli, $project_id);

if (!$project) {
    header('Location: dashboard.php?error=Proyecto no encontrado');
    exit();
}

// =========================================================================
// INICIO DE LA CORRECCIÓN: Usamos las funciones que sí existen
// =========================================================================

// Obtener los miembros que YA ESTÁN en el proyecto
$project_members = getProjectMembers($mysqli, $project_id);

// Obtener los usuarios que NO ESTÁN en el proyecto para poder añadirlos
$non_members = getNonProjectMembers($mysqli, $project_id);

// =========================================================================
// FIN DE LA CORRECCIÓN
// =========================================================================

?>

<div class="manage-container">
    <!-- Encabezado del Proyecto (consistente con project_view) -->
    <div class="view-header">
        <div>
            <!-- Corregimos el enlace para que vuelva a la vista del proyecto, no al dashboard general -->
            <a href="project_view.php?id=<?php echo $project_id; ?>" class="back-link"><i class="fas fa-arrow-left"></i> Volver al Proyecto</a>
            <h1>Gestionar Equipo</h1>
            <p>Añade o elimina miembros del proyecto "<?php echo htmlspecialchars($project['nombre']); ?>"</p>
        </div>
    </div>

    <!-- Mensajes de éxito o error -->
    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php endif; ?>
    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <div class="management-columns">
        <!-- Columna 1: Miembros Actuales -->
        <div class="management-column">
            <h3>Miembros Actuales</h3>
            <div class="members-list">
                <?php if (!empty($project_members)): ?>
                    <?php foreach ($project_members as $member): ?>
                        <div class="member-item">
                            <div class="member-info">
                                <strong><?php echo htmlspecialchars($member['nombre']); ?></strong>
                                <span>(<?php echo htmlspecialchars($member['rol']); ?>)</span>
                            </div>
                            <!-- Formulario para eliminar miembro -->
                            <form action="core/manage_members_process.php" method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                                <input type="hidden" name="user_id" value="<?php echo $member['id']; ?>">
                                <button type="submit" class="btn-icon btn-remove" title="Eliminar miembro">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Este proyecto aún no tiene miembros asignados.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Columna 2: Añadir Nuevos Miembros -->
        <div class="management-column">
            <h3>Añadir Miembro al Proyecto</h3>
            <form action="core/manage_members_process.php" method="POST" class="add-member-form">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                
                <div class="form-group">
                    <label for="user_id">Selecciona un usuario:</label>
                    <select name="user_id" id="user_id" class="form-control" required>
                        <option value="" disabled selected>-- Elige un miembro --</option>
                        <?php if (!empty($non_members)): ?>
                            <?php foreach ($non_members as $user): ?>
                                <option value="<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['nombre']); ?> (<?php echo htmlspecialchars($user['rol']); ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <button type="submit" class="btn">Añadir al Equipo</button>
            </form>
            <?php if (empty($non_members)): ?>
                <p class="all-users-assigned">Todos los usuarios ya están en este proyecto.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
