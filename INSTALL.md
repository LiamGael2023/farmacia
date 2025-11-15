# Guía de Instalación - Sistema de Farmacia

Esta guía te ayudará a instalar y configurar el Sistema de Gestión Farmacéutica paso a paso.

## Requisitos Previos

Antes de comenzar, asegúrate de tener instalado:

- **Servidor Web**: Apache 2.4+ o Nginx 1.18+
- **PHP**: 7.4 o superior
- **MySQL**: 5.7+ o MariaDB 10.2+
- **Extensiones PHP necesarias**:
  - pdo
  - pdo_mysql
  - mbstring
  - json
  - session
  - curl (opcional, para futuras integraciones)

### Verificar Requisitos

```bash
# Verificar versión de PHP
php -v

# Verificar extensiones PHP
php -m | grep -E "pdo|mysql|mbstring|json"

# Verificar MySQL
mysql --version
```

## Instalación Paso a Paso

### 1. Descargar el Sistema

```bash
# Clonar desde repositorio
git clone https://github.com/usuario/farmacia.git

# O descargar ZIP y extraer
unzip farmacia.zip
cd farmacia
```

### 2. Configurar el Servidor Web

#### Opción A: Apache

1. Copiar el proyecto al directorio web:

```bash
sudo cp -r farmacia /var/www/html/
sudo chown -R www-data:www-data /var/www/html/farmacia
sudo chmod -R 755 /var/www/html/farmacia
```

2. Habilitar mod_rewrite:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

3. Configurar Virtual Host (opcional):

```bash
sudo nano /etc/apache2/sites-available/farmacia.conf
```

Agregar:

```apache
<VirtualHost *:80>
    ServerName farmacia.local
    DocumentRoot /var/www/html/farmacia

    <Directory /var/www/html/farmacia>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/farmacia_error.log
    CustomLog ${APACHE_LOG_DIR}/farmacia_access.log combined
</VirtualHost>
```

Habilitar el sitio:

```bash
sudo a2ensite farmacia.conf
sudo systemctl reload apache2
```

#### Opción B: Nginx

Configurar bloque de servidor:

```bash
sudo nano /etc/nginx/sites-available/farmacia
```

Agregar:

```nginx
server {
    listen 80;
    server_name farmacia.local;
    root /var/www/html/farmacia;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }

    location ^~ /config/ {
        deny all;
    }

    location ^~ /includes/ {
        deny all;
    }

    location ^~ /database/ {
        deny all;
    }
}
```

Habilitar:

```bash
sudo ln -s /etc/nginx/sites-available/farmacia /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 3. Configurar Base de Datos

#### 3.1 Crear Base de Datos

```bash
mysql -u root -p
```

En el prompt de MySQL:

```sql
CREATE DATABASE farmacia_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'farmacia_user'@'localhost' IDENTIFIED BY 'password_seguro_aqui';
GRANT ALL PRIVILEGES ON farmacia_db.* TO 'farmacia_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### 3.2 Importar Esquema

```bash
mysql -u farmacia_user -p farmacia_db < database/schema.sql
```

Verificar que se importaron las tablas:

```bash
mysql -u farmacia_user -p farmacia_db -e "SHOW TABLES;"
```

Deberías ver:

```
+------------------------+
| Tables_in_farmacia_db  |
+------------------------+
| categorias             |
| clientes               |
| compras                |
| compras_detalle        |
| configuracion          |
| logs                   |
| lotes                  |
| medicamentos           |
| medicos                |
| movimientos_inventario |
| prescripciones         |
| prescripciones_detalle |
| proveedores            |
| usuarios               |
| ventas                 |
| ventas_detalle         |
+------------------------+
```

### 4. Configurar Conexión a Base de Datos

Editar el archivo de configuración:

```bash
nano config/database.php
```

Modificar las credenciales:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'farmacia_user');
define('DB_PASS', 'password_seguro_aqui');
define('DB_NAME', 'farmacia_db');
```

### 5. Configurar Permisos

```bash
# Permisos generales
sudo chown -R www-data:www-data /var/www/html/farmacia
sudo find /var/www/html/farmacia -type d -exec chmod 755 {} \;
sudo find /var/www/html/farmacia -type f -exec chmod 644 {} \;

# Crear directorios adicionales si es necesario
mkdir -p /var/www/html/farmacia/uploads
mkdir -p /var/www/html/farmacia/logs
mkdir -p /var/www/html/farmacia/cache

# Permisos de escritura
sudo chmod 775 /var/www/html/farmacia/uploads
sudo chmod 775 /var/www/html/farmacia/logs
sudo chmod 775 /var/www/html/farmacia/cache
```

### 6. Configurar PHP

Editar configuración de PHP:

```bash
sudo nano /etc/php/7.4/apache2/php.ini
# o para Nginx
sudo nano /etc/php/7.4/fpm/php.ini
```

Ajustar valores recomendados:

```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
date.timezone = America/Lima
display_errors = Off  ; En producción
log_errors = On
error_log = /var/log/php_errors.log
session.save_path = "/var/www/html/farmacia/sessions"
```

Reiniciar PHP:

```bash
# Apache
sudo systemctl restart apache2

# Nginx
sudo systemctl restart php7.4-fpm
```

### 7. Acceder al Sistema

1. Abrir navegador web
2. Navegar a: `http://localhost/farmacia` o `http://farmacia.local`
3. Login con credenciales por defecto:
   - **Usuario**: admin
   - **Contraseña**: admin123

### 8. Configuración Post-Instalación

#### 8.1 Cambiar Contraseña de Administrador

1. Iniciar sesión como admin
2. Ir a **Perfil** (esquina superior derecha)
3. Cambiar contraseña

#### 8.2 Configurar Datos de la Farmacia

1. Ir a **Administración > Configuración**
2. Actualizar:
   - Nombre de la farmacia
   - RUC
   - Dirección
   - Teléfono y email
   - Porcentaje de impuesto (IGV)
   - Moneda

#### 8.3 Crear Usuarios Adicionales

1. Ir a **Administración > Usuarios**
2. Crear usuarios para vendedores, farmacéuticos, cajeros
3. Asignar roles apropiados

#### 8.4 Configurar Categorías

1. Ir a **Inventario > Categorías**
2. Las categorías por defecto ya están creadas
3. Agregar más según necesidad

### 9. Datos de Prueba (Opcional)

Para probar el sistema con datos de ejemplo:

```bash
mysql -u farmacia_user -p farmacia_db < database/sample_data.sql
```

## Solución de Problemas

### Error: "No se puede conectar a la base de datos"

- Verificar credenciales en `config/database.php`
- Verificar que MySQL esté corriendo: `sudo systemctl status mysql`
- Verificar permisos del usuario de BD

### Error 500 - Internal Server Error

- Revisar logs de Apache/Nginx:
  ```bash
  sudo tail -f /var/log/apache2/error.log
  # o
  sudo tail -f /var/log/nginx/error.log
  ```
- Verificar permisos de archivos
- Revisar configuración de PHP

### No se muestran estilos (CSS)

- Verificar que la URL base sea correcta
- Limpiar caché del navegador
- Verificar permisos de directorio `assets/`

### Sesión no se mantiene

- Verificar que el directorio `sessions/` exista y tenga permisos
- Verificar configuración de `session.save_path` en php.ini
- Verificar cookies en el navegador

## Actualización del Sistema

Para actualizar a una nueva versión:

```bash
# Backup de la base de datos
mysqldump -u farmacia_user -p farmacia_db > backup_$(date +%Y%m%d).sql

# Backup de archivos
cp -r /var/www/html/farmacia /var/www/html/farmacia_backup_$(date +%Y%m%d)

# Actualizar código
git pull origin main

# Ejecutar migraciones si las hay
mysql -u farmacia_user -p farmacia_db < database/migrations/update_vX.X.sql
```

## Seguridad en Producción

1. **Cambiar todas las contraseñas por defecto**
2. **Habilitar HTTPS** con Let's Encrypt o certificado SSL
3. **Configurar firewall**:
   ```bash
   sudo ufw allow 80/tcp
   sudo ufw allow 443/tcp
   sudo ufw enable
   ```
4. **Deshabilitar display_errors** en php.ini
5. **Configurar backups automáticos**
6. **Actualizar regularmente** el sistema operativo y paquetes
7. **Revisar logs periódicamente**

## Backups Automatizados

Crear script de backup:

```bash
sudo nano /usr/local/bin/backup_farmacia.sh
```

Contenido:

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/farmacia"
mkdir -p $BACKUP_DIR

# Backup BD
mysqldump -u farmacia_user -pPASSWORD farmacia_db > $BACKUP_DIR/db_$DATE.sql
gzip $BACKUP_DIR/db_$DATE.sql

# Backup archivos
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/html/farmacia

# Eliminar backups antiguos (más de 30 días)
find $BACKUP_DIR -name "*.sql.gz" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
```

Hacer ejecutable y programar en cron:

```bash
sudo chmod +x /usr/local/bin/backup_farmacia.sh
sudo crontab -e
```

Agregar:

```cron
0 2 * * * /usr/local/bin/backup_farmacia.sh
```

## Soporte

Para soporte adicional:
- Documentación: Ver README.md
- Issues: https://github.com/usuario/farmacia/issues
- Email: soporte@farmacia.com

---

¡Instalación completada! El sistema está listo para usar.
