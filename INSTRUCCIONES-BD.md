# 📋 Instrucciones para Guardar Contratos en Base de Datos

## 🚀 Configuración Inicial

### 1. Configurar la Base de Datos
Antes de usar la funcionalidad, necesitas configurar la base de datos:

```bash
php setup_database.php
```

Este script creará:
- Base de datos `kontratos`
- Tabla `usuarios`
- Tabla `contratos`
- Usuario administrador por defecto

### 2. Verificar Configuración
Asegúrate de que el archivo `config/database.php` tenga la configuración correcta:
- Host: localhost
- Puerto: 3307 (o el puerto de tu MySQL)
- Base de datos: kontratos
- Usuario: root
- Contraseña: (la de tu MySQL)

## 📊 Cómo Usar la Funcionalidad

### Paso 1: Subir Archivo CSV
1. Ve a la página "Mis Contratos"
2. En la sección "📊 Subir Archivo de Contratos"
3. Selecciona tu archivo CSV con los datos de contratos
4. Haz clic en "📤 Subir y Procesar"

### Paso 2: Revisar Datos
1. Los datos se mostrarán en una tabla
2. Revisa que la información sea correcta
3. Verifica que las columnas coincidan con el formato esperado

### Paso 3: Guardar en Base de Datos
1. Haz clic en el botón "💾 Guardar en Base de Datos"
2. Confirma la acción en el diálogo
3. Los datos se guardarán permanentemente

### Paso 4: Ver Contratos Guardados
1. Después de guardar, verás una nueva sección
2. "💾 Contratos Guardados en Base de Datos"
3. Aquí se muestran todos los contratos guardados

## 📋 Formato del Archivo CSV

El archivo CSV debe tener las siguientes columnas en este orden:

| Columna | Descripción | Tipo |
|---------|-------------|------|
| AÑO | Año del contrato | Número |
| Empresa | Nombre de la empresa | Texto |
| CLIENTE | Nombre del cliente | Texto |
| No contrato | Número de contrato | Texto |
| valor en pesos sin IVA | Valor en pesos mexicanos | Número |
| valor en dolares | Valor en dólares | Número |
| Descripcion | Descripción del contrato | Texto |
| Categoria | Categoría del contrato | Texto |
| Valor Mensual | Valor mensual | Número |
| observaciones | Observaciones adicionales | Texto |
| FECHA DE INICIO | Fecha de inicio (dd/mm/yyyy) | Fecha |
| FECHA DE VENCIMIENTO | Fecha de vencimiento (dd/mm/yyyy) | Fecha |
| valor Facturado | Valor facturado | Número |
| % ejecucion según Facturacion | Porcentaje de ejecución | Número |
| Valor Pendiente por ejecutar | Valor pendiente | Número |
| ESTADO | Estado del contrato | Texto |
| No de horas | Número de horas | Número |
| factura No | Número de factura | Texto |
| No DE POLIZA | Número de póliza | Texto |
| FECHA DE VENCIMIENTO POLIZA | Fecha de vencimiento de póliza (dd/mm/yyyy) | Fecha |

## 🔧 Características Técnicas

### Validación de Datos
- Los valores monetarios se limpian automáticamente (se eliminan $, comas, espacios)
- Las fechas se convierten automáticamente al formato de la base de datos
- Los porcentajes se limpian (se elimina el símbolo %)
- Todos los textos se escapan para prevenir inyección SQL

### Seguridad
- Los datos se asocian al usuario logueado
- Uso de consultas preparadas para prevenir inyección SQL
- Validación de tipos de datos
- Transacciones para garantizar integridad

### Rendimiento
- Paginación para manejar grandes volúmenes de datos
- Índices en la base de datos para consultas rápidas
- Límites configurables de registros por página

## 🐛 Solución de Problemas

### Error de Conexión a Base de Datos
- Verifica que MySQL esté ejecutándose
- Confirma el puerto en `config/database.php`
- Verifica las credenciales de acceso

### Error al Guardar Datos
- Revisa el formato del archivo CSV
- Verifica que las fechas estén en formato dd/mm/yyyy
- Asegúrate de que los valores numéricos no contengan caracteres especiales

### Datos No Se Muestran
- Verifica que hayas guardado los datos en la base de datos
- Confirma que estés logueado con el usuario correcto
- Revisa los logs de error del servidor

## 📞 Soporte

Si tienes problemas, verifica:
1. La configuración de la base de datos
2. El formato del archivo CSV
3. Los permisos del usuario de la base de datos
4. Los logs de error del servidor web
