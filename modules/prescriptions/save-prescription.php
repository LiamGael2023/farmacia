<?php
require_once __DIR__ . '/../../includes/session.php';
checkAuth();
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../prescriptions.php');
    exit();
}

$db = getDB();

try {
    $db->beginTransaction();

    $stmt = $db->prepare("
        INSERT INTO prescripciones (numero_receta, cliente_id, medico_id, fecha_emision,
                                   diagnostico, observaciones, estado)
        VALUES (?, ?, ?, ?, ?, ?, 'pendiente')
    ");

    $stmt->execute([
        $_POST['numero_receta'],
        $_POST['cliente_id'],
        $_POST['medico_id'] ?: null,
        $_POST['fecha_emision'],
        $_POST['diagnostico'] ?: null,
        $_POST['observaciones'] ?: null
    ]);

    $id = $db->lastInsertId();

    $db->commit();

    logAction('create_prescription', 'prescripciones', $id, 'Prescripción creada: ' . $_POST['numero_receta']);

    $_SESSION['success'] = 'Prescripción registrada exitosamente';
    header('Location: ../../prescription-view.php?id=' . $id);
} catch (PDOException $e) {
    $db->rollBack();
    error_log("Save prescription error: " . $e->getMessage());
    $_SESSION['error'] = 'Error al registrar la prescripción';
    header('Location: ../../prescriptions.php');
}
exit();
?>
