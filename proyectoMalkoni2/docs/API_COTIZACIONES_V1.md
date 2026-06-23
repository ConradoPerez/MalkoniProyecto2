# API de Integración - Sistema de Cotizaciones v1

**Fecha de especificación**: 23 de junio de 2026  
**Versión**: 1.0.0  
**Proyecto**: Integración malkoni-online ↔ proyectoMalkoni2

---

## 📋 Resumen General

Esta API permite la importación de pedidos del Optimizador de Cortes (OPT) desde el sistema `malkoni-online` hacia el nuevo sistema de cotizaciones `proyectoMalkoni2`. El objetivo es sincronizar información de clientes y crear cotizaciones de manera idempotente a partir de eventos generados cuando un usuario presiona el botón "Cotizar" en el listado de pedidos OPT.

---

## 🔗 Información General del Endpoint

### Endpoint Principal

**Ruta**: `POST /api/v1/cotizaciones/importar`

**URL Base** (en desarrollo):
- Local: `http://localhost:8000/api/v1/cotizaciones/importar`

**URL Base** (en producción - a definir):
- Opción A: `https://cotizaciones.malkoni.com.ar/api/v1/cotizaciones/importar`
- Opción B: `https://malkoni-cotizaciones.vercel.app/api/v1/cotizaciones/importar`

### Formato de Comunicación

- **Método HTTP**: `POST`
- **Content-Type**: `application/json`
- **Charset**: UTF-8

### Protocolo de Seguridad

Este endpoint utiliza **autenticación estática mediante token compartido** para validar que las peticiones provienen exclusivamente del sistema `malkoni-online`.

**Mecanismo de Autenticación**:

El sistema de origen (`malkoni-online`) debe incluir un header personalizado en cada petición:

```
X-Integration-Token: {token_secreto}
```

**Validación en el Backend**:

El sistema receptor (`proyectoMalkoni2`) contrastará el valor recibido en el header `X-Integration-Token` contra una variable de entorno configurada en el archivo `.env`:

```env
INTEGRATION_TOKEN=tu_token_secreto_seguro_aqui
```

Si el token no coincide, es inválido o está ausente, el sistema responderá con un código `401 Unauthorized`.

**⚠️ Importante**: 
- El token debe mantenerse estrictamente confidencial.
- En producción, el token debe generarse usando un mecanismo criptográficamente seguro (mínimo 32 caracteres alfanuméricos).
- El token debe configurarse manualmente en ambos sistemas antes del despliegue.

---

## 📦 Especificación del Payload (Request Body JSON)

### Campos Obligatorios

Todos los campos listados a continuación son **estrictamente obligatorios** (`required`). La ausencia de cualquiera de ellos resultará en una respuesta `400 Bad Request`.

| Campo | Tipo | Descripción | Restricciones |
|-------|------|-------------|---------------|
| `persona_external_id` | `integer` | ID de la persona en el sistema de origen (malkoni-online). | Mayor a 0. |
| `empresa_external_id` | `integer` | ID de la empresa principal asociada a la persona en el sistema de origen. | Mayor a 0. |
| `empresa_activa_external_id` | `integer` | **Campo crítico**. ID de la empresa activa seleccionada por el usuario al momento de presionar "Cotizar". Esta puede diferir de `empresa_external_id` en escenarios multi-empresa. | Mayor a 0. |
| `token_opt` | `string` | Token alfanumérico de 20 caracteres que identifica al usuario en el sistema Lepton OPT. | Longitud exacta: 20 caracteres. |
| `pedido_id` | `integer` | ID único del pedido de corte generado en Lepton. | Mayor a 0. |
| `pdf_url` | `string` (URL) | URL absoluta del archivo PDF del plano alojado en Amazon S3. | Formato: `https://optionline-prod-files.s3.amazonaws.com/planos/{pedido_id}_.pdf` |

### Campos Opcionales (Datos Contextuales)

Estos campos son **opcionales** pero altamente recomendados para enriquecer el contexto de la cotización:

| Campo | Tipo | Descripción | Valor por defecto |
|-------|------|-------------|-------------------|
| `project` | `string` | Nombre del proyecto/obra asociado al corte. | `null` |
| `mat_descri` | `string` | Descripción del material cortado (ej: "Melamina Blanco 18mm"). | `null` |
| `cant_placas` | `integer` | Cantidad de placas procesadas en el corte. | `null` |
| `created_date` | `integer` | Timestamp Unix del momento de creación del pedido en Lepton (en milisegundos). | `null` |

### Validaciones Adicionales

- **`token_opt`**: Debe contener exactamente 20 caracteres alfanuméricos y guiones bajos (`[a-zA-Z0-9_]{20}`).
- **`pdf_url`**: Debe ser una URL válida que comience con `https://` y apunte al dominio de Amazon S3.
- **`cant_placas`** (si se envía): Debe ser mayor a 0.
- **IDs externos**: Todos los IDs de tipo `integer` deben ser mayores a 0.

---

## 🔄 Comportamiento de Idempotencia

Para garantizar la consistencia de datos y evitar la creación de cotizaciones duplicadas cuando el usuario reintenta la operación (por ejemplo, por doble clic o errores de red), el sistema implementa un mecanismo de **idempotencia basado en clave de negocio compuesta**.

### Clave de Idempotencia

La unicidad de una cotización se determina por la siguiente combinación de campos:

```
pedido_id + persona_external_id + empresa_activa_external_id
```

Esta combinación se almacena en la base de datos mediante un índice único compuesto en la tabla `cotizaciones`:

```sql
UNIQUE INDEX cotizaciones_opt_persona_empresa_unique (
    pedido_opt_id, 
    persona_external_id_snapshot, 
    empresa_external_id_snapshot
)
```

### Flujo de Procesamiento

1. **Primera Petición** (cotización nueva):
   - El sistema verifica que no existe una cotización con la clave de idempotencia.
   - Ejecuta el **upsert** de persona y empresa (sincronización on-demand).
   - Crea la relación N:N en `persona_empresa` si no existe.
   - Crea una nueva cotización con número formateado: `OPT-{pedido_id}`.
   - Responde con código `201 Created` y la URL de redirección.

2. **Peticiones Subsecuentes** (cotización existente):
   - El sistema detecta que ya existe una cotización con la misma clave de idempotencia.
   - **NO** crea una nueva cotización.
   - Recupera la cotización existente.
   - Responde con código `200 OK` y la URL de redirección de la cotización preexistente.

Este comportamiento garantiza que múltiples llamadas con los mismos parámetros de negocio produzcan el mismo resultado sin efectos secundarios adicionales.

---

## 📡 Códigos de Respuesta HTTP

### Respuestas Exitosas

| Código | Descripción | Cuándo se Retorna |
|--------|-------------|-------------------|
| **`201 Created`** | **Cotización creada exitosamente**. Se procesó el upsert de persona/empresa, se creó la relación pivot y se generó una nueva cotización. | Primera importación del pedido. |
| **`200 OK`** | **Cotización ya existente**. El pedido fue importado previamente. Se devuelve la URL de la cotización existente sin crear duplicados. | Reintento o petición idempotente. |

### Respuestas de Error del Cliente

| Código | Descripción | Cuándo se Retorna |
|--------|-------------|-------------------|
| **`400 Bad Request`** | **Payload malformado**. JSON inválido, campos faltantes obligatorios o tipos de datos incorrectos. | Faltan campos `required` o JSON no parseable. |
| **`401 Unauthorized`** | **Autenticación fallida**. El header `X-Integration-Token` es inválido, está ausente o no coincide con el token configurado. | Token incorrecto o ausente. |
| **`422 Unprocessable Entity`** | **Validación de negocio fallida**. Aunque el JSON es válido, los valores no cumplen las reglas de negocio. | `cant_placas <= 0`, strings vacíos, IDs <= 0, formato de `pdf_url` inválido. |

### Respuestas de Error del Servidor

| Código | Descripción | Cuándo se Retorna |
|--------|-------------|-------------------|
| **`500 Internal Server Error`** | **Error interno no controlado**. Excepción inesperada, falla de base de datos o error de lógica de negocio. | Errores de conexión a BD, excepciones no capturadas. |

---

## 💡 Ejemplos de Uso

### 1. Request Completo (Cotización Nueva)

**Headers**:
```http
POST /api/v1/cotizaciones/importar HTTP/1.1
Host: cotizaciones.malkoni.com.ar
Content-Type: application/json
X-Integration-Token: a8f5e2c9b1d4f7a3e6c8b2d5f9a1c4e7
```

**Body**:
```json
{
  "persona_external_id": 123,
  "empresa_external_id": 456,
  "empresa_activa_external_id": 456,
  "token_opt": "Xy9Km3Lp8Qw2Rt5Nv7Bz",
  "pedido_id": 4926740,
  "pdf_url": "https://optionline-prod-files.s3.amazonaws.com/planos/4926740_.pdf",
  "project": "Muebles Cocina - Depto A201",
  "mat_descri": "Melamina Blanco Premium 18mm",
  "cant_placas": 8,
  "created_date": 1751270895000
}
```

---

### 2. Response de Éxito - Cotización Creada (`201 Created`)

**Headers**:
```http
HTTP/1.1 201 Created
Content-Type: application/json
```

**Body**:
```json
{
  "success": true,
  "message": "Cotización importada exitosamente desde Optimizador de Cortes.",
  "data": {
    "cotizacion_id": 1247,
    "numero": "OPT-4926740",
    "estado": "Nuevo",
    "persona_id": 89,
    "empresa_id": 124,
    "pedido_opt_id": 4926740,
    "pdf_url": "https://optionline-prod-files.s3.amazonaws.com/planos/4926740_.pdf",
    "created_at": "2026-06-23T14:32:18.000000Z"
  },
  "redirect_url": "https://cotizaciones.malkoni.com.ar/cliente/cotizacion/1247/seleccionar-vendedor"
}
```

**Campo `redirect_url`**:
- **Propósito**: URL absoluta a la que el sistema `malkoni-online` debe redirigir automáticamente al usuario después de procesar la respuesta exitosa.
- **Formato**: `{base_url}/cliente/cotizacion/{cotizacion_id}/seleccionar-vendedor`
- **Destino**: Pantalla del cliente donde puede asignar un vendedor y agregar productos adicionales a la cotización.

---

### 3. Response de Éxito - Cotización Existente (`200 OK`)

**Headers**:
```http
HTTP/1.1 200 OK
Content-Type: application/json
```

**Body**:
```json
{
  "success": true,
  "message": "Cotización previamente importada. Redirigiendo a la cotización existente.",
  "data": {
    "cotizacion_id": 1247,
    "numero": "OPT-4926740",
    "estado": "Abierto",
    "persona_id": 89,
    "empresa_id": 124,
    "pedido_opt_id": 4926740,
    "pdf_url": "https://optionline-prod-files.s3.amazonaws.com/planos/4926740_.pdf",
    "created_at": "2026-06-23T14:32:18.000000Z",
    "updated_at": "2026-06-23T15:10:42.000000Z"
  },
  "redirect_url": "https://cotizaciones.malkoni.com.ar/cliente/cotizacion/1247/seleccionar-vendedor"
}
```

---

### 4. Response de Error - Token Inválido (`401 Unauthorized`)

**Headers**:
```http
HTTP/1.1 401 Unauthorized
Content-Type: application/json
```

**Body**:
```json
{
  "success": false,
  "message": "Token de integración inválido o ausente.",
  "error": "UNAUTHORIZED",
  "hint": "Verifique el header X-Integration-Token y asegúrese de que coincida con el token configurado en el servidor."
}
```

---

### 5. Response de Error - Validación Fallida (`422 Unprocessable Entity`)

**Headers**:
```http
HTTP/1.1 422 Unprocessable Entity
Content-Type: application/json
```

**Body**:
```json
{
  "success": false,
  "message": "Los datos proporcionados no cumplen con las reglas de validación.",
  "errors": {
    "persona_external_id": [
      "El campo persona_external_id es obligatorio.",
      "El campo persona_external_id debe ser mayor a 0."
    ],
    "token_opt": [
      "El campo token_opt debe tener exactamente 20 caracteres."
    ],
    "cant_placas": [
      "El campo cant_placas debe ser mayor a 0."
    ],
    "pdf_url": [
      "El campo pdf_url debe ser una URL válida."
    ]
  }
}
```

**Estructura de `errors`**:
- Objeto indexado por nombre de campo.
- Cada clave contiene un array de mensajes de error específicos para ese campo.
- Facilita el mapeo directo de errores en el frontend.

---

### 6. Response de Error - Payload Malformado (`400 Bad Request`)

**Headers**:
```http
HTTP/1.1 400 Bad Request
Content-Type: application/json
```

**Body**:
```json
{
  "success": false,
  "message": "Payload JSON malformado o campos requeridos faltantes.",
  "error": "BAD_REQUEST",
  "details": "Syntax error: Unexpected end of JSON input at character 245."
}
```

---

### 7. Response de Error - Error Interno del Servidor (`500 Internal Server Error`)

**Headers**:
```http
HTTP/1.1 500 Internal Server Error
Content-Type: application/json
```

**Body**:
```json
{
  "success": false,
  "message": "Ocurrió un error interno al procesar la solicitud. Por favor, contacte al equipo de soporte.",
  "error": "INTERNAL_SERVER_ERROR",
  "request_id": "req_a8f5e2c9b1d4f7a3"
}
```

**Campo `request_id`**:
- Identificador único de la petición para fines de debugging y trazabilidad en logs.
- Debe almacenarse en la tabla `logs_integracion` para auditoría.

---

## 🔒 Consideraciones de Seguridad

### Nivel de Seguridad para Entrega Académica

Para la entrega del proyecto final, se implementa un nivel de seguridad **básico pero funcional** que garantiza:

1. **Autenticación estática con token compartido**.
2. **Validación estricta de campos obligatorios**.
3. **Sanitización de entradas para prevenir inyección SQL** (mediante Eloquent ORM).
4. **Logging de todas las peticiones** en la tabla `logs_integracion`.

### Recomendaciones para Producción Futura

Si este sistema llega a producción real (post-entrega académica), se recomienda fortalecer la seguridad con:

- **Autenticación HMAC con firma temporal**: Generar firmas usando HMAC-SHA256 con timestamp para evitar replay attacks.
- **Rotación periódica de tokens**: Implementar vencimiento y renovación automática de tokens.
- **Rate limiting**: Limitar cantidad de peticiones por IP/token por minuto.
- **Encriptación TLS 1.3**: Asegurar que todas las peticiones usen HTTPS estricto.
- **Auditoría de logs**: Implementar monitoreo activo de logs para detectar patrones anómalos.

---

## 📊 Logging y Trazabilidad

Cada petición al endpoint `/api/v1/cotizaciones/importar` debe registrarse en la tabla `logs_integracion` con los siguientes datos:

| Campo | Valor |
|-------|-------|
| `source_system` | `malkoni_online` |
| `metodo` | `POST` |
| `endpoint` | `/api/v1/cotizaciones/importar` |
| `request_id` | UUID autogenerado |
| `idempotency_key` | `{pedido_id}-{persona_external_id}-{empresa_activa_external_id}` |
| `http_status` | Código HTTP de la respuesta |
| `status` | `success` / `error` |
| `request_payload` | JSON completo del body recibido |
| `response_payload` | JSON completo de la respuesta enviada |
| `error_message` | Mensaje de error (si aplica) |
| `processed_at` | Timestamp del procesamiento |

Este registro permite:
- Debugging de errores en producción.
- Auditoría de operaciones de sincronización.
- Análisis de patrones de uso y performance.
- Reproducción de escenarios problemáticos.

---

## 🧪 Testing y Validación

### Casos de Prueba Recomendados

#### Test 1: Importación Exitosa (Happy Path)
- **Entrada**: Payload completo válido con todos los campos obligatorios.
- **Esperado**: `201 Created` con `redirect_url` válida.

#### Test 2: Idempotencia (Reintentos)
- **Entrada**: Mismo payload enviado 3 veces consecutivas.
- **Esperado**: Primera vez `201`, siguientes veces `200` con misma cotización.

#### Test 3: Token Inválido
- **Entrada**: Header `X-Integration-Token` con valor incorrecto.
- **Esperado**: `401 Unauthorized`.

#### Test 4: Campos Faltantes
- **Entrada**: Payload sin `persona_external_id`.
- **Esperado**: `400 Bad Request`.

#### Test 5: Validación de Negocio
- **Entrada**: `cant_placas = -5`.
- **Esperado**: `422 Unprocessable Entity` con error en `cant_placas`.

#### Test 6: URL de PDF Inválida
- **Entrada**: `pdf_url = "http://malicious-site.com/fake.pdf"`.
- **Esperado**: `422 Unprocessable Entity` con error en `pdf_url`.

---

## 🚀 Configuración de Variables de Entorno

En el archivo `.env` del sistema `proyectoMalkoni2`, agregar:

```env
# ======================================
# INTEGRACIÓN CON MALKONI-ONLINE
# ======================================

# Token de autenticación para la API de importación de cotizaciones
# Este token debe coincidir con el configurado en malkoni-online
INTEGRATION_TOKEN=tu_token_secreto_de_minimo_32_caracteres_aqui

# URL base del sistema de cotizaciones (sin trailing slash)
# En desarrollo
APP_URL=http://localhost:8000

# En producción (a definir según despliegue)
# Opción A: Subdominio dedicado
# APP_URL=https://cotizaciones.malkoni.com.ar

# Opción B: Deploy en Vercel
# APP_URL=https://malkoni-cotizaciones.vercel.app
```

---

## 📝 Notas Adicionales

### Sobre el Número de Cotización

- **Formato**: `OPT-{pedido_id}`
- **Ejemplo**: `OPT-4926740`
- **Propósito**: Permite identificar rápidamente cotizaciones originadas desde el OPT y evitar colisiones con el atributo `unique` existente en el campo `numero` de la tabla `cotizaciones`.

### Sobre la Sincronización de Datos

- **Modo**: On-demand (solo cuando se importa un pedido).
- **Estrategia**: Upsert por `id_persona_externo` e `id_empresa_externo`.
- **Datos Sincronizados**: 
  - Persona: nombre, apellido, email, teléfono, DNI, género, token_opt.
  - Empresa: razón social, CUIT, condición IVA, email, teléfono.
  - Relación persona-empresa en tabla pivot.

### Sobre el Flujo Post-Importación

Después de una importación exitosa (códigos `201` o `200`):

1. `malkoni-online` redirige al usuario a la URL proporcionada en `redirect_url`.
2. El usuario llega a la pantalla "Seleccionar Vendedor" del sistema nuevo.
3. El cliente puede:
   - Asignar un vendedor a la cotización.
   - Agregar productos adicionales (sin ver precios).
   - Ver el PDF del plano adjunto.
4. El vendedor asignado procesa la cotización y envía el presupuesto final.

---

## 📞 Soporte y Contacto

**Documentación Técnica**: Este archivo  
**Repositorio**: `proyectoMalkoni2`  
**Última Actualización**: 23 de junio de 2026  
**Versión de la API**: v1.0.0

---

## 📜 Historial de Cambios

| Versión | Fecha | Cambios |
|---------|-------|---------|
| 1.0.0 | 2026-06-23 | Especificación inicial de la API de importación de cotizaciones. |

---

**Fin de la Especificación Técnica**
