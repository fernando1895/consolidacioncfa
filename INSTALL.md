# Guía de Instalación – Sistema de Consolidación & Ministración

Sistema de registro de asistencia para el **Departamento de Consolidación y Célula – CFA**.

---

## Requisitos previos

| Componente | Versión mínima |
|-----------|---------------|
| PHP       | 8.0 o superior (con extensiones `pdo`, `pdo_mysql`) |
| MySQL / MariaDB | 5.7 / 10.3 o superior |
| Servidor web | Apache 2.4+ o Nginx 1.18+ |

---

## 🪟 Instalación en Windows

### Opción A – Con XAMPP (recomendado para desarrollo)

1. **Descargar e instalar XAMPP**
   - Ir a <https://www.apachefriends.org> y descargar XAMPP para Windows.
   - Ejecutar el instalador y seleccionar al menos los módulos **Apache**, **MySQL** y **PHP**.

2. **Copiar los archivos del proyecto**
   ```
   C:\xampp\htdocs\consolidacion\
   ```
   Copiar todo el contenido de este repositorio dentro de esa carpeta.

3. **Iniciar los servicios**
   - Abrir **XAMPP Control Panel**.
   - Hacer clic en **Start** para **Apache** y **MySQL**.

4. **Crear la base de datos**
   - Abrir el navegador en `http://localhost/phpmyadmin`.
   - Ir a la pestaña **SQL** o usar el botón **Importar**.
   - Importar el archivo `database.sql` que se encuentra en la raíz del proyecto.

5. **Configurar la conexión**
   - Abrir `config/database.php` con cualquier editor de texto.
   - Verificar o ajustar los valores:
     ```php
     define('DB_HOST',     'localhost');
     define('DB_USER',     'root');
     define('DB_PASSWORD', '');          // En XAMPP suele estar vacío
     define('DB_NAME',     'consolidacion_cfa');
     ```

6. **Acceder a la aplicación**
   - Abrir el navegador en: `http://localhost/consolidacion/`

---

## 🐧 Instalación en Linux (Ubuntu / Debian)

### 1. Instalar dependencias

```bash
sudo apt update
sudo apt install -y apache2 mysql-server php php-mysql php-pdo libapache2-mod-php
```

### 2. Copiar los archivos del proyecto

```bash
sudo cp -r /ruta/al/proyecto/* /var/www/html/consolidacion/
sudo chown -R www-data:www-data /var/www/html/consolidacion/
sudo chmod -R 755 /var/www/html/consolidacion/
```

### 3. Crear la base de datos

```bash
# Ingresar a MySQL (usar sudo si no tiene contraseña de root configurada)
sudo mysql -u root -p

# Dentro de la consola MySQL:
source /var/www/html/consolidacion/database.sql;
exit;
```

O de forma alternativa:

```bash
sudo mysql -u root -p < /var/www/html/consolidacion/database.sql
```

### 4. Configurar la conexión

```bash
sudo nano /var/www/html/consolidacion/config/database.php
```

Ajustar usuario y contraseña de MySQL según su configuración.

### 5. Reiniciar Apache

```bash
sudo systemctl restart apache2
```

### 6. Acceder a la aplicación

Abrir el navegador en: `http://localhost/consolidacion/`

> **Nota para Ubuntu 22.04+:** si Apache no tiene el módulo `rewrite` activo, ejecute:
> ```bash
> sudo a2enmod rewrite
> sudo systemctl restart apache2
> ```

---

## 🍎 Instalación en macOS

### Opción A – Con MAMP (recomendado para desarrollo)

1. **Descargar e instalar MAMP**
   - Ir a <https://www.mamp.info> y descargar MAMP gratuito para macOS.
   - Instalar normalmente arrastrando a la carpeta **Aplicaciones**.

2. **Copiar los archivos del proyecto**
   ```
   /Applications/MAMP/htdocs/consolidacion/
   ```

3. **Iniciar MAMP**
   - Abrir la aplicación MAMP y hacer clic en **Start Servers**.

4. **Crear la base de datos**
   - Abrir `http://localhost:8888/phpmyadmin` (MAMP usa el puerto 8888 por defecto).
   - Ir a la pestaña **Importar** y seleccionar `database.sql`.

5. **Configurar la conexión**
   - Abrir `config/database.php` y ajustar el puerto si es necesario:
     ```php
     define('DB_PORT', '8889'); // Puerto MySQL de MAMP
     define('DB_USER', 'root');
     define('DB_PASSWORD', 'root'); // Contraseña por defecto de MAMP
     ```

6. **Acceder a la aplicación**
   - Abrir: `http://localhost:8888/consolidacion/`

### Opción B – Con Homebrew (entorno de desarrollo avanzado)

```bash
# Instalar Homebrew si no está instalado
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Instalar PHP y MySQL
brew install php mysql

# Iniciar MySQL
brew services start mysql

# Crear la base de datos
mysql -u root < /ruta/al/proyecto/database.sql

# Iniciar el servidor PHP integrado (desde la carpeta del proyecto)
cd /ruta/al/proyecto
php -S localhost:8080

# Abrir en el navegador: http://localhost:8080
```

Ajustar `config/database.php` con las credenciales de su instalación.

---

## ⚙️ Configuración rápida de `config/database.php`

| Parámetro    | Descripción                      | Valor predeterminado   |
|-------------|----------------------------------|------------------------|
| `DB_HOST`    | Servidor de la base de datos     | `localhost`            |
| `DB_PORT`    | Puerto MySQL                     | `3306`                 |
| `DB_NAME`    | Nombre de la base de datos       | `consolidacion_cfa`    |
| `DB_USER`    | Usuario MySQL                    | `root`                 |
| `DB_PASSWORD`| Contraseña MySQL                 | *(vacío)*              |
| `DB_CHARSET` | Juego de caracteres              | `utf8mb4`              |

---

## 📁 Estructura del proyecto

```
consolidacion/
├── config/
│   └── database.php       # Configuración de conexión a BD
├── css/
│   └── styles.css         # Estilos responsivos
├── includes/
│   ├── header.php         # Plantilla de encabezado
│   └── footer.php         # Plantilla de pie de página
├── index.php              # Listado y búsqueda de registros
├── registro.php           # Formulario de nuevo registro
├── editar.php             # Formulario de edición
├── eliminar.php           # Eliminación de registro
├── database.sql           # Script de creación de BD
├── INSTALL.md             # Este documento
└── README.md              # Descripción general
```

---

## 🚀 Funcionalidades del sistema

- **Listado** de todos los registros con búsqueda y filtros.
- **Nuevo registro** con validación en el servidor.
- **Editar** cualquier registro existente.
- **Eliminar** registros (con confirmación).
- **Estadísticas** rápidas (total, devocionales, cultos AM/PM).
- **Paginación** configurable.
- **Diseño responsivo** – funciona en móvil, tablet y escritorio.
- **Badges de color** por equipo para identificación visual.

---

## 🐛 Solución de problemas comunes

| Síntoma | Posible causa | Solución |
|---------|--------------|---------|
| "Access denied for user 'root'" | Contraseña incorrecta | Verificar `DB_PASSWORD` en `config/database.php` |
| "Unknown database 'consolidacion_cfa'" | BD no creada | Importar `database.sql` |
| Página en blanco | Error PHP oculto | Activar `display_errors = On` en `php.ini` |
| Caracteres especiales rotos | Charset incorrecto | Verificar que `DB_CHARSET` es `utf8mb4` |
| Error 403 en Apache | Permisos incorrectos | `chmod -R 755 /var/www/html/consolidacion` |

---

## 📄 Licencia

Uso exclusivo del Ministerio Consolidación & Ministración – Dpto. de Consolidación y Célula – CFA.
