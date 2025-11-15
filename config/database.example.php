<?php
/**
 * Configuración de Base de Datos - EJEMPLO
 * Sistema de Farmacia
 *
 * INSTRUCCIONES:
 * 1. Copiar este archivo como database.php
 * 2. Actualizar las credenciales con tus datos
 * 3. El archivo database.php está en .gitignore por seguridad
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');  // Cambiar por tu usuario de MySQL
define('DB_PASS', '');      // Cambiar por tu contraseña de MySQL
define('DB_NAME', 'farmacia_db');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $charset = DB_CHARSET;
    private $conn;
    private $error;

    public function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->conn = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            error_log("Database Connection Error: " . $this->error);
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function getError() {
        return $this->error;
    }
}

// Función helper para obtener conexión
function getDB() {
    static $db = null;
    if ($db === null) {
        $database = new Database();
        $db = $database->getConnection();
    }
    return $db;
}
?>
