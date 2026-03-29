# Guía de Migración y Despliegue del Sistema SIGCOEP

Este documento contiene los pasos necesarios para migrar el sistema de gestión de correspondencia desde un entorno de desarrollo (como XAMPP) a un servidor de producción o a otra máquina.

---

## Requisitos Previos en el Servidor de Destino

Antes de comenzar, asegúrate de que el nuevo servidor tenga instalado lo siguiente:

- **Servidor Web:** Apache 2.4 o superior.
- **PHP:** Versión 7.4 o superior.
- **Base de Datos:** MySQL 5.7+ o MariaDB 10.4+.
- **Extensiones de PHP:**
  - `pdo_mysql`: Para la conexión a la base de datos.
  - `gd`: **(Muy importante)** Para la compresión y manipulación de imágenes.
  - `mbstring`: Para el manejo de caracteres multibyte.
- **Herramienta de gestión de BD:** Se recomienda `phpMyAdmin` para facilitar la importación/exportación.

---

## Paso 1: Respaldar la Base de Datos Actual

Lo primero es crear una copia de seguridad completa de tu base de datos `sigcoep` actual.

1.  Abre tu navegador y ve a `http://localhost/phpmyadmin`.
2.  En el menú de la izquierda, selecciona tu base de datos: **`sigcoep`**.
3.  Ve a la pestaña **"Exportar"** en la parte superior.
4.  Deja el método de exportación en **"Rápido"** y el formato en **"SQL"**.
5.  Haz clic en el botón **"Exportar"**. Se descargará un archivo llamado `sigcoep.sql`. Guarda este archivo en un lugar seguro.

## Paso 2: Transferir los Archivos del Proyecto

Ahora, necesitas copiar todos los archivos del sistema al nuevo servidor.

1.  Comprime la carpeta completa del proyecto: `C:\xampp\htdocs\sigcoep`.
2.  Transfiere el archivo `.zip` resultante al servidor de destino usando un cliente FTP (como FileZilla), SCP, o cualquier otro método.
3.  En el servidor de destino, descomprime el archivo dentro del directorio raíz del servidor web (comúnmente `/var/www/html/` en Linux o `C:\xampp\htdocs\` en Windows). Deberías tener una estructura como `/var/www/html/sigcoep/...`.

## Paso 3: Crear e Importar la Base de Datos en el Nuevo Servidor

Con los archivos ya en su sitio, es hora de restaurar la base de datos.

1.  En el nuevo servidor, abre `phpMyAdmin`.
2.  Crea una nueva base de datos. **Es crucial que se llame exactamente igual: `sigcoep`**.
    -   Al crearla, asegúrate de que el cotejamiento (collation) sea `utf8mb4_general_ci`.
3.  Una vez creada, entra en la base de datos `sigcoep` (que estará vacía).
4.  Ve a la pestaña **"Importar"**.
5.  Haz clic en **"Seleccionar archivo"** y elige el archivo `sigcoep.sql` que exportaste en el Paso 1.
6.  Deja las demás opciones por defecto y haz clic en el botón **"Importar"** al final de la página.

> **Nota:** Si el nuevo servidor no es para producción y no tiene un usuario/contraseña de base de datos, puedes saltar al siguiente paso. Si es un servidor de producción, asegúrate de crear un usuario de base de datos específico con permisos sobre `sigcoep` por seguridad.

## Paso 4: Configurar la Conexión a la Base de Datos

Este es el paso más importante. Debes indicarle al sistema cómo conectarse a la nueva base de datos.

1.  En el nuevo servidor, abre el archivo `sigcoep/db.php` con un editor de texto.
2.  Modifica las siguientes variables con los datos de tu **nuevo servidor**:

    ```php
    // db.php - Conexión a la base de datos PDO
    
    // Configuración de la base de datos
    $DB_HOST = 'localhost';       // Generalmente 'localhost', a menos que la BD esté en otro servidor.
    $DB_NAME = 'sigcoep';         // Debe ser el mismo nombre que creaste.
    $DB_USER = 'root';            // Cambia por el usuario de la BD del nuevo servidor.
    $DB_PASS = '';                // Cambia por la contraseña de ese usuario.
    ```

3.  Guarda los cambios en el archivo.

## Paso 5: Ajustar Permisos de Carpetas (¡Muy Importante en Linux!)

Si tu nuevo servidor usa un sistema operativo Linux (como Ubuntu, CentOS, etc.), el servidor web (Apache) necesita permisos para escribir archivos en las carpetas de subida. Si omites este paso, las funciones de subir fotos y desarchivar fallarán.

Abre una terminal en tu servidor y ejecuta los siguientes comandos, reemplazando `/var/www/html/sigcoep` con la ruta real de tu proyecto:

```bash
# Otorgar propiedad de todos los archivos al usuario del servidor web (comúnmente www-data)
sudo chown -R www-data:www-data /var/www/html/sigcoep

# Dar permisos de escritura a las carpetas de subida
sudo chmod -R 775 /var/www/html/sigcoep/assets/fotos_correspondencia
sudo chmod -R 775 /var/www/html/sigcoep/assets/solicitud_desarchivo
```

> Si estás migrando a otro XAMPP en Windows, este paso generalmente no es necesario.

## Paso 6: Pruebas Finales

¡Todo está listo para probar!

1.  Abre tu navegador y accede a la URL del sistema en el nuevo servidor (ej. `http://tu-dominio.com/sigcoep`).
2.  Intenta iniciar sesión con un usuario existente.
3.  **Prueba de Fuego:**
    -   Crea una **Nueva Correspondencia** y adjunta una imagen o un PDF.
    -   Busca un documento archivado y utiliza la función de **Desarchivar**, adjuntando el PDF/foto de respaldo.

Si ambas subidas de archivos funcionan y no ves ningún error, ¡la migración ha sido un éxito!

---

### Solución de Problemas Comunes

- **"Error de conexión a la base de datos"**: Revisa 100% los datos en `db.php` (Paso 4). Usuario, contraseña y host deben ser los correctos para el nuevo servidor.
- **"No se pudo guardar la foto/PDF en el servidor"**: Es casi seguro un problema de permisos. Vuelve a ejecutar los comandos del Paso 5.
- **Página en blanco o error 500**: Puede ser que falte una extensión de PHP (como `gd` o `pdo_mysql`). Revisa los logs de error de Apache para más detalles.
