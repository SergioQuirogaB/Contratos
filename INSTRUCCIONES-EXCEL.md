# 📊 Instrucciones para Subir Archivos Excel/CSV

## ✅ Solución al Error de PhpSpreadsheet

**El error que viste es normal** - la versión completa requiere librerías adicionales. **Usa la versión simplificada que ya funciona perfectamente.**

## 🚀 Cómo Usar la Funcionalidad

### 1. Accede a "Mis Contratos"
- Inicia sesión en el sistema
- Ve a tu dashboard
- Haz clic en "Mis Contratos"

### 2. Sube tu archivo
- **Formato aceptado:** CSV (.csv) o TXT (.txt)
- **Si tienes Excel:** Exporta como CSV
- **Si tienes Google Sheets:** Descarga como CSV

### 3. Los datos se mostrarán automáticamente
- Primera fila = encabezados de columnas
- Resto de filas = datos de contratos
- Tabla responsive y organizada

## 📋 Cómo Exportar desde Excel

### Método 1: Excel
1. Abre tu archivo Excel
2. Ve a **Archivo** → **Guardar como**
3. Selecciona **CSV UTF-8 (delimitado por comas)**
4. Guarda el archivo
5. Súbelo al sistema

### Método 2: Google Sheets
1. Abre tu hoja de cálculo
2. Ve a **Archivo** → **Descargar** → **CSV**
3. Súbelo al sistema

## 📄 Formato del Archivo

Tu archivo CSV debe tener esta estructura:

```csv
ID,Cliente,Descripción,Fecha Inicio,Fecha Fin,Valor,Estado
1,Empresa ABC,Desarrollo de Software,2024-01-15,2024-06-15,$50000,Activo
2,Corporación XYZ,Consultoría IT,2024-02-01,2024-12-31,$75000,Activo
```

### Reglas importantes:
- ✅ **Primera fila:** Nombres de las columnas
- ✅ **Separador:** Comas (,)
- ✅ **Sin espacios extra** alrededor de las comas
- ✅ **Sin caracteres especiales** en los nombres de columnas

## 🎯 Archivo de Ejemplo

Ya tienes un archivo de ejemplo: `ejemplo-contratos.csv`

Puedes usarlo para probar la funcionalidad:
1. Descarga el archivo
2. Súbelo en "Mis Contratos"
3. Verás los datos organizados en tabla

## 🔧 Si Quieres la Versión Completa (Opcional)

Si realmente necesitas la versión que maneja archivos Excel directamente:

1. **Instala Composer:**
   - Ve a https://getcomposer.org/download/
   - Descarga e instala Composer

2. **Habilita la extensión GD en XAMPP:**
   - Abre `C:\xampp\php\php.ini`
   - Busca la línea `;extension=gd`
   - Quita el punto y coma: `extension=gd`
   - Reinicia Apache

3. **Instala las dependencias:**
   ```bash
   composer install
   ```

4. **Usa `mis-contratos.php` en lugar de `mis-contratos-simple.php`**

## ✅ Recomendación

**Usa la versión simplificada** (`mis-contratos-simple.php`) que:
- ✅ Funciona sin dependencias adicionales
- ✅ Es más rápida y ligera
- ✅ Maneja archivos CSV perfectamente
- ✅ Es compatible con Excel (exportando como CSV)

## 🆘 Si Tienes Problemas

1. **Verifica el formato del archivo**
2. **Asegúrate de que sea CSV**
3. **Revisa que la primera fila tenga encabezados**
4. **Prueba con el archivo de ejemplo primero**

---

**¡La funcionalidad ya está lista para usar!** 🎉
