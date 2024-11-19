<?php
session_start();
if (!isset($_SESSION['correo']) || $_SESSION['rol'] != 'administrador') {
  header("Location: index.php");
  exit();
}

require '../../src/db.php'; // segurarse de que este archivo contiene la conexión a la base de datos

$mensaje = "";

// Obtener todos los tipos de documento para los formularios
try {
  $stmt = $conection->prepare("SELECT id, tipo FROM tipos_documento");
  if (!$stmt) {
    throw new Exception("Error en la preparación de la consulta: " . $conection->error);
  }
  if (!$stmt->execute()) {
    throw new Exception("Error en la ejecución de la consulta: " . $stmt->error);
  }
  $stmt->bind_result($tipo_documento_id, $tipo);
  $tipos_documento = [];
  while ($stmt->fetch()) {
    $tipos_documento[] = [
      'id' => $tipo_documento_id,
      'tipo' => $tipo
    ];
  }
  $stmt->close();
} catch (Exception $e) {
  $mensaje = "Se produjo un error: " . $e->getMessage();
  error_log($mensaje); // Para registrar el error en el log
}

// Procesar el formulario de agregar inscripción
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_inscripcion'])) {
  $usuario_id = $_POST['usuario_id'];
  $curso_id = $_POST['curso_id'];
  $fecha_inscripcion = date('Y-m-d H:i:s'); // Generar el timestamp actual

  try {
    // Insertar la inscripción en la tabla inscripciones
    $stmt = $conection->prepare("INSERT INTO inscripciones (usuario_id, curso_id, fecha_inscripcion) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $usuario_id, $curso_id, $fecha_inscripcion);
    if (!$stmt->execute()) {
      throw new Exception("Error al insertar la inscripción: " . $stmt->error);
    }
    $stmt->close();

    $mensaje = "Inscripción agregada con éxito.";
  } catch (Exception $e) {
    $mensaje = "Se produjo un error: " . $e->getMessage();
    error_log($mensaje); // Para registrar el error en el log
  }
}

// Procesar el formulario de actualizar inscripción
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_inscripcion'])) {
  $inscripcion_id = $_POST['inscripcion_id'];
  $usuario_id = $_POST['usuario_id'];
  $curso_id = $_POST['curso_id'];

  try {
    // Actualizar la inscripción en la tabla inscripciones
    $stmt = $conection->prepare("UPDATE inscripciones SET usuario_id = ?, curso_id = ? WHERE id = ?");
    $stmt->bind_param("iii", $usuario_id, $curso_id, $inscripcion_id);
    if (!$stmt->execute()) {
      throw new Exception("Error al actualizar la inscripción: " . $stmt->error);
    }
    $stmt->close();

    $mensaje = "Inscripción actualizada con éxito.";
  } catch (Exception $e) {
    $mensaje = "Se produjo un error: " . $e->getMessage();
    error_log($mensaje); // Para registrar el error en el log
  }
}

// Procesar el formulario de eliminar inscripción
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_inscripcion'])) {
  $inscripcion_id = $_POST['inscripcion_id'];

  try {
    // Eliminar la inscripción de la tabla inscripciones
    $stmt = $conection->prepare("DELETE FROM inscripciones WHERE id = ?");
    $stmt->bind_param("i", $inscripcion_id);
    if (!$stmt->execute()) {
      throw new Exception("Error al eliminar la inscripción: " . $stmt->error);
    }
    $stmt->close();

    $mensaje = "Inscripción eliminada con éxito.";
  } catch (Exception $e) {
    $mensaje = "Se produjo un error: " . $e->getMessage();
    error_log($mensaje); // Para registrar el error en el log
  }
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

// Obtener todos los usuarios para los formularios de inscripciones
try {
  $stmt = $conection->prepare("SELECT id, nombre FROM usuarios");
  if (!$stmt) {
    throw new Exception("Error en la preparación de la consulta: " . $conection->error);
  }
  if (!$stmt->execute()) {
    throw new Exception("Error en la ejecución de la consulta: " . $stmt->error);
  }
  $stmt->bind_result($usuario_id, $usuario_nombre);
  $usuarios_inscripciones = [];
  while ($stmt->fetch()) {
    $usuarios_inscripciones[] = ['id' => $usuario_id, 'nombre' => $usuario_nombre];
  }
  $stmt->close();
} catch (Exception $e) {
  $mensaje = "Se produjo un error: " . $e->getMessage();
  error_log($mensaje); // Para registrar el error en el log
}

// Obtener todos los cursos para los formularios de inscripciones
try {
  $stmt = $conection->prepare("SELECT id, titulo FROM cursos");
  if (!$stmt) {
    throw new Exception("Error en la preparación de la consulta: " . $conection->error);
  }
  if (!$stmt->execute()) {
    throw new Exception("Error en la ejecución de la consulta: " . $stmt->error);
  }
  $stmt->bind_result($curso_id, $curso_titulo);
  $cursos_inscripciones = [];
  while ($stmt->fetch()) {
    $cursos_inscripciones[] = ['id' => $curso_id, 'titulo' => $curso_titulo];
  }
  $stmt->close();
} catch (Exception $e) {
  $mensaje = "Se produjo un error: " . $e->getMessage();
  error_log($mensaje); // Para registrar el error en el log
}

// Obtener todos los tipos de documento para los formularios
try {
  $stmt = $conection->prepare("SELECT id, tipo FROM tipos_documento");
  if (!$stmt) {
    throw new Exception("Error en la preparación de la consulta: " . $conection->error);
  }
  if (!$stmt->execute()) {
    throw new Exception("Error en la ejecución de la consulta: " . $stmt->error);
  }
  $stmt->bind_result($tipo_documento_id, $tipo);
  $tipos_documento = [];
  while ($stmt->fetch()) {
    $tipos_documento[] = [
      'id' => $tipo_documento_id,
      'tipo' => $tipo
    ];
  }
  $stmt->close();
} catch (Exception $e) {
  $mensaje = "Se produjo un error: " . $e->getMessage();
  error_log($mensaje); // Para registrar el error en el log
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/styles.css">
  <title>Ciber Academy - admin/inscriptions</title>
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
  <h1>Vista de Administrador/inscriptions</h1>
  <p>Bienvenido, <?php echo $_SESSION['nombre']; ?>!</p>
  <a href="../admin.php" class="btn">Volver a Panel de Administración</a>

  <!-- Mostrar mensajes -->
  <?php if ($mensaje): ?>
    <p><?php echo htmlspecialchars($mensaje); ?></p>
  <?php endif; ?>

  <!-- CRUD para Inscripciones -->
  <h2>Gestión de Inscripciones</h2>
  <form method="POST" action="">
    <h3>Crear Inscripción</h3>
    <label for="usuario_id">Usuario:</label>
    <select name="usuario_id" id="usuario_id" required>
      <option value="" disabled selected>Selecciona un usuario</option>
      <?php foreach ($usuarios_inscripciones as $usuario): ?>
        <option value="<?php echo htmlspecialchars($usuario['id']); ?>"><?php echo htmlspecialchars($usuario['nombre']); ?></option>
      <?php endforeach; ?>
    </select>
    <label for="curso_id">Curso:</label>
    <select name="curso_id" id="curso_id" required>
      <option value="" disabled selected>Selecciona un curso</option>
      <?php foreach ($cursos_inscripciones as $curso): ?>
        <option value="<?php echo htmlspecialchars($curso['id']); ?>"><?php echo htmlspecialchars($curso['titulo']); ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" name="create_inscripcion">Crear</button>
  </form>

  <form method="POST" action="">
    <h3>Actualizar Inscripción</h3>
    <label for="inscripcion_id">Inscripción:</label>
    <select name="inscripcion_id" id="inscripcion_id" required>
      <option value="" disabled selected>Selecciona una inscripción</option>
      <?php foreach ($inscripciones as $inscripcion): ?>
        <option value="<?php echo htmlspecialchars($inscripcion['id']); ?>"><?php echo htmlspecialchars($inscripcion['usuario_nombre'] . " - " . $inscripcion['curso_titulo']); ?></option>
      <?php endforeach; ?>
    </select>
    <label for="usuario_id">Usuario:</label>
    <select name="usuario_id" id="usuario_id" required>
      <option value="" disabled selected>Selecciona un usuario</option>
      <?php foreach ($usuarios_inscripciones as $usuario): ?>
        <option value="<?php echo htmlspecialchars($usuario['id']); ?>"><?php echo htmlspecialchars($usuario['nombre']); ?></option>
      <?php endforeach; ?>
    </select>
    <label for="curso_id">Curso:</label>
    <select name="curso_id" id="curso_id" required>
      <option value="" disabled selected>Selecciona un curso</option>
      <?php foreach ($cursos_inscripciones as $curso): ?>
        <option value="<?php echo htmlspecialchars($curso['id']); ?>"><?php echo htmlspecialchars($curso['titulo']); ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" name="update_inscripcion">Actualizar</button>
  </form>

  <form method="POST" action="">
    <h3>Eliminar Inscripción</h3>
    <label for="inscripcion_id">Inscripción:</label>
    <select name="inscripcion_id" id="inscripcion_id" required>
      <option value="" disabled selected>Selecciona una inscripción</option>
      <?php foreach ($inscripciones as $inscripcion): ?>
        <option value="<?php echo htmlspecialchars($inscripcion['id']); ?>"><?php echo htmlspecialchars($inscripcion['usuario_nombre'] . " - " . $inscripcion['curso_titulo']); ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" name="delete_inscripcion">Eliminar</button>
  </form>

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

</body>

</html>