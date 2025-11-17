<?php
/**
 * Script de Depuración de Login
 * Usar este archivo para verificar por qué el login no funciona
 */

// Habilitar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Depuración de Login</h2>";
echo "<hr>";

require_once 'config/database.php';

try {
    $db = getDB();
    echo "<p style='color: green;'>✓ Conexión a la base de datos exitosa</p>";

    // Buscar usuario admin
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE username = 'admin'");
    $stmt->execute();
    $user = $stmt->fetch();

    if ($user) {
        echo "<h3>Usuario 'admin' encontrado:</h3>";
        echo "<pre>";
        print_r([
            'id' => $user['id'],
            'username' => $user['username'],
            'nombre' => $user['nombre'],
            'apellido' => $user['apellido'],
            'email' => $user['email'],
            'rol' => $user['rol'],
            'estado' => $user['estado'],
            'password_hash' => substr($user['password'], 0, 50) . '...'
        ]);
        echo "</pre>";

        // Verificar estado
        if ($user['estado'] !== 'activo') {
            echo "<p style='color: red;'><strong>⚠ PROBLEMA:</strong> El usuario NO está activo. Estado actual: " . $user['estado'] . "</p>";
            echo "<p>Para activarlo:</p>";
            echo "<pre>UPDATE usuarios SET estado = 'activo' WHERE username = 'admin';</pre>";
        } else {
            echo "<p style='color: green;'>✓ Usuario está activo</p>";
        }

        // Probar la contraseña
        echo "<h3>Prueba de contraseña 'admin123':</h3>";

        $password_test = 'admin123';
        $password_hash = $user['password'];

        echo "<p>Contraseña a probar: <strong>$password_test</strong></p>";
        echo "<p>Hash en BD: <code>" . $password_hash . "</code></p>";

        if (password_verify($password_test, $password_hash)) {
            echo "<p style='color: green; font-size: 18px;'><strong>✓ LA CONTRASEÑA ES CORRECTA</strong></p>";
            echo "<p>El login debería funcionar. Si no funciona, puede ser un problema de sesiones o redirección.</p>";

            // Probar inicio de sesión
            echo "<hr><h3>Simulación de Login:</h3>";
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['apellido'] = $user['apellido'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['rol'] = $user['rol'];

            echo "<p style='color: green;'>✓ Sesión iniciada correctamente</p>";
            echo "<p>Variables de sesión:</p>";
            echo "<pre>";
            print_r($_SESSION);
            echo "</pre>";

            echo "<p><a href='dashboard.php' style='padding: 10px 20px; background: #206bc4; color: white; text-decoration: none; border-radius: 4px;'>Ir al Dashboard</a></p>";
            echo "<p><small>Si al hacer clic te lleva al dashboard, el problema es en el formulario de login.</small></p>";

        } else {
            echo "<p style='color: red; font-size: 18px;'><strong>✗ LA CONTRASEÑA NO COINCIDE</strong></p>";
            echo "<p>El hash en la base de datos no corresponde a 'admin123'.</p>";

            echo "<h3>Solución: Regenerar el hash correcto</h3>";
            $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
            echo "<p>Ejecuta esta query en tu base de datos:</p>";
            echo "<pre style='background: #f1f3f5; padding: 15px; border-left: 4px solid #206bc4;'>";
            echo "UPDATE usuarios SET password = '$new_hash' WHERE username = 'admin';\n";
            echo "</pre>";

            echo "<p>O haz clic en el botón para actualizar automáticamente:</p>";
            echo "<form method='post' style='margin: 20px 0;'>";
            echo "<input type='hidden' name='fix_password' value='1'>";
            echo "<button type='submit' style='padding: 10px 20px; background: #d63939; color: white; border: none; border-radius: 4px; cursor: pointer;'>Corregir Contraseña Ahora</button>";
            echo "</form>";
        }

    } else {
        echo "<p style='color: red;'><strong>✗ Usuario 'admin' NO ENCONTRADO</strong></p>";
        echo "<p>Necesitas importar el schema.sql o crear el usuario manualmente.</p>";

        echo "<h3>Crear usuario admin:</h3>";
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        echo "<pre style='background: #f1f3f5; padding: 15px;'>";
        echo "INSERT INTO usuarios (username, password, nombre, apellido, email, rol, estado) \n";
        echo "VALUES ('admin', '$hash', 'Administrador', 'Sistema', 'admin@farmacia.com', 'admin', 'activo');\n";
        echo "</pre>";

        echo "<p>O haz clic para crear automáticamente:</p>";
        echo "<form method='post' style='margin: 20px 0;'>";
        echo "<input type='hidden' name='create_admin' value='1'>";
        echo "<button type='submit' style='padding: 10px 20px; background: #206bc4; color: white; border: none; border-radius: 4px; cursor: pointer;'>Crear Usuario Admin</button>";
        echo "</form>";
    }

    // Procesar acciones
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['fix_password'])) {
            $new_hash = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE usuarios SET password = ? WHERE username = 'admin'");
            $stmt->execute([$new_hash]);
            echo "<p style='color: green; background: #d4edda; padding: 15px; border-radius: 4px;'><strong>✓ Contraseña actualizada!</strong> Recarga esta página para verificar.</p>";
        }

        if (isset($_POST['create_admin'])) {
            $hash = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $db->prepare("
                INSERT INTO usuarios (username, password, nombre, apellido, email, rol, estado)
                VALUES ('admin', ?, 'Administrador', 'Sistema', 'admin@farmacia.com', 'admin', 'activo')
            ");
            $stmt->execute([$hash]);
            echo "<p style='color: green; background: #d4edda; padding: 15px; border-radius: 4px;'><strong>✓ Usuario admin creado!</strong> Recarga esta página para verificar.</p>";
        }
    }

} catch (PDOException $e) {
    echo "<p style='color: red;'><strong>ERROR:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>Información del Sistema:</h3>";
echo "<ul>";
echo "<li>PHP Version: " . phpversion() . "</li>";
echo "<li>Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? 'Activa' : 'Inactiva') . "</li>";
echo "<li>Session ID: " . (session_status() === PHP_SESSION_ACTIVE ? session_id() : 'N/A') . "</li>";
echo "</ul>";

echo "<hr>";
echo "<p><a href='index.php'>← Volver al Login</a> | <a href='test-connection.php'>Test de Conexión</a></p>";
?>

<style>
body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    max-width: 900px;
    margin: 40px auto;
    padding: 20px;
    background: #f8f9fa;
}
h2 { color: #206bc4; }
h3 { color: #333; margin-top: 20px; }
pre {
    background: #f1f3f5;
    padding: 15px;
    border-radius: 4px;
    overflow-x: auto;
}
code {
    background: #e9ecef;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: monospace;
}
</style>
