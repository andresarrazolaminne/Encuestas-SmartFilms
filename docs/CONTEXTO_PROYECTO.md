# Contexto del proyecto – Encuestas SmartFilms

Documento para retomar el trabajo al mover el proyecto a WSL u otro entorno.

---

## Qué es el proyecto

Sistema web para **gestionar encuestas/formularios dinámicos**:

- **Público:** formularios con URL única (`/f/{slug}`), personalizables (fondo, colores, fuentes).
- **Admin (tras login):** crear/editar formularios (secciones, campos, tipos, opciones), configurar tema, ver y descargar respuestas (CSV/JSON).
- **Base de datos:** MySQL; definición y respuestas en JSON dentro de las tablas.

Despliegue previsto: Apache o Nginx, sin procesos complejos (PHP puro).

---

## Stack

- **Backend:** PHP 8.x
- **Base de datos:** MySQL 8.x / MariaDB
- **Frontend:** HTML, CSS, JS (vanilla o Alpine.js)

---

## Base de datos (servidor remoto)

- **Host:** 70.38.95.210
- **Base de datos:** administrator_fowforms
- **Usuario:** administrator_user_fowforms
- **Contraseña:** está en `.env` (archivo `DB_PASS`); no versionado en Git.

Las credenciales se cargan desde **`.env`** en la raíz del proyecto. Ver `.env.example` para la plantilla.

---

## Estructura actual del repositorio

```
Encuestas SmartFilms/
├── config/
│   ├── load_env.php      # Carga .env a getenv() / $_ENV
│   └── database.php      # get_db() → PDO
├── data/
│   └── smartfilms_definition.json   # Definición completa 1er formulario (8 secciones)
├── docs/
│   ├── PLAN_TRABAJO.md   # Plan en 6 fases, URLs, criterios
│   ├── MODELO_DATOS.md   # Diagrama y estructura JSON
│   └── CONTEXTO_PROYECTO.md  # Este archivo
├── public/
│   └── test_db.php       # Prueba conexión BD por navegador
├── .env                  # Credenciales (NO subir a Git)
├── .env.example          # Plantilla sin datos sensibles
├── .gitignore            # Incluye .env
├── schema.sql            # CREATE tables + usuario inicial admin
└── test_db.php           # Prueba conexión por CLI: php test_db.php
```

---

## Estado del desarrollo

- **Hecho:** plan de trabajo, modelo de datos, schema SQL, configuración BD (.env, `config/load_env.php`, `config/database.php`), definición JSON del primer formulario SmartFilms, scripts de prueba de conexión. **Fase 1:** front controller (`public/index.php`), `Router`, `.htaccess`, templates base (layout, home, login, admin/dashboard), rutas `/`, `/login`, `/admin`, `/f/{slug}`.
- **Pendiente (según PLAN_TRABAJO.md):**
  1. ~~Fase 1: Estructura completa (front controller, rutas).~~
  2. ~~Fase 2: Autenticación (login/logout, sesión, protección admin).~~
  3. Fase 3: CRUD formularios en admin (editor de campos y config).
  4. Fase 4: Ruta pública `/f/{slug}`, envío y guardado de respuestas.
  5. Fase 5: Panel de respuestas y descarga CSV/JSON.
  6. Fase 6: Desplegar y documentar (Apache/Nginx).

---

## Al mover a WSL

1. Copiar/clonar el proyecto en WSL (incluir `.env` si no usas Git para él, o recrear `.env` desde `.env.example`).
2. En la raíz del proyecto, probar conexión:
   ```bash
   php test_db.php
   ```
   Debe mostrar "Conexión OK" y el nombre de la base de datos.
3. Si las tablas aún no existen en el servidor, ejecutar:
   ```bash
   mysql -h 70.38.95.210 -u administrator_user_fowforms -p administrator_fowforms < schema.sql
   ```
   (te pedirá la contraseña de `.env`).
4. Seguir con la Fase 1 del plan en `docs/PLAN_TRABAJO.md`.

### Servidor PHP integrado (desarrollo)

Desde la **raíz del proyecto** (donde están `config/`, `public/`, `src/`), ejecutar:

```bash
php -S localhost:8000 -t public public/index.php
```

Así todas las rutas (`/`, `/login`, `/admin`, etc.) pasan por `index.php`. Si solo usas `php -S localhost:8000 -t public`, solo `/` ejecuta el front controller y el resto da 404. Si ves "ERR_EMPTY_RESPONSE", usa el comando con `public/index.php` al final y revisa la página de error que mostrará el mensaje de PHP.

---

## Referencias rápidas

- **Plan detallado y fases:** `docs/PLAN_TRABAJO.md`
- **Modelo de datos y JSON:** `docs/MODELO_DATOS.md`
- **Primer formulario (campos/opciones):** `data/smartfilms_definition.json`

Cuando retomes en WSL, puedes decir: “retomamos desde el contexto en docs/CONTEXTO_PROYECTO.md” o “continuamos la Fase 1”.
