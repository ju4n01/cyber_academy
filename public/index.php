<?php
session_start();
require '../src/functions.php';

// Inicializar variable de error
$error = "";

// Procesar el inicio de sesión
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo']; 
    $password = $_POST['password'];

    // Depuración
    error_log("Email: $correo"); // Para ver el correo en el log
    error_log("Password: $password"); // Para ver la contraseña en el log

    try {
        if (verifyUser($correo, $password)) {
            // Obtener rol y nombre del usuario
            $rol = getUserRole($correo);
            $stmt = $conection->prepare("SELECT nombre FROM usuarios WHERE correo = ?");
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $conection->error);
            }

            $stmt->bind_param("s", $correo);
            if (!$stmt->execute()) {
                throw new Exception("Error en la ejecución de la consulta: " . $stmt->error);
            }

            $stmt->bind_result($nombre);
            if (!$stmt->fetch()) {
                throw new Exception("No se encontró el nombre del usuario.");
            }
            $stmt->close();

            $_SESSION['correo'] = $correo;
            $_SESSION['nombre'] = $nombre; // Agregar nombre a la sesión
            $_SESSION['rol'] = $rol;

            // Redirigir a la vista correspondiente
            if ($rol == 'estudiante') {
                header("Location: student.php");
            } elseif ($rol == 'profesor') {
                header("Location: teacher.php");
            } elseif ($rol == 'administrador') {
                header("Location: admin.php");
            }
            exit();
        } else {
            $error = "Usuario o contraseña incorrectos.";
            error_log($error); // Para ver el error en el log
        }
    } catch (Exception $e) {
        // Manejo de errores
        $error = "Se produjo un error: " . $e->getMessage();
        error_log($error); // Para registrar el error en el log
    }
}

if (!empty($error)) {
    echo "<script>console.error('".$error."');</script>";
}
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Ciber Academy - Inicio</title>
</head>
<body>
    <h1>Bienvenido a Cyber Academy</h1>
    <?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
    <form action="index.php" method="POST">
        <input type="text" name="correo" placeholder="Correo" required> <!-- Cambia a 'correo' -->
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Iniciar Sesión</button>
    </form>
</body>
</html>
