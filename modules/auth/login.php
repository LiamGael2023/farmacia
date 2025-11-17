<?php
/**
 * Módulo de Autenticación - Login
 * Sistema de Farmacia
 */

// Habilitar errores para debugging (comentar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ../../dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../config/database.php';

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Por favor ingrese usuario y contraseña';
        header('Location: ../../index.php');
        exit();
    }

    try {
        $db = getDB();

        if (!$db) {
            die("Error: No se pudo conectar a la base de datos. Verifica la configuración en config/database.php");
        }

        $stmt = $db->prepare("
            SELECT id, username, password, nombre, apellido, email, rol, estado
            FROM usuarios
            WHERE username = ? AND estado = 'activo'
        ");

        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login exitoso
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['apellido'] = $user['apellido'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['rol'] = $user['rol'];

            // Actualizar último acceso
            $updateStmt = $db->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);

            // Registrar log
            $logStmt = $db->prepare("
                INSERT INTO logs (usuario_id, accion, detalles, ip_address)
                VALUES (?, 'login', 'Inicio de sesión exitoso', ?)
            ");
            $logStmt->execute([$user['id'], $_SERVER['REMOTE_ADDR']]);

            // Si seleccionó "Recordarme"
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 días
            }

            header('Location: ../../dashboard.php');
            exit();
        } else {
            // Login fallido
            $_SESSION['error'] = 'Usuario o contraseña incorrectos';

            // Registrar intento fallido
            if ($user) {
                $logStmt = $db->prepare("
                    INSERT INTO logs (usuario_id, accion, detalles, ip_address)
                    VALUES (?, 'login_failed', 'Intento de login fallido', ?)
                ");
                $logStmt->execute([$user['id'], $_SERVER['REMOTE_ADDR']]);
            }

            header('Location: ../../index.php');
            exit();
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['error'] = 'Error al procesar la solicitud: ' . $e->getMessage();
        // En desarrollo, mostrar el error
        die("Error de base de datos: " . $e->getMessage());
        // En producción, descomentar las siguientes líneas y comentar el die()
        // header('Location: ../../index.php');
        // exit();
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        die("Error general: " . $e->getMessage());
    }
} else {
    header('Location: ../../index.php');
    exit();
}
?>
