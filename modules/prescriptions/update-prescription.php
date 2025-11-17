<?php
require_once __DIR__ . '/../../includes/session.php';
checkAuth();
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../prescriptions.php');
    exit();
}

$db = getDB();
$id = $_POST['id'] ?? null;

if (!$id) {
    header('Location: ../../prescriptions.php');
    exit();
}

try {
    $stmt = $db->prepare("
        UPDATE prescripciones SET
            numero_receta = ?,
            cliente_id = ?,
            medico_id = ?,
            fecha_emision = ?,
            diagnostico = ?,
            observaciones = ?,
            estado = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $_POST['numero_receta'],
        $_POST['cliente_id'],
        $_POST['medico_id'] ?: null,
        $_POST['fecha_emision'],
        $_POST['diagnostico'] ?: null,
        $_POST['observaciones'] ?: null,
        $_POST['estado'],
        $id
    ]);

    logAction('update_prescription', 'prescripciones', $id, 'Prescripción actualizada: ' . $_POST['numero_receta']);
    $_SESSION['success'] = 'Prescripción actualizada exitosamente';
    header('Location: ../../prescription-view.php?id=' . $id);
} catch (PDOException $e) {
    error_log("Update prescription error: " . $e->getMessage());
    $_SESSION['error'] = 'Error al actualizar la prescripción';
    header('Location: ../../prescription-edit.php?id=' . $id);
}
exit();
?>
