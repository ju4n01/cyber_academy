<?php
session_start();
if (!isset($_SESSION['correo']) || $_SESSION['rol'] != 'profesor') {
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
    <title>Ciber Academy - Profesor</title>
</head>
<body>
    <h1>Vista de Profesor</h1>
    <p>Bienvenido, <?php echo $_SESSION['nombre']; ?>!</p>
    <nav>
        <ul>
            <li><a href="index.php">Volver a Inicio</a></li>
            <li><a href="student.php">Estudiante</a></li>
            <li><a href="admin.php">Administrador</a></li>
        </ul>
    </nav>
    <form action="logout.php" method="POST">
      <button type="submit">Cerrar Sesión</button>
    </form>
    <!-- Contenido específico para profesores -->
</body>
</html>
