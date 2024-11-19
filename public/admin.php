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
    <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
        }
    </style>
</head>
<body>
    <h1>Vista de Administrador</h1>
    <p>Bienvenido, <?php echo $_SESSION['nombre']; ?>!</p>

    <nav>
        <ul>
            <li><a href="views_admin/users.php">Usuarios</a></li>
            <li><a href="views_admin/califications.php">Calificaciones</a></li>
            <li><a href="views_admin/inscriptions.php">Inscripciones</a></li>
            <li><a href="views_admin/courses.php">Cursos</a></li>
            <li><a href="views_admin/docTypes.php">Tipos de Documento</a></li>
        </ul>
    </nav>

    <form action="logout.php" method="POST">
      <button type="submit">Cerrar Sesión</button>
    </form>

</body>
</html>