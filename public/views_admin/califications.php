<?php
session_start();
if (!isset($_SESSION['correo']) || $_SESSION['rol'] != 'administrador') {
    header("Location: index.php");
    exit();
}

require '../../src/db.php'; // segurarse de que este archivo contiene la conexión a la base de datos

$mensaje = "";

// Obtener todos los usuarios y cursos para los formularios
try {
  $stmt = $conection->prepare("SELECT id, nombre FROM usuarios");
  if (!$stmt) {
      throw new Exception("Error en la preparación de la consulta: " . $conection->error);
  }
  if (!$stmt->execute()) {
      throw new Exception("Error en la ejecución de la consulta: " . $stmt->error);
  }
  $stmt->bind_result($usuario_id, $usuario_nombre);
  $usuarios = [];
  while ($stmt->fetch()) {
      $usuarios[] = ['id' => $usuario_id, 'nombre' => $usuario_nombre];
  }
  $stmt->close();

  $stmt = $conection->prepare("SELECT id, titulo FROM cursos");
  if (!$stmt) {
      throw new Exception("Error en la preparación de la consulta: " . $conection->error);
  }
  if (!$stmt->execute()) {
      throw new Exception("Error en la ejecución de la consulta: " . $stmt->error);
  }
  $stmt->bind_result($curso_id, $curso_titulo);
  $cursos = [];
  while ($stmt->fetch()) {
      $cursos[] = ['id' => $curso_id, 'titulo' => $curso_titulo];
  }
  $stmt->close();
} catch (Exception $e) {
  $mensaje = "Se produjo un error: " . $e->getMessage();
  error_log($mensaje); // Para registrar el error en el log
}

// Procesar el formulario de agregar nota
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_nota'])) {
  $usuario_id = $_POST['usuario_id'];
  $curso_id = $_POST['curso_id'];
  $nota = $_POST['nota'];
  $fecha_asignacion = date('Y-m-d H:i:s'); // Generar el timestamp actual

  try {
      // Insertar la nota en la tabla notas
      $stmt = $conection->prepare("INSERT INTO notas (usuario_id, curso_id, nota, fecha_asignacion) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("iids", $usuario_id, $curso_id, $nota, $fecha_asignacion);
      if (!$stmt->execute()) {
          throw new Exception("Error al insertar la nota: " . $stmt->error);
      }
      $stmt->close();

      $mensaje = "Nota agregada con éxito.";
  } catch (Exception $e) {
      $mensaje = "Se produjo un error: " . $e->getMessage();
      error_log($mensaje); // Para registrar el error en el log
  }
}

// Procesar el formulario de actualizar nota
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_nota'])) {
  $usuario_id = $_POST['usuario_id'];
  $curso_id = $_POST['curso_id'];
  $nota = $_POST['nota'];

  try {
      // Actualizar la nota en la tabla notas
      $stmt = $conection->prepare("UPDATE notas SET nota = ? WHERE usuario_id = ? AND curso_id = ?");
      $stmt->bind_param("dii", $nota, $usuario_id, $curso_id);
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

// Procesar el formulario de eliminar nota
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_nota'])) {
  $usuario_id = $_POST['usuario_id'];
  $curso_id = $_POST['curso_id'];

  try {
      // Eliminar la nota de la tabla notas
      $stmt = $conection->prepare("DELETE FROM notas WHERE usuario_id = ? AND curso_id = ?");
      $stmt->bind_param("ii", $usuario_id, $curso_id);
      if (!$stmt->execute()) {
          throw new Exception("Error al eliminar la nota: " . $stmt->error);
      }
      $stmt->close();

      $mensaje = "Nota eliminada con éxito.";
  } catch (Exception $e) {
      $mensaje = "Se produjo un error: " . $e->getMessage();
      error_log($mensaje); // Para registrar el error en el log
  }
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Ciber Academy - admin/califications</title>
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
  <h1>Vista de Administrador/califications</h1>
  <p>Bienvenido, <?php echo $_SESSION['nombre']; ?>!</p>
  <a href="../admin.php" class="btn">Volver a Panel de Administración</a>

  <!-- Mostrar mensajes -->
  <?php if ($mensaje): ?>
      <p><?php echo htmlspecialchars($mensaje); ?></p>
  <?php endif; ?>

  <!-- CRUD para Notas -->
  <h2>Gestión de Notas</h2>
  <form method="POST" action="">
      <h3>Crear Nota</h3>
      <label for="usuario_id">Usuario:</label>
      <select name="usuario_id" id="usuario_id" required>
          <option value="" disabled selected>Selecciona un usuario</option>
          <?php foreach ($usuarios as $usuario): ?>
              <option value="<?php echo htmlspecialchars($usuario['id']); ?>"><?php echo htmlspecialchars($usuario['nombre']); ?></option>
          <?php endforeach; ?>
      </select>
      <label for="curso_id">Curso:</label>
      <select name="curso_id" id="curso_id" required>
          <option value="" disabled selected>Selecciona un curso</option>
          <?php foreach ($cursos as $curso): ?>
              <option value="<?php echo htmlspecialchars($curso['id']); ?>"><?php echo htmlspecialchars($curso['titulo']); ?></option>
          <?php endforeach; ?>
      </select>
      <label for="nota">Nota:</label>
      <input type="number" step="0.01" name="nota" id="nota" required>
      <button type="submit" name="create_nota">Crear</button>
  </form>

  <form method="POST" action="">
      <h3>Actualizar Nota</h3>
      <label for="usuario_id">Usuario:</label>
      <select name="usuario_id" id="usuario_id" required>
          <option value="" disabled selected>Selecciona un usuario</option>
          <?php foreach ($usuarios as $usuario): ?>
              <option value="<?php echo htmlspecialchars($usuario['id']); ?>"><?php echo htmlspecialchars($usuario['nombre']); ?></option>
          <?php endforeach; ?>
      </select>
      <label for="curso_id">Curso:</label>
      <select name="curso_id" id="curso_id" required>
          <option value="" disabled selected>Selecciona un curso</option>
          <?php foreach ($cursos as $curso): ?>
              <option value="<?php echo htmlspecialchars($curso['id']); ?>"><?php echo htmlspecialchars($curso['titulo']); ?></option>
          <?php endforeach; ?>
      </select>
      <label for="nota">Nota:</label>
      <input type="number" step="0.01" name="nota" id="nota" required>
      <button type="submit" name="update_nota">Actualizar</button>
  </form>

  <form method="POST" action="">
      <h3>Eliminar Nota</h3>
      <label for="usuario_id">Usuario:</label>
      <select name="usuario_id" id="usuario_id" required>
          <option value="" disabled selected>Selecciona un usuario</option>
          <?php foreach ($usuarios as $usuario): ?>
              <option value="<?php echo htmlspecialchars($usuario['id']); ?>"><?php echo htmlspecialchars($usuario['nombre']); ?></option>
          <?php endforeach; ?>
      </select>
      <label for="curso_id">Curso:</label>
      <select name="curso_id" id="curso_id" required>
          <option value="" disabled selected>Selecciona un curso</option>
          <?php foreach ($cursos as $curso): ?>
              <option value="<?php echo htmlspecialchars($curso['id']); ?>"><?php echo htmlspecialchars($curso['titulo']); ?></option>
          <?php endforeach; ?>
      </select>
      <button type="submit" name="delete_nota">Eliminar</button>
  </form>

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

</body>
</html>