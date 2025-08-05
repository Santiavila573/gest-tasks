
<?php
include 'includes/header.php';
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>

<div class="auth-container">
    <h2>Iniciar Sesión</h2>
    <p>Bienvenido a GestorTasksIA</p>

    <?php
    if (isset($_GET['error'])) {
        echo '<div class="alert alert-error">' . htmlspecialchars($_GET['error']) . '</div>';
    }
    if (isset($_GET['success'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_GET['success']) . '</div>';
    }
    ?>

    <form action="core/login_process.php" method="POST">
        <div class="form-group">
            <input type="email" id="correo" name="correo" class="form-control" placeholder="Correo Electrónico" required>
        </div>
        <br>
        <div class="form-group">
            <input type="password" id="contrasena" name="contrasena" class="form-control" placeholder="Contraseña" required>
        </div>
        <br>
        <button type="submit" class="btn">Entrar</button>
    </form>
    <p class="form-link">¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a></p>
</div>

<?php include 'includes/footer.php'; ?>


