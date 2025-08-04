<?php
// Archivo: /includes/db_connect.php

// --- Configuración de la Base de Datos ---
// Estas son las credenciales para conectar a tu base de datos local en XAMPP.

$db_host = 'localhost';     // El servidor donde está la base de datos, casi siempre 'localhost'.
$db_user = 'root';          // El usuario por defecto de MySQL en XAMPP es 'root'.
$db_pass = '';              // Por defecto, la contraseña del usuario 'root' en XAMPP está vacía.
$db_name = 'gestscrum';// El nombre de la base de datos que creamos.

// --- Creación de la Conexión ---
// Usamos el estilo orientado a objetos de MySQLi, que es moderno y seguro.

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

// --- Verificación de la Conexión ---
// Es una buena práctica comprobar siempre si la conexión ha fallado.
// Si falla, el script se detendrá y mostrará un error claro.

if ($mysqli->connect_error) {
    // die() detiene la ejecución del script inmediatamente.
    die('Error de Conexión (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

// --- (Opcional pero recomendado) Establecer el juego de caracteres a UTF-8 ---
// Esto asegura que los caracteres especiales y acentos se guarden y muestren correctamente.
if (!$mysqli->set_charset("utf8mb4")) {
    printf("Error cargando el conjunto de caracteres utf8mb4: %s\n", $mysqli->error);
    exit();
}

// No cerramos la conexión aquí ($mysqli->close();).
// El archivo se incluirá en otros scripts que necesitan la conexión activa.
// La conexión se cerrará automáticamente cuando el script principal termine.
?>
