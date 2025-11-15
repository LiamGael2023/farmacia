# Sistema de Gestión Farmacéutica

Sistema completo de gestión para farmacias desarrollado con PHP y diseño **Tabler.io**. Incluye todas las funcionalidades necesarias para operar una farmacia real.

![PHP](https://img.shields.io/badge/PHP-7.4+-blue.svg)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)
![Tabler](https://img.shields.io/badge/Tabler-1.0-lightblue.svg)
![License](https://img.shields.io/badge/License-MIT-green.svg)

## 🚀 Características Principales

### 📊 Dashboard Interactivo
- Vista general de ventas diarias y mensuales
- Alertas de stock bajo y medicamentos próximos a vencer
- Estadísticas en tiempo real
- Productos más vendidos
- Últimas transacciones

### 💊 Gestión de Inventario
- **Medicamentos**: Registro completo con código, nombre genérico, presentación, laboratorio, etc.
- **Categorías**: Organización por tipo de medicamento
- **Lotes**: Control de lotes con fechas de vencimiento y cantidad
- **Movimientos**: Registro detallado de entradas y salidas
- Control de stock mínimo con alertas automáticas
- Gestión de ubicaciones en almacén

### 💰 Punto de Venta (POS)
- Interfaz intuitiva y rápida
- Búsqueda de productos en tiempo real
- Cálculo automático de totales e impuestos
- Múltiples métodos de pago (efectivo, tarjeta, transferencia)
- Gestión de descuentos
- Impresión de tickets de venta
- Registro de cliente opcional

### 👥 Gestión de Personas
- **Clientes/Pacientes**: Datos personales, alergias, historial
- **Proveedores**: Información de contacto y compras
- **Médicos**: Registro para prescripciones
- **Usuarios**: Diferentes roles (admin, vendedor, farmacéutico, cajero)

### 📋 Prescripciones Médicas
- Registro de recetas médicas
- Vinculación con médicos y pacientes
- Control de medicamentos que requieren receta
- Estados: pendiente, dispensada, parcial, cancelada

### 📦 Gestión de Compras
- Registro de compras a proveedores
- Control de recepción de mercancía
- Actualización automática de inventario
- Gestión de estados

### 📈 Reportes y Estadísticas
- Reportes de ventas por período
- Análisis de inventario
- Productos más vendidos
- Control de vencimientos
- Movimientos de inventario

### 🔐 Seguridad
- Sistema de autenticación robusto
- Control de acceso por roles
- Registro de auditoría (logs)
- Protección contra SQL Injection
- Sesiones seguras

## 🛠️ Tecnologías Utilizadas

- **Backend**: PHP 7.4+
- **Base de Datos**: MySQL 5.7+ / MariaDB
- **Frontend**:
  - [Tabler.io](https://tabler.io) - Framework de UI
  - Bootstrap 5
  - jQuery
  - DataTables
  - SweetAlert2
- **Iconos**: Tabler Icons

## 📋 Requisitos del Sistema

- PHP >= 7.4
- MySQL >= 5.7 o MariaDB >= 10.2
- Apache/Nginx con mod_rewrite
- Extensiones PHP requeridas:
  - PDO
  - PDO_MySQL
  - mbstring
  - json

## ⚙️ Instalación

### 1. Clonar el Repositorio

```bash
git clone https://github.com/usuario/farmacia.git
cd farmacia
```

### 2. Configurar la Base de Datos

1. Crear una base de datos MySQL:

```sql
CREATE DATABASE farmacia_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Importar el esquema:

```bash
mysql -u usuario -p farmacia_db < database/schema.sql
```

### 3. Configurar la Conexión

Editar el archivo `config/database.php` con tus credenciales:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');
define('DB_NAME', 'farmacia_db');
```

### 4. Configurar el Servidor Web

#### Apache

Asegúrate de tener habilitado `mod_rewrite` y crear un `.htaccess` si es necesario.

#### Nginx

Configuración básica:

```nginx
server {
    listen 80;
    server_name farmacia.local;
    root /ruta/a/farmacia;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
```

### 5. Acceder al Sistema

Visita `http://localhost/farmacia` en tu navegador.

**Credenciales por defecto:**
- Usuario: `admin`
- Contraseña: `admin123`

⚠️ **IMPORTANTE**: Cambia la contraseña después del primer login.

## 📖 Estructura del Proyecto

```
farmacia/
├── assets/               # Recursos estáticos
│   ├── css/             # Estilos personalizados
│   ├── js/              # Scripts personalizados
│   └── img/             # Imágenes
├── config/              # Archivos de configuración
│   └── database.php     # Configuración de BD
├── database/            # Scripts de base de datos
│   └── schema.sql       # Esquema completo
├── includes/            # Archivos incluidos
│   ├── header.php       # Cabecera común
│   ├── footer.php       # Pie común
│   ├── navbar.php       # Menú de navegación
│   └── session.php      # Gestión de sesión
├── modules/             # Módulos funcionales
│   ├── auth/            # Autenticación
│   ├── inventory/       # Inventario
│   ├── sales/           # Ventas
│   ├── customers/       # Clientes
│   ├── suppliers/       # Proveedores
│   ├── prescriptions/   # Prescripciones
│   └── reports/         # Reportes
├── index.php            # Página de login
├── dashboard.php        # Dashboard principal
├── pos.php              # Punto de venta
└── README.md            # Este archivo
```

## 👤 Roles de Usuario

### Administrador
- Acceso completo al sistema
- Gestión de usuarios
- Configuración del sistema
- Acceso a todos los reportes

### Farmacéutico
- Gestión de medicamentos
- Dispensación de recetas
- Control de inventario
- Ventas

### Vendedor
- Punto de venta
- Gestión de clientes
- Consulta de inventario

### Cajero
- Solo punto de venta
- Consulta limitada

## 🔧 Configuración Adicional

### Configuración del Sistema

Accede a **Administración > Configuración** para modificar:
- Nombre de la farmacia
- RUC
- Dirección y contacto
- Porcentaje de impuestos
- Días de alerta para vencimientos
- Moneda

### Gestión de Usuarios

1. Ir a **Administración > Usuarios**
2. Crear nuevo usuario
3. Asignar rol apropiado
4. Configurar permisos

## 📊 Base de Datos

El sistema utiliza las siguientes tablas principales:

- `usuarios` - Usuarios del sistema
- `medicamentos` - Catálogo de medicamentos
- `categorias` - Categorías de medicamentos
- `lotes` - Control de lotes y vencimientos
- `clientes` - Base de datos de clientes
- `proveedores` - Proveedores
- `medicos` - Médicos autorizados
- `ventas` - Registro de ventas
- `ventas_detalle` - Detalle de ventas
- `compras` - Registro de compras
- `compras_detalle` - Detalle de compras
- `prescripciones` - Recetas médicas
- `prescripciones_detalle` - Detalle de recetas
- `movimientos_inventario` - Movimientos de stock
- `logs` - Auditoría del sistema
- `configuracion` - Configuración del sistema

## 🔒 Seguridad

El sistema implementa las siguientes medidas de seguridad:

- Contraseñas hasheadas con bcrypt
- Prepared statements para prevenir SQL Injection
- Validación de entrada en servidor
- Control de sesiones
- Registro de auditoría
- Protección CSRF
- Sanitización de salida

## 📝 Uso del Sistema

### Realizar una Venta

1. Ir a **Ventas > Punto de Venta**
2. Buscar y agregar productos
3. Seleccionar cliente (opcional)
4. Elegir método de pago
5. Procesar venta
6. Imprimir ticket

### Agregar Medicamento

1. Ir a **Inventario > Medicamentos**
2. Clic en "Nuevo Medicamento"
3. Completar información:
   - Código único
   - Nombre comercial y genérico
   - Categoría
   - Presentación y concentración
   - Precios
   - Stock mínimo
4. Guardar

### Control de Vencimientos

El dashboard muestra alertas automáticas para:
- Medicamentos próximos a vencer (30 días)
- Lotes vencidos
- Stock bajo

## 🤝 Contribución

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/NuevaCaracteristica`)
3. Commit tus cambios (`git commit -m 'Agregar nueva característica'`)
4. Push a la rama (`git push origin feature/NuevaCaracteristica`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 👨‍💻 Autor

Desarrollado con ❤️ para farmacias

## 📧 Soporte

Para soporte, por favor abrir un issue en el repositorio.

## 🎯 Roadmap

- [ ] API REST para integración con otros sistemas
- [ ] Aplicación móvil
- [ ] Módulo de facturación electrónica
- [ ] Integración con SUNAT
- [ ] Dashboard de BI avanzado
- [ ] Sistema de fidelización de clientes
- [ ] Gestión de recetas electrónicas
- [ ] Módulo de telemedicina

## 📸 Capturas de Pantalla

### Dashboard
Vista general con estadísticas en tiempo real y alertas.

### Punto de Venta
Interfaz rápida e intuitiva para procesamiento de ventas.

### Gestión de Inventario
Control completo de medicamentos, lotes y movimientos.

---

**Versión:** 1.0.0
**Última actualización:** 2025
**Estado:** Producción Ready
