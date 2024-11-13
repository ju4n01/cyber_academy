<?php
session_start();
if (!isset($_SESSION['correo']) || $_SESSION['rol'] != 'administrador') {
    header("Location: index.php");
    exit();
}

require '../src/db.php'; // Asegúrate de que este archivo contiene la conexión a la base de datos

$mensaje = "";

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
    <form action="logout.php" method="POST">
      <button type="submit">Cerrar Sesión</button>
    </form>

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