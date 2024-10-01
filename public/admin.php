<?php
session_start();
if (!isset($_SESSION['correo']) || $_SESSION['rol'] != 'administrador') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Ciber Academy - Administrador</title>
</head>
<body>
    <h1>Vista de Administrador</h1>
    <p>Bienvenido, <?php echo $_SESSION['nombre']; ?>!</p>
    <form action="logout.php" method="POST">
      <button type="submit">Cerrar Sesión</button>
    </form>
    <!-- Contenido específico para administradores -->
</body>
</html>