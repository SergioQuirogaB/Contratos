# Sistema de Contratos

Un sistema completo de gestión de contratos con login, registro y panel administrativo.

## Características

- ✅ Sistema de login y registro de usuarios
- ✅ Panel administrativo con estadísticas
- ✅ Dashboard para usuarios normales
- ✅ Conexión a MySQL (XAMPP puerto 3307)
- ✅ Diseño moderno y responsive
- ✅ Seguridad con hash de contraseñas
- ✅ Validación de formularios
- ✅ Subida y visualización de archivos CSV/Excel
- ✅ Listado de datos de contratos en tabla

## Requisitos

- XAMPP con MySQL en puerto 3307
- PHP 7.4 o superior
- Navegador web moderno

## Instalación

1. **Configurar XAMPP:**
   - Asegúrate de que XAMPP esté corriendo en el puerto 3307
   - Inicia Apache y MySQL

2. **Crear la base de datos:**
   - Abre phpMyAdmin (http://localhost:3307/phpmyadmin)
   - Ejecuta el script SQL ubicado en `database/schema.sql`
   - O importa el archivo directamente

3. **Configurar el proyecto:**
   - Coloca todos los archivos en tu directorio web de XAMPP
   - Verifica que la configuración en `config/database.php` sea correcta

4. **Acceder al sistema:**
   - Abre http://localhost/tu-proyecto/
   - Para probar la conexión: http://localhost/tu-proyecto/test_connection.php
   - Usuario admin por defecto:
     - Email: admin@contratos.com
     - Contraseña: password

## Estructura del Proyecto

```
Contratos/
├── admin/
│   ├── home.php          # Dashboard administrativo
│   ├── mis-contratos.php # Gestión de contratos con CSV
│   ├── nuevo-contrato.php # Crear nuevo contrato
│   ├── perfil.php        # Editar perfil de usuario
│   └── estadisticas.php  # Estadísticas del usuario
├── assets/
│   └── css/
│       └── style.css     # Estilos del sistema
├── config/
│   └── database.php      # Configuración de base de datos
├── database/
│   └── schema.sql        # Script de creación de BD
├── includes/
│   └── functions.php     # Funciones auxiliares
├── index.php             # Página de login
├── register.php          # Página de registro
├── dashboard.php         # Dashboard de usuarios
├── ejemplo-contratos.csv # Archivo de ejemplo
├── logout.php           # Cerrar sesión
└── README.md            # Este archivo
```

## Uso

### Para Administradores:
1. Inicia sesión con credenciales de admin
2. Accede al panel administrativo
3. Gestiona usuarios y contratos
4. Visualiza estadísticas del sistema

### Para Usuarios:
1. Regístrate en el sistema
2. Inicia sesión con tus credenciales
3. Accede a tu dashboard personal
4. Gestiona tus contratos
5. Sube archivos CSV con datos de contratos
6. Visualiza los datos en formato de tabla

## Seguridad

- Contraseñas hasheadas con `password_hash()`
- Validación de entrada de datos
- Protección contra SQL injection
- Control de sesiones
- Verificación de roles de usuario

## Personalización

Puedes personalizar:
- Colores y estilos en `assets/css/style.css`
- Configuración de base de datos en `config/database.php`
- Funciones auxiliares en `includes/functions.php`

## Gestión de Contratos

### Subir Archivos CSV:
1. Ve a "Mis Contratos" desde tu dashboard
2. Sube un archivo CSV con los datos de contratos
3. La primera fila debe contener los nombres de las columnas
4. Los datos se mostrarán en una tabla organizada

### Formato del CSV:
- Usa comas como separadores
- La primera fila son los encabezados
- Puedes exportar desde Excel como CSV
- Incluye un archivo de ejemplo: `ejemplo-contratos.csv`

## Soporte

Si tienes problemas:
1. Verifica que XAMPP esté corriendo en el puerto 3307
2. Confirma que la base de datos se creó correctamente
3. Revisa los logs de error de PHP
4. Verifica la configuración de la base de datos
5. Para archivos Excel, usa el formato CSV exportado desde Excel