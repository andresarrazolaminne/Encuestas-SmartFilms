# Despliegue – Encuestas SmartFilms

## Nginx (document root del servidor)

El **home** de Nginx para la web es: **`/usr/share/nginx/html`**.  
El **root** del server block debe apuntar a la carpeta **`public`** del proyecto.

---

## Paso a paso (despliegue completo)

### Paso 1 – Conectarte al servidor

```bash
ssh usuario@IP_DEL_SERVIDOR
```

---

### Paso 2 – Comprobar PHP y Nginx

```bash
php -v          # Debe ser 8.x
nginx -v        # Nginx instalado
```

Si no hay PHP: `sudo apt update && sudo apt install php-fpm php-mysql php-mbstring php-json` (o el gestor de paquetes de tu distro).

---

### Paso 3 – Clonar el repositorio

```bash
cd /usr/share/nginx/html
sudo git clone https://github.com/andresarrazolaminne/Encuestas-SmartFilms.git encuestas-smartfilms
cd encuestas-smartfilms
```

---

### Paso 4 – Crear y editar el archivo .env

```bash
sudo cp .env.example .env
sudo nano .env
```

Completa (y guarda con Ctrl+O, Enter, Ctrl+X):

- `DB_HOST` = IP o host de tu MySQL
- `DB_NAME` = nombre de la base de datos
- `DB_USER` = usuario de la base de datos
- `DB_PASS` = contraseña

---

### Paso 5 – Permisos para Nginx/PHP-FPM

Averigua el usuario (suele ser `nginx` o `www-data`):

```bash
ps aux | grep nginx
```

Luego ejecuta **cada comando por separado** (sustituye `nginx` por `www-data` si aplica):

```bash
sudo chown -R nginx:nginx /usr/share/nginx/html/encuestas-smartfilms
```

```bash
sudo chmod -R 755 /usr/share/nginx/html/encuestas-smartfilms
```

```bash
sudo mkdir -p /usr/share/nginx/html/encuestas-smartfilms/storage/sessions
```

```bash
sudo chmod -R 775 /usr/share/nginx/html/encuestas-smartfilms/storage
```

---

### Paso 6 – Base de datos

Si la base de datos está vacía, crea las tablas. **Sustituye** `TU_HOST`, `TU_USUARIO`, `TU_BASE` por los valores de tu archivo `.env` (DB_HOST, DB_USER, DB_NAME). La contraseña la pedirá al ejecutar:

```bash
cd /usr/share/nginx/html/encuestas-smartfilms
mysql -h TU_HOST -u TU_USUARIO -p TU_BASE < schema.sql
```

Ejemplo si en `.env` tienes `DB_HOST=70.38.95.210`, `DB_USER=administrator_user_fowforms`, `DB_NAME=administrator_fowforms`:

```bash
mysql -h 70.38.95.210 -u administrator_user_fowforms -p administrator_fowforms < schema.sql
```

Luego fija la contraseña del usuario admin para poder entrar:

```bash
php fix_admin_password.php
```

(Después podrás entrar con usuario **admin** y contraseña **admin123**.)

---

### Paso 7 – Buscar el socket de PHP-FPM

```bash
ls /run/php/php*.sock
```

Anota el nombre (ej. `php8.1-fpm.sock` o `php-fpm.sock`) para el siguiente paso.

---

### Paso 8 – Configurar Nginx

Edita el archivo de configuración del sitio (puede ser `/etc/nginx/nginx.conf` o un archivo en `/etc/nginx/conf.d/` o `/etc/nginx/sites-available/`). Asegúrate de tener un bloque como este (ajusta `server_name` y la ruta del socket si es distinta):

```nginx
server {
    listen 80;
    server_name tudominio.com;   # o la IP del servidor

    root /usr/share/nginx/html/encuestas-smartfilms/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;   # usar el socket del paso 7
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

Comprueba y recarga Nginx:

```bash
sudo nginx -t
sudo systemctl reload nginx
```

---

### Paso 9 – Probar en el navegador

- Abre `http://IP_O_DOMINIO/` o la URL que hayas puesto en `server_name`.
- Deberías ver la página de inicio o el login.
- Entra con **admin** / **admin123** (o la contraseña que fijaste con `fix_admin_password.php`).

---

### Paso 10 – Producción (recomendado)

- **HTTPS:** instala un certificado (ej. `certbot`) y configura `listen 443 ssl` en Nginx.
- **Oculta test_db.php:** borra o renombra `public/test_db.php` para que no sea accesible desde fuera.

---

## Resumen rápido (referencia)

### 1. Clonar el repositorio

```bash
cd /usr/share/nginx/html
sudo git clone https://github.com/andresarrazolaminne/Encuestas-SmartFilms.git encuestas-smartfilms
cd encuestas-smartfilms
```

### 2. Configurar entorno

```bash
sudo cp .env.example .env
sudo nano .env   # Rellenar DB_HOST, DB_NAME, DB_USER, DB_PASS
```

### 3. Permisos

Ejecutar uno por uno (cambiar `nginx` por `www-data` si aplica):

```bash
sudo chown -R nginx:nginx /usr/share/nginx/html/encuestas-smartfilms
sudo chmod -R 755 /usr/share/nginx/html/encuestas-smartfilms
sudo mkdir -p /usr/share/nginx/html/encuestas-smartfilms/storage/sessions
sudo chmod -R 775 /usr/share/nginx/html/encuestas-smartfilms/storage
```

### 4. Base de datos

Sustituir HOST, USUARIO y BASE por los valores de tu `.env` (DB_HOST, DB_USER, DB_NAME). Ejemplo:

```bash
mysql -h 70.38.95.210 -u administrator_user_fowforms -p administrator_fowforms < schema.sql
```

Luego, si hace falta, fijar la contraseña del admin:

```bash
php fix_admin_password.php
```

### 5. Nginx: document root

En el server block que sirva esta aplicación, el **root** debe ser la carpeta **public**:

```nginx
root /usr/share/nginx/html/encuestas-smartfilms/public;
index index.php;

location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/run/php/php8.1-fpm.sock;   # ajustar versión si aplica
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    include fastcgi_params;
}
```

Recargar Nginx tras cambiar la config:

```bash
sudo nginx -t
sudo systemctl reload nginx
```

---

## Producción

- Eliminar o restringir acceso a `public/test_db.php`.
- Usar HTTPS (certificado y `listen 443 ssl` en Nginx).
- En `.env` no subir credenciales a Git (`.env` está en `.gitignore`).
