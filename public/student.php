<?php
session_start();
if (!isset($_SESSION['correo']) || $_SESSION['rol'] != 'estudiante') {
    header("Location: index.php");
    exit();
}

require '../src/db.php'; // Asegúrate de que este archivo contiene la conexión a la base de datos

$correo = $_SESSION['correo'];

try {
    // Preparar la consulta para obtener los cursos del estudiante
    $stmt = $conection->prepare("
        SELECT c.titulo, c.descripcion, i.fecha_inscripcion, n.nota
        FROM cursos c
        JOIN inscripciones i ON c.id = i.curso_id
        JOIN usuarios u ON i.usuario_id = u.id
        LEFT JOIN notas n ON i.usuario_id = n.usuario_id AND i.curso_id = n.curso_id
        WHERE u.correo = ?
    ");
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta: " . $conection->error);
    }

    $stmt->bind_param("s", $correo);
    if (!$stmt->execute()) {
        throw new Exception("Error en la ejecución de la consulta: " . $stmt->error);
    }

    $stmt->bind_result($titulo, $descripcion, $fecha_inscripcion, $nota);
    $cursos = [];
    while ($stmt->fetch()) {
        $cursos[] = [
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'fecha_inscripcion' => $fecha_inscripcion,
            'nota' => $nota
        ];
    }
    $stmt->close();

    // Preparar la consulta para obtener los datos del usuario
    $stmt = $conection->prepare("
        SELECT numero_identificacion, correo, rol 
        FROM usuarios 
        WHERE correo = ?
    ");
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta: " . $conection->error);
    }

    $stmt->bind_param("s", $correo);
    if (!$stmt->execute()) {
        throw new Exception("Error en la ejecución de la consulta: " . $stmt->error);
    }

    $stmt->bind_result($numero_identificacion, $correo, $rol);
    if (!$stmt->fetch()) {
        throw new Exception("No se encontraron datos del usuario.");
    }
    $stmt->close();
} catch (Exception $e) {
    $error = "Se produjo un error: " . $e->getMessage();
    error_log($error); // Para registrar el error en el log
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Ciber Academy - Estudiante</title>
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
    <h1>Vista de Estudiante</h1>
    <p>Bienvenido, <?php echo $_SESSION['nombre']; ?>!</p>
    <form action="logout.php" method="POST">
      <button type="submit">Cerrar Sesión</button>
    </form>

    <!-- Sección de datos del estudiante -->
    <h2>Tus Datos</h2>
    <table>
        <tr>
            <th>Número de Identificación</th>
            <th>Correo</th>
            <th>Rol</th>
        </tr>
        <tr>
            <td><?php echo htmlspecialchars($numero_identificacion); ?></td>
            <td><?php echo htmlspecialchars($correo); ?></td>
            <td><?php echo htmlspecialchars($rol); ?></td>
        </tr>
    </table>

    <!-- Contenido específico para estudiantes -->
    <h2>Tus Cursos</h2>
    <?php if (!empty($cursos)): ?>
        <table>
            <tr>
                <th>Título</th>
                <th>Descripción</th>
                <th>Fecha de Inscripción</th>
                <th>Nota</th>
            </tr>
            <?php foreach ($cursos as $curso): ?>
                <tr>
                    <td><?php echo htmlspecialchars($curso['titulo']); ?></td>
                    <td><?php echo htmlspecialchars($curso['descripcion']); ?></td>
                    <td><?php echo htmlspecialchars($curso['fecha_inscripcion']); ?></td>
                    <td><?php echo htmlspecialchars($curso['nota']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No estás inscrito en ningún curso.</p>
    <?php endif; ?>
</body>
</html>