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

// Procesar el formulario de agregar usuario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_usuario'])) {
  $nombre = $_POST['nombre'];
  $numero_identificacion = $_POST['numero_identificacion'];
  $tipo_documento_id = $_POST['tipo_documento_id'];
  $correo = $_POST['correo'];
  $clave = $_POST['clave']; // No encriptar la clave
  $rol = $_POST['rol'];
  $fecha_registro = date('Y-m-d H:i:s'); // Generar el timestamp actual

  try {
    // Insertar el usuario en la tabla usuarios
    $stmt = $conection->prepare("INSERT INTO usuarios (nombre, numero_identificacion, tipo_documento_id, correo, clave, rol, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssissss", $nombre, $numero_identificacion, $tipo_documento_id, $correo, $clave, $rol, $fecha_registro);
    if (!$stmt->execute()) {
      throw new Exception("Error al insertar el usuario: " . $stmt->error);
    }
    $stmt->close();

    $mensaje = "Usuario agregado con éxito.";
  } catch (Exception $e) {
    $mensaje = "Se produjo un error: " . $e->getMessage();
    error_log($mensaje); // Para registrar el error en el log
  }
}

// Procesar el formulario de actualizar usuario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_usuario'])) {
  $usuario_id = $_POST['usuario_id'];
  $nombre = $_POST['nombre'];
  $numero_identificacion = $_POST['numero_identificacion'];
  $tipo_documento_id = $_POST['tipo_documento_id'];
  $correo = $_POST['correo'];
  $clave = $_POST['clave']; // No encriptar la clave
  $rol = $_POST['rol'];

  try {
    // Actualizar el usuario en la tabla usuarios
    $stmt = $conection->prepare("UPDATE usuarios SET nombre = ?, numero_identificacion = ?, tipo_documento_id = ?, correo = ?, clave = ?, rol = ? WHERE id = ?");
    $stmt->bind_param("ssisssi", $nombre, $numero_identificacion, $tipo_documento_id, $correo, $clave, $rol, $usuario_id);
    if (!$stmt->execute()) {
      throw new Exception("Error al actualizar el usuario: " . $stmt->error);
    }
    $stmt->close();

    $mensaje = "Usuario actualizado con éxito.";
  } catch (Exception $e) {
    $mensaje = "Se produjo un error: " . $e->getMessage();
    error_log($mensaje); // Para registrar el error en el log
  }
}

// Procesar el formulario de eliminar usuario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_usuario'])) {
  $usuario_id = $_POST['usuario_id'];

  try {
    // Eliminar el usuario de la tabla usuarios
    $stmt = $conection->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    if (!$stmt->execute()) {
      throw new Exception("Error al eliminar el usuario: " . $stmt->error);
    }
    $stmt->close();

    $mensaje = "Usuario eliminado con éxito.";
  } catch (Exception $e) {
    $mensaje = "Se produjo un error: " . $e->getMessage();
    error_log($mensaje); // Para registrar el error en el log
  }
}

// Obtener todos los usuarios
try {
  $stmt = $conection->prepare("
      SELECT u.id, u.nombre, u.numero_identificacion, t.tipo AS tipo_documento, u.correo, u.clave, u.rol, u.fecha_registro
      FROM usuarios u
      JOIN tipos_documento t ON u.tipo_documento_id = t.id
  ");
  if (!$stmt) {
    throw new Exception("Error en la preparación de la consulta: " . $conection->error);
  }
  if (!$stmt->execute()) {
    throw new Exception("Error en la ejecución de la consulta: " . $stmt->error);
  }
  $stmt->bind_result($usuario_id, $nombre, $numero_identificacion, $tipo_documento, $correo, $clave, $rol, $fecha_registro);
  $usuarios = [];
  while ($stmt->fetch()) {
    $usuarios[] = [
      'id' => $usuario_id,
      'nombre' => $nombre,
      'numero_identificacion' => $numero_identificacion,
      'tipo_documento' => $tipo_documento,
      'correo' => $correo,
      'clave' => $clave,
      'rol' => $rol,
      'fecha_registro' => $fecha_registro
    ];
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
  <title>Ciber Academy - admin/users</title>
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
  <h1>Vista de Administrador/users</h1>
  <p>Bienvenido, <?php echo $_SESSION['nombre']; ?>!</p>
  <a href="../admin.php" class="btn">Volver a Panel de Administración</a>

  <!-- Mostrar mensajes -->
  <?php if ($mensaje): ?>
    <p><?php echo htmlspecialchars($mensaje); ?></p>
  <?php endif; ?>


  <!-- CRUD para Usuarios -->
  <h2>Gestión de Usuarios</h2>
  <form method="POST" action="">
    <h3>Crear Usuario</h3>
    <label for="nombre">Nombre:</label>
    <input type="text" name="nombre" id="nombre" required>
    <label for="numero_identificacion">Número de Identificación:</label>
    <input type="text" name="numero_identificacion" id="numero_identificacion" required>
    <label for="tipo_documento_id">Tipo de Documento:</label>
    <select name="tipo_documento_id" id="tipo_documento_id" required>
      <option value="" disabled selected>Selecciona una opción</option>
      <?php foreach ($tipos_documento as $tipo_documento): ?>
        <option value="<?php echo htmlspecialchars($tipo_documento['id']); ?>"><?php echo htmlspecialchars($tipo_documento['tipo']); ?></option>
      <?php endforeach; ?>
    </select>
    <label for="correo">Correo:</label>
    <input type="email" name="correo" id="correo" required>
    <label for="clave">Clave:</label>
    <input type="password" name="clave" id="clave" required>
    <label for="rol">Rol:</label>
    <select name="rol" id="rol" required>
      <option value="" disabled selected>Selecciona una opción</option>
      <option value="estudiante">Estudiante</option>
      <option value="profesor">Profesor</option>
      <option value="administrador">Administrador</option>
    </select>
    <button type="submit" name="create_usuario">Crear</button>
  </form>

  <form method="POST" action="">
    <h3>Actualizar Usuario</h3>
    <label for="usuario_id">Usuario:</label>
    <select name="usuario_id" id="usuario_id" required>
      <option value="" disabled selected>Selecciona una opción</option>
      <?php foreach ($usuarios as $usuario): ?>
        <option value="<?php echo htmlspecialchars($usuario['id']); ?>"><?php echo htmlspecialchars($usuario['nombre']); ?></option>
      <?php endforeach; ?>
    </select>
    <label for="nombre">Nombre:</label>
    <input type="text" name="nombre" id="nombre" required>
    <label for="numero_identificacion">Número de Identificación:</label>
    <input type="text" name="numero_identificacion" id="numero_identificacion" required>
    <label for="tipo_documento_id">Tipo de Documento:</label>
    <select name="tipo_documento_id" id="tipo_documento_id" required>
      <option value="" disabled selected>Selecciona una opción</option>
      <?php foreach ($tipos_documento as $tipo_documento): ?>
        <option value="<?php echo htmlspecialchars($tipo_documento['id']); ?>"><?php echo htmlspecialchars($tipo_documento['tipo']); ?></option>
      <?php endforeach; ?>
    </select>
    <label for="correo">Correo:</label>
    <input type="email" name="correo" id="correo" required>
    <label for="clave">Clave:</label>
    <input type="password" name="clave" id="clave" required> <!-- Campo de clave sin encriptar -->
    <label for="rol">Rol:</label>
    <select name="rol" id="rol" required>
      <option value="" disabled selected>Selecciona una opción</option>
      <option value="estudiante">Estudiante</option>
      <option value="profesor">Profesor</option>
      <option value="administrador">Administrador</option>
    </select>
    <button type="submit" name="update_usuario">Actualizar</button>
  </form>

  <form method="POST" action="">
    <h3>Eliminar Usuario</h3>
    <label for="usuario_id">Usuario:</label>
    <select name="usuario_id" id="usuario_id" required>
      <option value="" disabled selected>Selecciona una opción</option>
      <?php foreach ($usuarios as $usuario): ?>
        <option value="<?php echo htmlspecialchars($usuario['id']); ?>"><?php echo htmlspecialchars($usuario['nombre']); ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" name="delete_usuario">Eliminar</button>
  </form>

  <!-- Listar usuarios -->
  <h2>Usuarios</h2>
  <table>
    <tr>
      <th>ID</th>
      <th>Nombre</th>
      <th>Número de Identificación</th>
      <th>Tipo de Documento</th>
      <th>Correo</th>
      <th>Clave</th> <!-- Añadir esta línea -->
      <th>Rol</th>
      <th>Fecha de Registro</th>
    </tr>
    <?php foreach ($usuarios as $usuario): ?>
      <tr>
        <td><?php echo htmlspecialchars($usuario['id']); ?></td>
        <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
        <td><?php echo htmlspecialchars($usuario['numero_identificacion']); ?></td>
        <td><?php echo htmlspecialchars($usuario['tipo_documento']); ?></td>
        <td><?php echo htmlspecialchars($usuario['correo']); ?></td>
        <td><?php echo htmlspecialchars($usuario['clave']); ?></td> <!-- Añadir esta línea -->
        <td><?php echo htmlspecialchars($usuario['rol']); ?></td>
        <td><?php echo htmlspecialchars($usuario['fecha_registro']); ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

</body>

</html>