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

// Procesar el formulario de agregar curso
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_curso'])) {
  $titulo = $_POST['titulo'];
  $descripcion = $_POST['descripcion'];

  try {
      // Insertar el curso en la tabla cursos
      $stmt = $conection->prepare("INSERT INTO cursos (titulo, descripcion) VALUES (?, ?)");
      $stmt->bind_param("ss", $titulo, $descripcion);
      if (!$stmt->execute()) {
          throw new Exception("Error al insertar el curso: " . $stmt->error);
      }
      $stmt->close();

      $mensaje = "Curso agregado con éxito.";
  } catch (Exception $e) {
      $mensaje = "Se produjo un error: " . $e->getMessage();
      error_log($mensaje); // Para registrar el error en el log
  }
}

// Procesar el formulario de actualizar curso
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_curso'])) {
  $curso_id = $_POST['curso_id'];
  $titulo = $_POST['titulo'];
  $descripcion = $_POST['descripcion'];

  try {
      // Actualizar el curso en la tabla cursos
      $stmt = $conection->prepare("UPDATE cursos SET titulo = ?, descripcion = ? WHERE id = ?");
      $stmt->bind_param("ssi", $titulo, $descripcion, $curso_id);
      if (!$stmt->execute()) {
          throw new Exception("Error al actualizar el curso: " . $stmt->error);
      }
      $stmt->close();

      $mensaje = "Curso actualizado con éxito.";
  } catch (Exception $e) {
      $mensaje = "Se produjo un error: " . $e->getMessage();
      error_log($mensaje); // Para registrar el error en el log
  }
}

// Procesar el formulario de eliminar curso
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_curso'])) {
  $curso_id = $_POST['curso_id'];

  try {
      // Eliminar el curso de la tabla cursos
      $stmt = $conection->prepare("DELETE FROM cursos WHERE id = ?");
      $stmt->bind_param("i", $curso_id);
      if (!$stmt->execute()) {
          throw new Exception("Error al eliminar el curso: " . $stmt->error);
      }
      $stmt->close();

      $mensaje = "Curso eliminado con éxito.";
  } catch (Exception $e) {
      $mensaje = "Se produjo un error: " . $e->getMessage();
      error_log($mensaje); // Para registrar el error en el log
  }
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Ciber Academy - admin/courses</title>
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
  <h1>Vista de Administrador/courses</h1>
  <p>Bienvenido, <?php echo $_SESSION['nombre']; ?>!</p>
  <a href="../admin.php" class="btn">Volver a Panel de Administración</a>

  <!-- Mostrar mensajes -->
  <?php if ($mensaje): ?>
      <p><?php echo htmlspecialchars($mensaje); ?></p>
  <?php endif; ?>

  
  <h2>Gestión de Cursos</h2>
  <form method="POST" action="">
      <h3>Crear Curso</h3>
      <label for="titulo">Título:</label>
      <input type="text" name="titulo" id="titulo" required>
      <label for="descripcion">Descripción:</label>
      <input type="text" name="descripcion" id="descripcion">
      <button type="submit" name="create_curso">Crear</button>
  </form>

  <form method="POST" action="">
      <h3>Actualizar Curso</h3>
      <label for="curso_id">Curso:</label>
      <select name="curso_id" id="curso_id" required>
          <option value="" disabled selected>Selecciona un curso</option>
          <?php foreach ($cursos as $curso): ?>
              <option value="<?php echo htmlspecialchars($curso['id']); ?>"><?php echo htmlspecialchars($curso['titulo']); ?></option>
          <?php endforeach; ?>
      </select>
      <label for="titulo">Título:</label>
      <input type="text" name="titulo" id="titulo" required>
      <label for="descripcion">Descripción:</label>
      <input type="text" name="descripcion" id="descripcion">
      <button type="submit" name="update_curso">Actualizar</button>
  </form>

  <form method="POST" action="">
      <h3>Eliminar Curso</h3>
      <label for="curso_id">Curso:</label>
      <select name="curso_id" id="curso_id" required>
          <option value="" disabled selected>Selecciona un curso</option>
          <?php foreach ($cursos as $curso): ?>
              <option value="<?php echo htmlspecialchars($curso['id']); ?>"><?php echo htmlspecialchars($curso['titulo']); ?></option>
          <?php endforeach; ?>
      </select>
      <button type="submit" name="delete_curso">Eliminar</button>
  </form>

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

</body>
</html>