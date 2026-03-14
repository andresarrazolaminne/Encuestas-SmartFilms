# Plan de trabajo – Sistema de gestión de encuestas (SmartFilms)

## 1. Resumen ejecutivo

Sistema web para crear, publicar y analizar formularios dinámicos con:
- **Área pública**: formularios con URL única, personalizables (fondo, colores, fuentes, tipos de campo).
- **Área privada**: autenticación simple, creador de formularios, panel de resultados y descarga.
- **Almacenamiento**: definición de formularios y respuestas en MySQL; respuestas por formulario en JSON dentro de la base de datos.

Diseñado para despliegue en **Apache o Nginx** con poco control de procesos (sin colas, workers complejos, etc.).

---

## 2. Stack tecnológico recomendado

| Capa | Tecnología | Motivo |
|------|------------|--------|
| Backend | **PHP 8.x** | Nativo en Apache/Nginx, sin daemons, fácil despliegue. |
| Base de datos | **MySQL 8.x** o **MariaDB** | JSON nativo, amplio soporte en hosting. |
| Frontend (público) | HTML, CSS, JS (vanilla o Alpine.js) | Formularios rápidos, sin build obligatorio. |
| Frontend (admin) | Mismo + JS para el editor de formularios | Consistencia y simplicidad. |
| Servidor web | Apache o Nginx | Según disponibilidad del servidor. |

Alternativa: **Node.js + Express** si el servidor permite mantener un proceso (PM2 o similar); el plan aplica igual cambiando solo la capa de aplicación.

---

## 3. Modelo de datos

### 3.1 Diagramo entidad-relación (resumen)

```
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────────┐
│     users       │     │     forms        │     │   form_responses    │
├─────────────────┤     ├──────────────────┤     ├─────────────────────┤
│ id (PK)         │     │ id (PK)          │     │ id (PK)             │
│ username        │────<│ created_by (FK)  │────<│ form_id (FK)        │
│ password_hash   │     │ slug (UNIQUE)     │     │ response_data (JSON)│
│ created_at      │     │ title            │     │ created_at          │
└─────────────────┘     │ config (JSON)    │     │ ip / user_agent     │
                        │ definition (JSON)│     └─────────────────────┘
                        │ created_at       │
                        │ updated_at       │
                        └──────────────────┘
```

- **users**: usuarios que pueden crear y administrar formularios.
- **forms**: cada formulario; `slug` = segmento de la URL única.
- **form_responses**: una fila por envío; `response_data` = respuestas en JSON.

### 3.2 Tablas SQL

```sql
-- Usuarios (autenticación simple)
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(64) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Formularios
CREATE TABLE forms (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  created_by INT UNSIGNED NOT NULL,
  slug VARCHAR(128) NOT NULL UNIQUE,
  title VARCHAR(255) NOT NULL,
  config JSON NOT NULL COMMENT 'Estilos: fondo, colores, fuentes, página de respuesta',
  definition JSON NOT NULL COMMENT 'Estructura: secciones, campos, opciones',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_forms_slug (slug)
);

-- Respuestas (una fila por envío, datos en JSON)
CREATE TABLE form_responses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  form_id INT UNSIGNED NOT NULL,
  response_data JSON NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  ip VARCHAR(45) NULL,
  user_agent VARCHAR(512) NULL,
  FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE,
  INDEX idx_responses_form (form_id),
  INDEX idx_responses_created (created_at)
);
```

### 3.3 Estructura de `config` (JSON) por formulario

Configuración visual y de comportamiento:

```json
{
  "theme": {
    "background": "#f5f5f5",
    "backgroundImage": null,
    "primaryColor": "#6b21a8",
    "textColor": "#1f2937",
    "fontFamily": "Inter, sans-serif",
    "borderRadius": "8px"
  },
  "responsePage": {
    "enabled": true,
    "title": "¡Gracias por participar!",
    "message": "Tu respuesta ha sido registrada.",
    "redirectUrl": null
  }
}
```

### 3.4 Estructura de `definition` (JSON) por formulario

Define secciones, campos y opciones (ejemplo alineado al primer formulario SmartFilms):

```json
{
  "sections": [
    {
      "id": "sec_1",
      "title": "1. Información básica",
      "order": 1,
      "fields": [
        {
          "id": "f_ciudad",
          "type": "text",
          "label": "¿En qué ciudad o municipio resides actualmente?",
          "required": true,
          "placeholder": "Ej: Medellín"
        },
        {
          "id": "f_programa",
          "type": "select",
          "label": "¿En qué programa de SMARTFILMS participaste?",
          "required": true,
          "options": [
            "Ruta Magenta",
            "SMARTFILMS Medellín",
            "SMARTFILMS Barranquilla",
            "SMARTFILMS Bogotá",
            "Otro"
          ]
        },
        {
          "id": "f_anio",
          "type": "number",
          "label": "¿En qué año participaste?",
          "required": true,
          "min": 2000,
          "max": 2030
        },
        {
          "id": "f_estrato",
          "type": "select",
          "label": "¿A qué estrato socioeconómico perteneces?",
          "required": true,
          "options": ["Estrato 1", "Estrato 2", "Estrato 3", "Estrato 4", "Estrato 5", "Estrato 6"]
        }
      ]
    },
    {
      "id": "sec_2",
      "title": "2. Situación antes de participar",
      "order": 2,
      "fields": [
        {
          "id": "f_situacion_antes",
          "type": "select",
          "label": "Antes de participar en el programa, tu situación laboral era:",
          "required": true,
          "options": [
            "Desempleado",
            "Estudiante",
            "Empleado formal",
            "Trabajador independiente",
            "Emprendedor",
            "Otro"
          ]
        },
        {
          "id": "f_experiencia_antes",
          "type": "select",
          "label": "Antes de participar, ¿tenías experiencia en producción audiovisual?",
          "required": true,
          "options": ["Ninguna", "Básica", "Intermedia", "Profesional"]
        },
        {
          "id": "f_ingreso_antes",
          "type": "select",
          "label": "Antes de participar en SMARTFILMS, ¿cuál era tu ingreso mensual aproximado?",
          "required": true,
          "options": [
            "No tenía ingresos",
            "Menos de $500.000",
            "$500.000 – $1.000.000",
            "$1.000.000 – $2.000.000",
            "Más de $2.000.000"
          ]
        }
      ]
    }
  ]
}
```

Tipos de campo a soportar en el editor: `text`, `textarea`, `number`, `email`, `select`, `radio`, `checkbox`, `date`. El primer formulario usa sobre todo `text`, `select` y `textarea` (testimonio).

### 3.5 Estructura de `response_data` (JSON) por respuesta

Un objeto plano: clave = `id` del campo, valor = respuesta (string, número o array para checkboxes):

```json
{
  "f_ciudad": "Medellín",
  "f_programa": "SMARTFILMS Medellín",
  "f_anio": 2024,
  "f_estrato": "Estrato 3",
  "f_situacion_antes": "Estudiante",
  "f_experiencia_antes": "Básica",
  "f_ingreso_antes": "Menos de $500.000"
}
```

---

## 4. URLs y flujos

| URL | Acción | Quién |
|-----|--------|--------|
| `/` | Redirección a login o listado de formularios | Admin |
| `/login` | Inicio de sesión | Público → Admin |
| `/logout` | Cierre de sesión | Admin |
| `/admin` | Listado de formularios del usuario | Admin (autenticado) |
| `/admin/forms/new` | Crear formulario | Admin |
| `/admin/forms/{id}` | Editar formulario (definición + config) | Admin |
| `/admin/forms/{id}/responses` | Ver y descargar respuestas | Admin |
| `/f/{slug}` | Formulario público (rellenar y enviar) | Público |

Todas las rutas “admin” exigen sesión; `/f/{slug}` es siempre pública.

---

## 5. Fases del proyecto (plan de trabajo detallado)

### Fase 1: Base del proyecto y base de datos

| # | Tarea | Detalle | Entregable |
|---|--------|---------|------------|
| 1.1 | Estructura de carpetas | `public/`, `src/`, `config/`, `templates/`, `docs/` | Estructura lista |
| 1.2 | Configuración y entorno | `.env` para DB y secret de sesión; no subir `.env` | Config cargable |
| 1.3 | Script SQL inicial | Crear tablas `users`, `forms`, `form_responses` | `schema.sql` |
| 1.4 | Conexión a MySQL | Clase o función de conexión PDO | Conexión funcionando |
| 1.5 | Usuario inicial | Script o migración que cree un `admin` con contraseña hasheada | Usuario para login |

### Fase 2: Autenticación

| # | Tarea | Detalle | Entregable |
|---|--------|---------|------------|
| 2.1 | Login (formulario + validación) | POST a `/login`; verificar usuario y contraseña | Login funcional |
| 2.2 | Sesión | Iniciar sesión PHP; cookie segura; timeout | Sesión estable |
| 2.3 | Middleware “requiere login” | Redirigir a `/login` si no hay sesión en rutas `/admin/*` | Rutas admin protegidas |
| 2.4 | Logout | Destruir sesión y redirigir a login | Logout funcional |

### Fase 3: CRUD de formularios (admin)

| # | Tarea | Detalle | Entregable |
|---|--------|---------|------------|
| 3.1 | Listado de formularios | Listar `forms` del usuario con enlace a editar y a respuestas | Vista `/admin` |
| 3.2 | Alta de formulario | Formulario mínimo: título, slug (único); guardar `config` y `definition` por defecto | Crear formulario vacío |
| 3.3 | Editor de definición | UI para añadir/editar secciones y campos (tipo, label, opciones, required) | Definición editable |
| 3.4 | Editor de configuración | UI para fondo, colores, fuentes, texto de página de respuesta/redirect | Config editable |
| 3.5 | Validación de slug | Slug único, solo caracteres permitidos; validar al guardar | Slugs únicos y seguros |
| 3.6 | Eliminar formulario | Con confirmación; borrar en cascada respuestas | Borrado seguro |

### Fase 4: Formulario público y respuestas

| # | Tarea | Detalle | Entregable |
|---|--------|---------|------------|
| 4.1 | Ruta `/f/{slug}` | Cargar formulario por slug; 404 si no existe | URL única por formulario |
| 4.2 | Renderizado dinámico | Generar HTML/inputs desde `definition` y aplicar `config` (estilos) | Formulario visible y coherente con config |
| 4.3 | Envío y validación | Validar required y tipos en servidor; construir `response_data` JSON | Respuesta validada |
| 4.4 | Guardar respuesta | INSERT en `form_responses` (form_id, response_data, ip, user_agent) | Respuestas en MySQL |
| 4.5 | Página de respuesta | Tras enviar: mensaje de agradecimiento y/o redirect según `config.responsePage` | UX post-envío |

### Fase 5: Panel de resultados y exportación

| # | Tarea | Detalle | Entregable |
|---|--------|---------|------------|
| 5.1 | Listado de respuestas | Tabla por formulario: id, fecha, vista previa de campos; paginación si hay muchas | Vista `/admin/forms/{id}/responses` |
| 5.2 | Descarga CSV | Generar CSV con columnas = campos del formulario, filas = respuestas | Botón “Descargar CSV” |
| 5.3 | Descarga JSON | Exportar respuestas en JSON (array de objetos) | Botón “Descargar JSON” |

### Fase 6: Primer formulario (SmartFilms) y despliegue

| # | Tarea | Detalle | Entregable |
|---|--------|---------|------------|
| 6.1 | Definición SmartFilms | Crear el JSON `definition` completo con las 8 secciones y todos los campos/opciones que diste | Formulario listo para importar o crear |
| 6.2 | Documentación de despliegue | Pasos para Apache/Nginx (document root, rewrite a `index.php`), PHP, MySQL, `.env` | `docs/DEPLOY.md` |
| 6.3 | Pruebas en entorno tipo producción | Probar flujo: login → crear/editar → publicar → enviar → descargar resultados | Checklist de aceptación |

---

## 6. Estructura de archivos sugerida

```
Encuestas SmartFilms/
├── config/
│   └── database.php
├── public/
│   ├── index.php          # Front controller (rutas)
│   ├── login.php          # Opcional: si no usas solo index.php
│   ├── css/
│   ├── js/
│   └── .htaccess          # Rewrite a index.php (Apache)
├── src/
│   ├── Auth.php
│   ├── FormRepository.php
│   ├── ResponseRepository.php
│   ├── Router.php
│   └── ...
├── templates/
│   ├── admin/
│   │   ├── dashboard.php
│   │   ├── form_edit.php
│   │   └── responses.php
│   ├── public/
│   │   ├── form_view.php
│   │   └── thank_you.php
│   └── layout.php
├── data/                  # Opcional: seeds o JSON de ejemplo
│   └── smartfilms_definition.json
├── docs/
│   ├── PLAN_TRABAJO.md    # Este documento
│   └── DEPLOY.md
├── schema.sql
├── .env.example
└── README.md
```

---

## 7. Definición completa del primer formulario (SmartFilms)

En la siguiente sección se lista la definición del formulario para que puedas copiarla al editor o cargarla como seed. Los tipos de campo son: `text`, `textarea`, `select`, `number` según corresponda.

### Sección 1 – Información básica

| Campo | Tipo | Label | Opciones / Notas |
|-------|------|--------|------------------|
| ciudad | text | ¿En qué ciudad o municipio resides actualmente? | — |
| programa | select | ¿En qué programa de SMARTFILMS participaste? | Ruta Magenta, SMARTFILMS Medellín, SMARTFILMS Barranquilla, SMARTFILMS Bogotá, Otro |
| anio | number | ¿En qué año participaste? | min/max 2000–2030 |
| estrato | select | ¿A qué estrato socioeconómico perteneces? | Estrato 1 … Estrato 6 |

### Sección 2 – Situación antes de participar

| Campo | Tipo | Label | Opciones |
|-------|------|--------|----------|
| situacion_antes | select | Antes de participar en el programa, tu situación laboral era: | Desempleado, Estudiante, Empleado formal, Trabajador independiente, Emprendedor, Otro |
| experiencia_antes | select | Antes de participar, ¿tenías experiencia en producción audiovisual? | Ninguna, Básica, Intermedia, Profesional |
| ingreso_antes | select | Antes de participar en SMARTFILMS, ¿cuál era tu ingreso mensual aproximado? | No tenía ingresos, Menos de $500.000, $500.000 – $1.000.000, $1.000.000 – $2.000.000, Más de $2.000.000 |

### Sección 3 – Situación después de participar

| Campo | Tipo | Label | Opciones |
|-------|------|--------|----------|
| situacion_actual | select | Actualmente tu situación laboral es: | Empleado en sector audiovisual, Empleado en otro sector, Freelance en producción audiovisual, Emprendedor en industria creativa, Estudiante, Desempleado |
| genera_ingresos | select | ¿Actualmente generas ingresos gracias a actividades relacionadas con el audiovisual o la creación de contenido? | Sí, No, Parcialmente |
| ingreso_actual | select | ¿Cuál es tu ingreso mensual actual aproximado? | No tengo ingresos, Menos de $500.000, $500.000 – $1.000.000, $1.000.000 – $2.000.000, $2.000.000 – $5.000.000, $5.000.000 – $10.000.000, Más de $10.000.000 |

### Sección 4 – Emprendimiento

| Campo | Tipo | Label | Opciones |
|-------|------|--------|----------|
| proyecto_empresa | select | ¿Has creado algún proyecto o empresa relacionada con audiovisual o contenidos digitales después de participar en SMARTFILMS? | Sí, No, Estoy en proceso |
| personas_trabajan | select | Si respondiste sí, ¿cuántas personas trabajan contigo? | Solo yo, 2–3 personas, 4–10 personas, Más de 10 |

### Sección 5 – Impacto social

| Campo | Tipo | Label | Opciones |
|-------|------|--------|----------|
| mejoro_oportunidades | select | ¿Consideras que participar en SMARTFILMS mejoró tus oportunidades laborales? | Mucho, Algo, Poco, Nada |
| nuevas_oportunidades | select | ¿Participar en SMARTFILMS te permitió acceder a nuevas oportunidades educativas o profesionales? | Sí, No |
| proyecto_vida | select | ¿El programa influyó positivamente en tu proyecto de vida? | Mucho, Algo, Poco, Nada |

### Sección 6 – Contexto de vulnerabilidad

| Campo | Tipo | Label | Opciones |
|-------|------|--------|----------|
| situacion_antes_vuln | select | Antes de participar en SMARTFILMS, ¿vivías en alguna de estas situaciones? | Zona con presencia de violencia o conflicto armado, Barrio o comunidad con alta vulnerabilidad social, Desempleo prolongado, Falta de acceso a educación superior, Ninguna de las anteriores |
| alejarse_riesgo | select | ¿Participar en SMARTFILMS te ayudó a alejarte de situaciones de riesgo social? | Sí, No, Parcialmente |

### Sección 7 – Impacto cultural

| Campo | Tipo | Label | Opciones |
|-------|------|--------|----------|
| producido_contenidos | select | Después de participar en SMARTFILMS, ¿has producido contenidos audiovisuales? | Sí, No |
| cantidad_contenidos | select | ¿Cuántos contenidos has producido aproximadamente? | 1–3, 4–10, 10–20, Más de 20 |

### Sección 8 – Testimonio

| Campo | Tipo | Label | Opciones |
|-------|------|--------|----------|
| testimonio | textarea | En una frase, ¿cómo cambió SMARTFILMS tu vida? | — |

El archivo `data/smartfilms_definition.json` puede contener el `definition` completo en formato JSON para importar o seed.

---

## 8. Criterios de aceptación (resumen)

- [ ] Login/logout y rutas admin protegidas.
- [ ] Crear y editar formularios (secciones, campos, tipos, opciones).
- [ ] Configurar tema (fondo, colores, fuentes) y página de respuesta.
- [ ] Cada formulario accesible en `/f/{slug}` con estilos aplicados.
- [ ] Respuestas guardadas en MySQL en una tabla con JSON.
- [ ] Panel de respuestas por formulario con descarga CSV y JSON.
- [ ] Primer formulario SmartFilms creado con los 8 bloques y opciones indicadas.
- [ ] Documentación de despliegue para Apache o Nginx.

Si quieres, el siguiente paso puede ser generar el `schema.sql`, el `smartfilms_definition.json` completo y la estructura base de carpetas y `index.php` para empezar la Fase 1.
