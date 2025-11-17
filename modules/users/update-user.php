<?php
require_once __DIR__ . '/../../includes/session.php';
checkAuth();

if (!hasRole('admin')) {
    header('Location: ../../dashboard.php');
    exit();
}

require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../users.php');
    exit();
}

$db = getDB();
$id = $_POST['id'] ?? null;

if (!$id) {
    header('Location: ../../users.php');
    exit();
}

try {
    // Si se proporcionó una nueva contraseña, actualizarla también
    if (!empty($_POST['password'])) {
        $stmt = $db->prepare("
            UPDATE usuarios SET
                username = ?,
                password = ?,
                nombre = ?,
                apellido = ?,
                email = ?,
                telefono = ?,
                rol = ?,
                estado = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $_POST['username'],
            password_hash($_POST['password'], PASSWORD_BCRYPT),
            $_POST['nombre'],
            $_POST['apellido'],
            $_POST['email'],
            $_POST['telefono'] ?: null,
            $_POST['rol'],
            $_POST['estado'],
            $id
        ]);
    } else {
        // Actualizar sin cambiar la contraseña
        $stmt = $db->prepare("
            UPDATE usuarios SET
                username = ?,
                nombre = ?,
                apellido = ?,
                email = ?,
                telefono = ?,
                rol = ?,
                estado = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $_POST['username'],
            $_POST['nombre'],
            $_POST['apellido'],
            $_POST['email'],
            $_POST['telefono'] ?: null,
            $_POST['rol'],
            $_POST['estado'],
            $id
        ]);
    }

    logAction('update_user', 'usuarios', $id, 'Usuario actualizado: ' . $_POST['username']);
    $_SESSION['success'] = 'Usuario actualizado exitosamente';
    header('Location: ../../users.php');
} catch (PDOException $e) {
    error_log("Update user error: " . $e->getMessage());
    $_SESSION['error'] = 'Error al actualizar el usuario';
    header('Location: ../../user-edit.php?id=' . $id);
}
exit();
?>
