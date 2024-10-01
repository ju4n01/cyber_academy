<?php
require 'config.php';

// Crear Conexión
$conection = new mysqli($server, $user, $pass, $db);

// Verificar la Conexión
if ($conection->connect_errno) {
    die("Falla en la Conexión: " . $conection->connect_error);
} else {
  echo "Conexión Exitosa :)" . "<br>";
}

// Función para cerrar la conexión
function closeConnection($conection) {
    $conection->close();
}
?>
