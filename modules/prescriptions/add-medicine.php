<?php
require_once __DIR__ . '/../../includes/session.php';
checkAuth();
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../prescriptions.php');
    exit();
}

$db = getDB();
$prescripcion_id = $_POST['prescripcion_id'] ?? null;

if (!$prescripcion_id) {
    header('Location: ../../prescriptions.php');
    exit();
}

try {
    $stmt = $db->prepare("
        INSERT INTO prescripciones_detalle (prescripcion_id, medicamento_id, cantidad,
                                           dosis, frecuencia, duracion, indicaciones)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $prescripcion_id,
        $_POST['medicamento_id'],
        $_POST['cantidad'],
        $_POST['dosis'] ?: null,
        $_POST['frecuencia'] ?: null,
        $_POST['duracion'] ?: null,
        $_POST['indicaciones'] ?: null
    ]);

    logAction('add_medicine_to_prescription', 'prescripciones_detalle', $prescripcion_id, 'Medicamento agregado a prescripción');
    $_SESSION['success'] = 'Medicamento agregado a la prescripción';
    header('Location: ../../prescription-view.php?id=' . $prescripcion_id);
} catch (PDOException $e) {
    error_log("Add medicine error: " . $e->getMessage());
    $_SESSION['error'] = 'Error al agregar el medicamento';
    header('Location: ../../prescription-view.php?id=' . $prescripcion_id);
}
exit();
?>
