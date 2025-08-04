<?php
// Archivo: /core/register_process.php

// Incluimos la conexión a la base de datos
require_once '../includes/db_connect.php';

// Verificamos que los datos lleguen por el método POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Recoger y sanear los datos del formulario
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $contrasena = $_POST['contrasena']; // No usamos trim en la contraseña
    $rol = $_POST['rol'];

    // 2. Validar los datos
    if (empty($nombre) || empty($correo) || empty($contrasena) || empty($rol)) {
        header("Location: ../register.php?error=Todos los campos son obligatorios");
        exit();
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../register.php?error=El formato del correo no es válido");
        exit();
    }

    // Validar que el rol sea uno de los permitidos
    $roles_permitidos = ['Developer', 'Scrum Master', 'Product Owner'];
    if (!in_array($rol, $roles_permitidos)) {
        header("Location: ../register.php?error=El rol seleccionado no es válido");
        exit();
    }

    // 3. Comprobar si el correo ya existe en la base de datos
    $stmt = $mysqli->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // El correo ya está registrado
        header("Location: ../register.php?error=Este correo electrónico ya está en uso");
        $stmt->close();
        $mysqli->close();
        exit();
    }
    $stmt->close();

    // 4. Hashear la contraseña (¡NUNCA guardes contraseñas en texto plano!)
    $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);

    // 5. Insertar el nuevo usuario en la base de datos
    $stmt = $mysqli->prepare("INSERT INTO usuarios (nombre, correo, contrasena, rol) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombre, $correo, $hashed_password, $rol);

    if ($stmt->execute()) {
        // Registro exitoso
        header("Location: ../login.php?success=¡Registro completado! Ahora puedes iniciar sesión.");
        exit();
    } else {
        // Error en la inserción
        header("Location: ../register.php?error=Ocurrió un error. Por favor, inténtalo de nuevo.");
        exit();
    }

    $stmt->close();
    $mysqli->close();

} else {
    // Si alguien intenta acceder al archivo directamente, lo redirigimos
    header("Location: ../register.php");
    exit();
}
?>
