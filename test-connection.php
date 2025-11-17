<?php
/**
 * Script de Verificación de Conexión
 * Usar este archivo para verificar que la conexión a la base de datos funciona
 */

// Habilitar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Verificación de Conexión a Base de Datos</h2>";
echo "<hr>";

// Verificar archivo de configuración
if (!file_exists('config/database.php')) {
    echo "<p style='color: red;'><strong>ERROR:</strong> El archivo config/database.php no existe.</p>";
    echo "<p>Copia config/database.example.php a config/database.php y edita las credenciales.</p>";
    exit();
}

echo "<p style='color: green;'>✓ Archivo de configuración encontrado</p>";

// Cargar configuración
require_once 'config/database.php';

echo "<h3>Configuración actual:</h3>";
echo "<ul>";
echo "<li><strong>Host:</strong> " . DB_HOST . "</li>";
echo "<li><strong>Puerto:</strong> " . DB_PORT . "</li>";
echo "<li><strong>Usuario:</strong> " . DB_USER . "</li>";
echo "<li><strong>Base de Datos:</strong> " . DB_NAME . "</li>";
echo "<li><strong>Charset:</strong> " . DB_CHARSET . "</li>";
echo "</ul>";

echo "<h3>Intentando conectar...</h3>";

try {
    // Intentar conectar SIN especificar base de datos primero
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<p style='color: green;'>✓ Conexión exitosa al servidor MySQL en puerto " . DB_PORT . "</p>";

    // Verificar si la base de datos existe
    $stmt = $pdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    $result = $stmt->fetch();

    if ($result) {
        echo "<p style='color: green;'>✓ La base de datos '" . DB_NAME . "' existe</p>";

        // Conectar a la base de datos
        $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        echo "<p style='color: green;'>✓ Conexión exitosa a la base de datos '" . DB_NAME . "'</p>";

        // Verificar tablas
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo "<h3>Tablas encontradas (" . count($tables) . "):</h3>";

        if (count($tables) > 0) {
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>" . $table;

                // Contar registros en tabla usuarios
                if ($table === 'usuarios') {
                    $countStmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
                    $count = $countStmt->fetchColumn();
                    echo " <strong>(" . $count . " usuarios)</strong>";

                    if ($count === 0) {
                        echo " <span style='color: red;'>⚠ NO HAY USUARIOS - Debes importar el schema.sql</span>";
                    }
                }

                echo "</li>";
            }
            echo "</ul>";

            // Verificar usuario admin
            if (in_array('usuarios', $tables)) {
                $stmt = $pdo->query("SELECT * FROM usuarios WHERE username = 'admin'");
                $admin = $stmt->fetch();

                if ($admin) {
                    echo "<p style='color: green;'>✓ Usuario 'admin' existe</p>";
                    echo "<ul>";
                    echo "<li><strong>ID:</strong> " . $admin['id'] . "</li>";
                    echo "<li><strong>Nombre:</strong> " . $admin['nombre'] . " " . $admin['apellido'] . "</li>";
                    echo "<li><strong>Email:</strong> " . $admin['email'] . "</li>";
                    echo "<li><strong>Rol:</strong> " . $admin['rol'] . "</li>";
                    echo "<li><strong>Estado:</strong> " . $admin['estado'] . "</li>";
                    echo "</ul>";
                    echo "<p style='color: blue;'><strong>Credenciales de acceso:</strong></p>";
                    echo "<ul>";
                    echo "<li><strong>Usuario:</strong> admin</li>";
                    echo "<li><strong>Contraseña:</strong> admin123</li>";
                    echo "</ul>";
                } else {
                    echo "<p style='color: red;'>✗ Usuario 'admin' NO EXISTE - Debes importar database/schema.sql</p>";
                }
            }

        } else {
            echo "<p style='color: red;'>⚠ <strong>NO HAY TABLAS</strong> - Debes importar database/schema.sql</p>";
            echo "<h3>Para importar el schema:</h3>";
            echo "<pre>mysql -u " . DB_USER . " -p -P " . DB_PORT . " " . DB_NAME . " < database/schema.sql</pre>";
        }

        echo "<hr>";
        echo "<p style='color: green; font-size: 18px;'><strong>✓ TODO ESTÁ CORRECTO</strong></p>";
        echo "<p><a href='index.php' style='padding: 10px 20px; background: #206bc4; color: white; text-decoration: none; border-radius: 4px;'>Ir al Login</a></p>";

    } else {
        echo "<p style='color: red;'>✗ La base de datos '" . DB_NAME . "' NO EXISTE</p>";
        echo "<h3>Para crear la base de datos:</h3>";
        echo "<pre>mysql -u " . DB_USER . " -p -P " . DB_PORT . " -e \"CREATE DATABASE " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\"</pre>";
        echo "<h3>Luego importar el schema:</h3>";
        echo "<pre>mysql -u " . DB_USER . " -p -P " . DB_PORT . " " . DB_NAME . " < database/schema.sql</pre>";
    }

} catch (PDOException $e) {
    echo "<p style='color: red;'><strong>ERROR DE CONEXIÓN:</strong></p>";
    echo "<pre style='background: #ffebee; padding: 15px; border-left: 4px solid red;'>" . $e->getMessage() . "</pre>";

    echo "<h3>Posibles causas:</h3>";
    echo "<ul>";
    echo "<li>MySQL no está corriendo en el puerto " . DB_PORT . "</li>";
    echo "<li>Usuario o contraseña incorrectos</li>";
    echo "<li>El puerto configurado es incorrecto (verifica que sea 3307)</li>";
    echo "<li>El usuario no tiene permisos</li>";
    echo "</ul>";

    echo "<h3>Soluciones:</h3>";
    echo "<ol>";
    echo "<li>Verifica que MySQL esté corriendo: <code>mysqladmin -u " . DB_USER . " -p -P " . DB_PORT . " ping</code></li>";
    echo "<li>Verifica el puerto en config/database.php (debe ser 3307)</li>";
    echo "<li>Verifica usuario y contraseña en config/database.php</li>";
    echo "</ol>";
}
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
}
</style>
