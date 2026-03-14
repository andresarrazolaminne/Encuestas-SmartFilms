# Modelo de datos – Encuestas SmartFilms

## Diagrama relacional

```
                    ┌─────────────────────────────────────┐
                    │              users                   │
                    ├─────────────────────────────────────┤
                    │ id (PK)                              │
                    │ username (UNIQUE)                    │
                    │ password_hash                        │
                    │ created_at                           │
                    └──────────────────┬──────────────────┘
                                       │ 1
                                       │
                                       │ N
                    ┌──────────────────▼──────────────────┐
                    │              forms                  │
                    ├─────────────────────────────────────┤
                    │ id (PK)                              │
                    │ created_by (FK → users.id)           │
                    │ slug (UNIQUE)  ← URL: /f/{slug}      │
                    │ title                                │
                    │ config (JSON)   ← tema, página OK    │
                    │ definition (JSON) ← secciones/campos│
                    │ created_at, updated_at               │
                    └──────────────────┬──────────────────┘
                                       │ 1
                                       │
                                       │ N
                    ┌──────────────────▼──────────────────┐
                    │          form_responses              │
                    ├─────────────────────────────────────┤
                    │ id (PK)                              │
                    │ form_id (FK → forms.id)              │
                    │ response_data (JSON) ← respuestas    │
                    │ created_at                           │
                    │ ip, user_agent (opcional)            │
                    └─────────────────────────────────────┘
```

## Contenido de los JSON

### `forms.config`

| Clave | Tipo | Descripción |
|-------|------|-------------|
| theme.background | string | Color o URL de imagen de fondo |
| theme.primaryColor | string | Color principal (botones, bordes) |
| theme.textColor | string | Color del texto |
| theme.fontFamily | string | Fuente CSS |
| theme.borderRadius | string | Ej: "8px" |
| responsePage.enabled | bool | Mostrar página de agradecimiento |
| responsePage.title | string | Título de la página de gracias |
| responsePage.message | string | Mensaje |
| responsePage.redirectUrl | string \| null | URL a la que redirigir (opcional) |

### `forms.definition`

- **sections**: array de secciones en orden.
  - **id**: identificador único (ej. `sec_1`).
  - **title**: título de la sección.
  - **order**: número para ordenar.
  - **fields**: array de campos.
    - **id**: identificador único (ej. `ciudad`); será la clave en `response_data`.
    - **type**: `text` | `textarea` | `number` | `email` | `select` | `radio` | `checkbox` | `date`.
    - **label**: texto de la pregunta.
    - **required**: boolean.
    - **placeholder**: opcional.
    - **options**: array de strings (para select/radio).
    - **min** / **max**: para number/date si aplica.
    - **rows**: para textarea si aplica.

### `form_responses.response_data`

Objeto plano: cada clave es el `id` de un campo y el valor es la respuesta (string, número o array en checkboxes).

Ejemplo:

```json
{
  "ciudad": "Medellín",
  "programa": "SMARTFILMS Medellín",
  "anio": 2024,
  "estrato": "Estrato 3",
  "situacion_antes": "Estudiante",
  "experiencia_antes": "Básica",
  "ingreso_antes": "Menos de $500.000",
  "situacion_actual": "Freelance en producción audiovisual",
  "genera_ingresos": "Sí",
  "ingreso_actual": "$500.000 – $1.000.000",
  "proyecto_empresa": "Estoy en proceso",
  "personas_trabajan": "",
  "mejoro_oportunidades": "Mucho",
  "nuevas_oportunidades": "Sí",
  "proyecto_vida": "Mucho",
  "situacion_antes_vuln": "Ninguna de las anteriores",
  "alejarse_riesgo": "Sí",
  "producido_contenidos": "Sí",
  "cantidad_contenidos": "1–3",
  "testimonio": "SMARTFILMS me abrió las puertas al mundo audiovisual."
}
```

## Tipos de campo soportados

| type | Uso | Validación |
|------|-----|------------|
| text | Una línea | required, longitud opcional |
| textarea | Varias líneas | required, rows |
| number | Número | required, min, max |
| email | Email | required, formato email |
| select | Desplegable | required, options |
| radio | Opción única | required, options |
| checkbox | Varias opciones | options, valor = array |
| date | Fecha | required, min, max |
