<?php
require 'db.php';

// Función para registrar un usuario (sin hashear la contraseña)
function registerUser($nombre, $password, $correo, $rol) {
	global $conection;
	$stmt = $conection->prepare("INSERT INTO usuarios (nombre, correo, clave, rol) VALUES (?, ?, ?, ?)");
	$stmt->bind_param("ssss", $nombre, $correo, $password, $rol); // Usar la contraseña sin hashear
	$stmt->execute();
	$stmt->close();
}

// Función para verificar usuario y contraseña (sin hashear)
function verifyUser($correo, $password) {
	global $conection;
	$stmt = $conection->prepare("SELECT clave FROM usuarios WHERE correo = ?");
	$stmt->bind_param("s", $correo);
	$stmt->execute();
	$stmt->bind_result($stored_password); // Cambiado a stored_password
	$stmt->fetch();
	$stmt->close();

	// Comparar la contraseña en texto plano
	return $password === $stored_password;
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
