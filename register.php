<?php
// Archivo: /register.php (Versión corregida con iconos y placeholders)
include 'includes/header.php';

// Si el usuario ya está logueado, lo mandamos al dashboard.
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>

<!-- 1. Cambiamos la clase del contenedor principal -->
<div class="auth-container">
    <h2>Crear una Cuenta</h2>
    <p>Únete al equipo de GestorTasksIA</p>

    <?php
    // 2. Usamos las nuevas clases de alerta para los mensajes
    if (isset($_GET['error'])) {
        echo '<div class="alert alert-error">' . htmlspecialchars($_GET['error']) . '</div>';
    }
    if (isset($_GET['success'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_GET['success']) . '</div>';
    }
    ?>

    <form action="core/register_process.php" method="POST">
        
        <!-- 3. Reemplazamos los form-group por input-group con iconos -->
        
        <!-- Grupo para Nombre Completo -->
        <div class="input-group">
            <i class="fas fa-user"></i>
            <input type="text" name="nombre" class="form-control" placeholder="Nombre Completo" required>
        </div>

        <!-- Grupo para Correo Electrónico -->
        <div class="input-group">
            <i class="fas fa-envelope"></i>
            <input type="email" name="correo" class="form-control" placeholder="Correo Electrónico" required>
        </div>

        <!-- Grupo para Contraseña -->
        <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" name="contrasena" class="form-control" placeholder="Contraseña" required>
        </div>

        <!-- Grupo para Rol -->
        <div class="input-group">
            <i class="fas fa-briefcase"></i>
            <select name="rol" class="form-control" required>
                <!-- Añadimos una opción deshabilitada como placeholder para el select -->
                <option value="" disabled selected>Selecciona tu rol</option>
                <option value="Developer">Developer</option>
                <option value="Scrum Master">Scrum Master</option>
                <option value="Product Owner">Product Owner</option>
            </select>
        </div>

        <button type="submit" class="btn">Registrarse</button>
    </form>
    <p class="form-link">¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
</div>

<?php include 'includes/footer.php'; ?>
