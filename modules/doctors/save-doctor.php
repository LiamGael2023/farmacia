<?php
require_once __DIR__ . '/../../includes/session.php';
checkAuth();
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../doctors.php');
    exit();
}

$db = getDB();

try {
    $stmt = $db->prepare("
        INSERT INTO medicos (nombre, apellido, numero_colegiatura, especialidad,
                            telefono, email, estado)
        VALUES (?, ?, ?, ?, ?, ?, 'activo')
    ");

    $stmt->execute([
        $_POST['nombre'],
        $_POST['apellido'],
        $_POST['numero_colegiatura'] ?: null,
        $_POST['especialidad'] ?: null,
        $_POST['telefono'] ?: null,
        $_POST['email'] ?: null
    ]);

    $id = $db->lastInsertId();
    logAction('create_doctor', 'medicos', $id, 'Médico creado: ' . $_POST['nombre'] . ' ' . $_POST['apellido']);

    $_SESSION['success'] = 'Médico registrado exitosamente';
    header('Location: ../../doctors.php');
} catch (PDOException $e) {
    error_log("Save doctor error: " . $e->getMessage());
    $_SESSION['error'] = 'Error al registrar el médico';
    header('Location: ../../doctors.php');
}
exit();
?>
