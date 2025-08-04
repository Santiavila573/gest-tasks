<?php
// Archivo: /core/login_process.php

// Iniciamos la sesión para poder usar variables de sesión
session_start();

// Incluimos nuestro archivo de conexión a la base de datos
require_once '../includes/db_connect.php';

// Verificamos que los datos lleguen por el método POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];

    // Preparamos la consulta para evitar inyecciones SQL
    $stmt = $mysqli->prepare("SELECT id, nombre, contrasena, rol FROM usuarios WHERE correo = ? AND estado = 1");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->store_result();

    // Verificamos si se encontró un usuario
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $nombre, $hashed_password, $rol);
        $stmt->fetch();

        // Verificamos la contraseña
        if (password_verify($contrasena, $hashed_password)) {
            // La contraseña es correcta. Guardamos los datos en la sesión.
            $_SESSION['user_id'] = $id;
            $_SESSION['user_nombre'] = $nombre;
            $_SESSION['user_rol'] = $rol;

            // Redirigimos al dashboard
            header("Location: ../dashboard.php");
            exit();
        } else {
            // Contraseña incorrecta
            header("Location: ../login.php?error=Correo o contraseña incorrectos");
            exit();
        }
    } else {
        // Usuario no encontrado o inactivo
        header("Location: ../login.php?error=Correo o contraseña incorrectos");
        exit();
    }
    $stmt->close();
} else {
    // Si alguien intenta acceder al archivo directamente, lo redirigimos
    header("Location: ../login.php");
    exit();
}

$mysqli->close();
?>
