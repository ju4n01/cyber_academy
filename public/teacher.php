<?php
session_start();
if (!isset($_SESSION['correo']) || $_SESSION['rol'] != 'profesor') {
	header("Location: index.php");
	exit();
}

require '../src/db.php'; // Asegúrate de que este archivo contiene la conexión a la base de datos

$mensaje = "";

// Procesar el formulario de actualizar nota
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_nota'])) {
	$nota_id = $_POST['nota_id'];
	$nota = $_POST['nota'];

	try {
		// Actualizar la nota en la tabla notas
		$stmt = $conection->prepare("UPDATE notas SET nota = ? WHERE id = ?");
		$stmt->bind_param("di", $nota, $nota_id);
		if (!$stmt->execute()) {
			throw new Exception("Error al actualizar la nota: " . $stmt->error);
		}
		$stmt->close();

		$mensaje = "Nota actualizada con éxito.";
	} catch (Exception $e) {
		$mensaje = "Se produjo un error: " . $e->getMessage();
		error_log($mensaje); // Para registrar el error en el log
	}
}

// Obtener todos los usuarios
try {
	$stmt = $conection->prepare("SELECT id, nombre, numero_identificacion, correo, rol FROM usuarios");
	if (!$stmt) {
		throw new Exception("Error en la preparación de la consulta: " . $conection->error);
	}
	if (!$stmt->execute()) {
		throw new Exception("Error en la ejecución de la consulta: " . $stmt->error);
	}
	$stmt->bind_result($usuario_id, $nombre, $numero_identificacion, $correo, $rol);
	$usuarios = [];
	while ($stmt->fetch()) {
		$usuarios[] = [
			'id' => $usuario_id,
			'nombre' => $nombre,
			'numero_identificacion' => $numero_identificacion,
			'correo' => $correo,
			'rol' => $rol
		];
	}
	$stmt->close();
} catch (Exception $e) {
	$mensaje = "Se produjo un error: " . $e->getMessage();
	error_log($mensaje); // Para registrar el error en el log
}

// Obtener todas las inscripciones
try {
	$stmt = $conection->prepare("
        SELECT i.id, u.nombre AS usuario_nombre, c.titulo AS curso_titulo, i.fecha_inscripcion
        FROM inscripciones i
        JOIN usuarios u ON i.usuario_id = u.id
        JOIN cursos c ON i.curso_id = c.id
    ");
	if (!$stmt) {
		throw new Exception("Error en la preparación de la consulta: " . $conection->error);
	}
	if (!$stmt->execute()) {
		throw new Exception("Error en la ejecución de la consulta: " . $stmt->error);
	}
	$stmt->bind_result($inscripcion_id, $usuario_nombre, $curso_titulo, $fecha_inscripcion);
	$inscripciones = [];
	while ($stmt->fetch()) {
		$inscripciones[] = [
			'id' => $inscripcion_id,
			'usuario_nombre' => $usuario_nombre,
			'curso_titulo' => $curso_titulo,
			'fecha_inscripcion' => $fecha_inscripcion
		];
	}
	$stmt->close();
} catch (Exception $e) {
	$mensaje = "Se produjo un error: " . $e->getMessage();
	error_log($mensaje); // Para registrar el error en el log
}

// Obtener todos los cursos
try {
	$stmt = $conection->prepare("SELECT id, titulo, descripcion FROM cursos");
	if (!$stmt) {
		throw new Exception("Error en la preparación de la consulta: " . $conection->error);
	}
	if (!$stmt->execute()) {
		throw new Exception("Error en la ejecución de la consulta: " . $stmt->error);
	}
	$stmt->bind_result($curso_id, $titulo, $descripcion);
	$cursos = [];
	while ($stmt->fetch()) {
		$cursos[] = [
			'id' => $curso_id,
			'titulo' => $titulo,
			'descripcion' => $descripcion
		];
	}
	$stmt->close();
} catch (Exception $e) {
	$mensaje = "Se produjo un error: " . $e->getMessage();
	error_log($mensaje); // Para registrar el error en el log
}

// Obtener todas las notas
try {
	$stmt = $conection->prepare("
        SELECT n.id, u.nombre AS usuario_nombre, c.titulo AS curso_titulo, n.nota, n.fecha_asignacion
        FROM notas n
        JOIN usuarios u ON n.usuario_id = u.id
        JOIN cursos c ON n.curso_id = c.id
    ");
	if (!$stmt) {
		throw new Exception("Error en la preparación de la consulta: " . $conection->error);
	}
	if (!$stmt->execute()) {
		throw new Exception("Error en la ejecución de la consulta: " . $stmt->error);
	}
	$stmt->bind_result($nota_id, $usuario_nombre, $curso_titulo, $nota, $fecha_asignacion);
	$notas = [];
	while ($stmt->fetch()) {
		$notas[] = [
			'id' => $nota_id,
			'usuario_nombre' => $usuario_nombre,
			'curso_titulo' => $curso_titulo,
			'nota' => $nota,
			'fecha_asignacion' => $fecha_asignacion
		];
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
	<title>Ciber Academy - Profesor</title>
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
	<h1>Vista de Profesor</h1>
	<p>Bienvenido, <?php echo $_SESSION['nombre']; ?>!</p>
	<form action="logout.php" method="POST">
		<button type="submit">Cerrar Sesión</button>
	</form>

	<!-- Mostrar mensajes -->
	<?php if ($mensaje): ?>
		<p><?php echo htmlspecialchars($mensaje); ?></p>
	<?php endif; ?>

	<!-- Listar usuarios -->
	<h2>Usuarios</h2>
	<table>
		<tr>
			<th>ID</th>
			<th>Nombre</th>
			<th>Número de Identificación</th>
			<th>Correo</th>
			<th>Rol</th>
		</tr>
		<?php foreach ($usuarios as $usuario): ?>
			<tr>
				<td><?php echo htmlspecialchars($usuario['id']); ?></td>
				<td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
				<td><?php echo htmlspecialchars($usuario['numero_identificacion']); ?></td>
				<td><?php echo htmlspecialchars($usuario['correo']); ?></td>
				<td><?php echo htmlspecialchars($usuario['rol']); ?></td>
			</tr>
		<?php endforeach; ?>
	</table>

	<!-- Listar inscripciones -->
	<h2>Inscripciones</h2>
	<table>
		<tr>
			<th>ID</th>
			<th>Usuario</th>
			<th>Curso</th>
			<th>Fecha de Inscripción</th>
		</tr>
		<?php foreach ($inscripciones as $inscripcion): ?>
			<tr>
				<td><?php echo htmlspecialchars($inscripcion['id']); ?></td>
				<td><?php echo htmlspecialchars($inscripcion['usuario_nombre']); ?></td>
				<td><?php echo htmlspecialchars($inscripcion['curso_titulo']); ?></td>
				<td><?php echo htmlspecialchars($inscripcion['fecha_inscripcion']); ?></td>
			</tr>
		<?php endforeach; ?>
	</table>

	<!-- Listar cursos -->
	<h2>Cursos</h2>
	<table>
		<tr>
			<th>ID</th>
			<th>Título</th>
			<th>Descripción</th>
		</tr>
		<?php foreach ($cursos as $curso): ?>
			<tr>
				<td><?php echo htmlspecialchars($curso['id']); ?></td>
				<td><?php echo htmlspecialchars($curso['titulo']); ?></td>
				<td><?php echo htmlspecialchars($curso['descripcion']); ?></td>
			</tr>
		<?php endforeach; ?>
	</table>

	<!-- Listar notas -->
	<h2>Notas</h2>
	<table>
		<tr>
			<th>ID</th>
			<th>Usuario</th>
			<th>Curso</th>
			<th>Nota</th>
			<th>Fecha de Asignación</th>
		</tr>
		<?php foreach ($notas as $nota): ?>
			<tr>
				<td><?php echo htmlspecialchars($nota['id']); ?></td>
				<td><?php echo htmlspecialchars($nota['usuario_nombre']); ?></td>
				<td><?php echo htmlspecialchars($nota['curso_titulo']); ?></td>
				<td><?php echo htmlspecialchars($nota['nota']); ?></td>
				<td><?php echo htmlspecialchars($nota['fecha_asignacion']); ?></td>
			</tr>
		<?php endforeach; ?>
	</table>

	<!-- Formulario para actualizar nota -->
	<h2>Actualizar Nota</h2>
	<form method="POST" action="">
		<label for="nota_id">Nota:</label>
		<select name="nota_id" id="nota_id" required>
			<option value="" disabled selected>Selecciona una nota</option>
			<?php foreach ($notas as $nota): ?>
				<option value="<?php echo htmlspecialchars($nota['id']); ?>"><?php echo htmlspecialchars($nota['usuario_nombre'] . " - " . $nota['curso_titulo']); ?></option>
			<?php endforeach; ?>
		</select>
		<label for="nota">Nota:</label>
		<input type="number" step="0.01" name="nota" id="nota" required>
		<button type="submit" name="update_nota">Actualizar</button>
	</form>
</body>

</html>