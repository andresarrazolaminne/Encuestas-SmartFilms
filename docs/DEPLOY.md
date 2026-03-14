# Despliegue – Encuestas SmartFilms

## Nginx (document root del servidor)

El **home** de Nginx para la web es:

```
/usr/share/nginx/html
```

El proyecto debe clonarse en una carpeta bajo esa ruta (o en `/var/www`) y el **root** del server block debe apuntar a la carpeta **`public`** del proyecto.

---

## Instalación en el servidor

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

El usuario con el que corre Nginx/PHP-FPM (suele ser `nginx` o `www-data`) debe poder leer el proyecto y escribir en `storage/`:

```bash
sudo chown -R nginx:nginx /usr/share/nginx/html/encuestas-smartfilms
# o: sudo chown -R www-data:www-data /usr/share/nginx/html/encuestas-smartfilms
sudo chmod -R 755 /usr/share/nginx/html/encuestas-smartfilms
sudo mkdir -p /usr/share/nginx/html/encuestas-smartfilms/storage/sessions
sudo chmod -R 775 /usr/share/nginx/html/encuestas-smartfilms/storage
```

### 4. Base de datos

Si las tablas no existen, ejecutar el schema (sustituir host/usuario/BD por los de tu `.env`):

```bash
mysql -h TU_HOST -u TU_USUARIO -p TU_BASE < schema.sql
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
