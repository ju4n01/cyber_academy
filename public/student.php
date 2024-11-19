<?php
session_start();
if (!isset($_SESSION['correo']) || $_SESSION['rol'] != 'estudiante') {
	header("Location: index.php");
	exit();
}

require '../src/db.php'; // Asegúrate de que este archivo contiene la conexión a la base de datos

$correo = $_SESSION['correo'];
$mensaje = "";

// Procesar el formulario de inscripción
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['curso_id'])) {
	$curso_id = $_POST['curso_id'];
	$fecha_inscripcion = date('Y-m-d H:i:s'); // Generar el timestamp actual

	try {
		// Obtener el usuario_id del estudiante
		$stmt = $conection->prepare("SELECT id FROM usuarios WHERE correo = ?");
		$stmt->bind_param("s", $correo);
		$stmt->execute();
		$stmt->bind_result($usuario_id);
		if (!$stmt->fetch()) {
			throw new Exception("No se encontró el usuario.");
		}
		$stmt->close();

		// Verificar si el estudiante ya está inscrito en el curso
		$stmt = $conection->prepare("SELECT COUNT(*) FROM inscripciones WHERE usuario_id = ? AND curso_id = ?");
		$stmt->bind_param("ii", $usuario_id, $curso_id);
		$stmt->execute();
		$stmt->bind_result($count);
		$stmt->fetch();
		$stmt->close();

		if ($count > 0) {
			throw new Exception("Ya estás inscrito en este curso.");
		}

		// Insertar la inscripción en la tabla inscripciones
		$stmt = $conection->prepare("INSERT INTO inscripciones (usuario_id, curso_id, fecha_inscripcion) VALUES (?, ?, ?)");
		$stmt->bind_param("iis", $usuario_id, $curso_id, $fecha_inscripcion);
		if (!$stmt->execute()) {
			throw new Exception("Error al insertar la inscripción: " . $stmt->error);
		}
		$stmt->close();

		// Redirigir al usuario después de un envío exitoso
		$_SESSION['mensaje'] = "Inscripción realizada con éxito.";
		header("Location: student.php");
		exit();
	} catch (Exception $e) {
		$_SESSION['mensaje'] = "Se produjo un error: " . $e->getMessage();
		header("Location: student.php");
		exit();
	}
}

// Obtener el mensaje de la sesión si existe
if (isset($_SESSION['mensaje'])) {
	$mensaje = $_SESSION['mensaje'];
	unset($_SESSION['mensaje']);
}

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

	// Obtener todos los cursos disponibles para la inscripción
	$stmt = $conection->prepare("SELECT id, titulo FROM cursos");
	if (!$stmt) {
		throw new Exception("Error en la preparación de la consulta: " . $conection->error);
	}
	if (!$stmt->execute()) {
		throw new Exception("Error en la ejecución de la consulta: " . $stmt->error);
	}
	$stmt->bind_result($curso_id, $curso_titulo);
	$cursos_disponibles = [];
	while ($stmt->fetch()) {
		$cursos_disponibles[] = ['id' => $curso_id, 'titulo' => $curso_titulo];
	}
	$stmt->close();
} catch (Exception $e) {
	$mensaje = "Se produjo un error: " . $e->getMessage();
	error_log($mensaje); // Para registrar el error en el log
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
		table,
		th,
		td {
			border: 1px solid black;
			border-collapse: collapse;
		}

		th,
		td {
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

	<!-- Formulario para inscribirse a nuevos cursos -->
	<h2>Inscribirse a Nuevos Cursos</h2>
	<?php if ($mensaje): ?>
		<p><?php echo htmlspecialchars($mensaje); ?></p>
	<?php endif; ?>
	<form method="POST" action="">
		<label for="curso_id">Selecciona un curso:</label>
		<select name="curso_id" id="curso_id" required>
			<option value="" disabled selected>Selecciona un curso</option>
			<?php foreach ($cursos_disponibles as $curso): ?>
				<option value="<?php echo htmlspecialchars($curso['id']); ?>"><?php echo htmlspecialchars($curso['titulo']); ?></option>
			<?php endforeach; ?>
		</select>
		<br>
		<button type="submit">Inscribirse</button>
	</form>
</body>

</html>