<?php
require 'db.php';

// Función para registrar un usuario
function registerUser($nombre, $password, $correo, $rol) {
    global $conection;
    $stmt = $conection->prepare("INSERT INTO usuarios (nombre, correo, clave, rol) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombre, $correo, password_hash($password, PASSWORD_DEFAULT), $rol);
    $stmt->execute();
    $stmt->close();
}

// Función para verificar usuario y contraseña
function verifyUser($correo, $password) {
    global $conection;
    $stmt = $conection->prepare("SELECT clave FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();
    
    return password_verify($password, $hashed_password);
}

// Función para obtener el rol del usuario
function getUserRole($correo) {
    global $conection;
    $stmt = $conection->prepare("SELECT rol FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->bind_result($rol);
    $stmt->fetch();
    $stmt->close();
    
    return $rol;
}
?>
