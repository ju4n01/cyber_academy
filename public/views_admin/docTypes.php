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

// Procesar el formulario de agregar tipo de documento
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_tipo_documento'])) {
  $tipo = $_POST['tipo'];

  try {
    // Insertar el tipo de documento en la tabla tipos_documento
    $stmt = $conection->prepare("INSERT INTO tipos_documento (tipo) VALUES (?)");
    $stmt->bind_param("s", $tipo);
    if (!$stmt->execute()) {
      throw new Exception("Error al insertar el tipo de documento: " . $stmt->error);
    }
    $stmt->close();

    $mensaje = "Tipo de documento agregado con éxito.";
  } catch (Exception $e) {
    $mensaje = "Se produjo un error: " . $e->getMessage();
    error_log($mensaje); // Para registrar el error en el log
  }
}

// Procesar el formulario de actualizar tipo de documento
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_tipo_documento'])) {
  $tipo_id = $_POST['tipo_id'];
  $tipo = $_POST['tipo'];

  try {
    // Actualizar el tipo de documento en la tabla tipos_documento
    $stmt = $conection->prepare("UPDATE tipos_documento SET tipo = ? WHERE id = ?");
    $stmt->bind_param("si", $tipo, $tipo_id);
    if (!$stmt->execute()) {
      throw new Exception("Error al actualizar el tipo de documento: " . $stmt->error);
    }
    $stmt->close();

    $mensaje = "Tipo de documento actualizado con éxito.";
  } catch (Exception $e) {
    $mensaje = "Se produjo un error: " . $e->getMessage();
    error_log($mensaje); // Para registrar el error en el log
  }
}

// Procesar el formulario de eliminar tipo de documento
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_tipo_documento'])) {
  $tipo_id = $_POST['tipo_id'];

  try {
    // Eliminar el tipo de documento de la tabla tipos_documento
    $stmt = $conection->prepare("DELETE FROM tipos_documento WHERE id = ?");
    $stmt->bind_param("i", $tipo_id);
    if (!$stmt->execute()) {
      throw new Exception("Error al eliminar el tipo de documento: " . $stmt->error);
    }
    $stmt->close();

    $mensaje = "Tipo de documento eliminado con éxito.";
  } catch (Exception $e) {
    $mensaje = "Se produjo un error: " . $e->getMessage();
    error_log($mensaje); // Para registrar el error en el log
  }
}

// Obtener todos los tipos de documento
try {
  $stmt = $conection->prepare("SELECT id, tipo FROM tipos_documento");
  if (!$stmt) {
    throw new Exception("Error en la preparación de la consulta: " . $conection->error);
  }
  if (!$stmt->execute()) {
    throw new Exception("Error en la ejecución de la consulta: " . $stmt->error);
  }
  $stmt->bind_result($tipo_id, $tipo);
  $tipos_documento = [];
  while ($stmt->fetch()) {
    $tipos_documento[] = [
      'id' => $tipo_id,
      'tipo' => $tipo
    ];
  }
  $stmt->close();
} catch (Exception $e) {
  $mensaje = "Se produjo un error: " . $e->getMessage();
  error_log($mensaje); // Para registrar el error en el log
}

// Obtener todos los tipos de documento para los formularios
// TODO - ¿instrucción necesaria en este archivo docTypes.php?
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
  <title>Ciber Academy - admin/docTypes</title>
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
  <h1>Vista de Administrador/docTypes</h1>
  <p>Bienvenido, <?php echo $_SESSION['nombre']; ?>!</p>
  <a href="../admin.php" class="btn">Volver a Panel de Administración</a>

  <!-- Mostrar mensajes -->
  <?php if ($mensaje): ?>
    <p><?php echo htmlspecialchars($mensaje); ?></p>
  <?php endif; ?>

  <!-- CRUD para Tipos de Documento -->
  <h2>Gestión de Tipos de Documento</h2>
  <form method="POST" action="">
    <h3>Crear Tipo de Documento</h3>
    <label for="tipo">Tipo:</label>
    <input type="text" name="tipo" id="tipo" required>
    <button type="submit" name="create_tipo_documento">Crear</button>
  </form>

  <form method="POST" action="">
    <h3>Actualizar Tipo de Documento</h3>
    <label for="tipo_id">Tipo de Documento:</label>
    <select name="tipo_id" id="tipo_id" required>
      <option value="" disabled selected>Selecciona un tipo de documento</option>
      <?php foreach ($tipos_documento as $tipo_documento): ?>
        <option value="<?php echo htmlspecialchars($tipo_documento['id']); ?>"><?php echo htmlspecialchars($tipo_documento['tipo']); ?></option>
      <?php endforeach; ?>
    </select>
    <label for="tipo">Tipo:</label>
    <input type="text" name="tipo" id="tipo" required>
    <button type="submit" name="update_tipo_documento">Actualizar</button>
  </form>

  <form method="POST" action="">
    <h3>Eliminar Tipo de Documento</h3>
    <label for="tipo_id">Tipo de Documento:</label>
    <select name="tipo_id" id="tipo_id" required>
      <option value="" disabled selected>Selecciona un tipo de documento</option>
      <?php foreach ($tipos_documento as $tipo_documento): ?>
        <option value="<?php echo htmlspecialchars($tipo_documento['id']); ?>"><?php echo htmlspecialchars($tipo_documento['tipo']); ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" name="delete_tipo_documento">Eliminar</button>
  </form>

  <!-- Listar tipos de documento -->
  <h2>Tipos de Documento</h2>
  <table>
    <tr>
      <th>ID</th>
      <th>Tipo</th>
    </tr>
    <?php foreach ($tipos_documento as $tipo_documento): ?>
      <tr>
        <td><?php echo htmlspecialchars($tipo_documento['id']); ?></td>
        <td><?php echo htmlspecialchars($tipo_documento['tipo']); ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

</body>

</html>