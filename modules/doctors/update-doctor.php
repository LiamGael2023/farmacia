<?php
require_once __DIR__ . '/../../includes/session.php';
checkAuth();
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../doctors.php');
    exit();
}

$db = getDB();
$id = $_POST['id'] ?? null;

if (!$id) {
    header('Location: ../../doctors.php');
    exit();
}

try {
    $stmt = $db->prepare("
        UPDATE medicos SET
            nombre = ?,
            apellido = ?,
            numero_colegiatura = ?,
            especialidad = ?,
            telefono = ?,
            email = ?,
            estado = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $_POST['nombre'],
        $_POST['apellido'],
        $_POST['numero_colegiatura'] ?: null,
        $_POST['especialidad'] ?: null,
        $_POST['telefono'] ?: null,
        $_POST['email'] ?: null,
        $_POST['estado'],
        $id
    ]);

    logAction('update_doctor', 'medicos', $id, 'Médico actualizado: ' . $_POST['nombre'] . ' ' . $_POST['apellido']);
    $_SESSION['success'] = 'Médico actualizado exitosamente';
    header('Location: ../../doctor-view.php?id=' . $id);
} catch (PDOException $e) {
    error_log("Update doctor error: " . $e->getMessage());
    $_SESSION['error'] = 'Error al actualizar el médico';
    header('Location: ../../doctor-edit.php?id=' . $id);
}
exit();
?>
