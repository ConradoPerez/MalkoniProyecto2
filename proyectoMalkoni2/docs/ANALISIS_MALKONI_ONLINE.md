# 📋 ANÁLISIS TÉCNICO — Sistema malkoni-online

**Fecha de análisis**: 22 de junio de 2026  
**Revisado con código fuente real**: 22 de junio de 2026  
**Analizado por**: GitHub Copilot  
**Propósito**: Documentar el sistema existente como base para implementar la integración con el nuevo sistema de cotizaciones.

---

## 1. RESUMEN GENERAL

`malkoni-online` es el sistema web actualmente desplegado en producción en **online.malkoni.com.ar** (cPanel).

### ¿Qué hace?
- Registro y autenticación de usuarios (personas físicas y empresas).
- Gestión de la relación persona ↔ empresa (incluye multi-empresa).
- Integración con el **Optimizador de Cortes (OPT)** de Lepton mediante iframe.
- Generación y uso de `token_OPT` para identificar usuarios dentro del OPT.
- Obtención de pedidos/cortes realizados por usuarios en el OPT (vía API de Lepton).
- Sincronización bidireccional con el sistema de facturación (SGA) vía API interna.
- Integración con el ecommerce **deherrajes.com** (login y creación de usuarios).

### Lo que NO hace
- Crear cotizaciones.
- Conectarse con el sistema de cotizaciones (proyectoMalkoni2).
- Gestionar vendedores o supervisores.

---

## 2. ARQUITECTURA ACTUAL

### Stack Tecnológico
- **Lenguaje**: PHP 8.x
- **ORM**: Doctrine (patrón Entity Manager)
- **Dependencias**: Composer, PHPMailer
- **Servidor**: cPanel
- **Base de datos**: MySQL (`malkoni_online`)
- **Frontend**: PHP con HTML/CSS vanilla + SweetAlert2 + Bootstrap (en algunas páginas)
- **Sin framework**: No usa Laravel, Symfony ni similar. Cada archivo PHP es un endpoint independiente.

### Estructura de Carpetas
```
malkoni-online/
├── public/
│   ├── login.php                  # Autenticación de usuarios (con redirectMap y soporte hardcodeado)
│   ├── logout.php                 # Cierre de sesión
│   ├── registro.php               # Registro multi-paso (Empresa + Persona) - CASO 2 y 3
│   ├── registro_cf.php            # Registro Consumidor Final (CASO 1)
│   ├── registro_cuit.php          # Primer paso: ingreso de CUIT para detectar caso
│   ├── tipo_identidad.php         # Inicio: elige entre empresa o persona física
│   ├── validar_usuario.php        # Verifica si una empresa ya tiene usuarios
│   ├── validar_usuario_cf.php     # Verifica si un Consumidor Final ya existe
│   ├── usuario_token.php          # Endpoint: valida token_OPT y retorna datos del usuario (para Lepton)
│   ├── crear_localidad.php        # Crea una localidad nueva en la BD
│   ├── SolicitarUsuario.php       # Solicitar usuario por email
│   ├── restablecer_contraseña.php # Restablecimiento de contraseña
│   ├── Dashboard/
│   │   ├── opt.php                # Dashboard principal: iframe del OPT + formulario de soporte
│   │   ├── navbar.php             # Navbar compartida configurable (include PHP)
│   │   ├── cotizar_mis_pedidos.php # Lista de pedidos desde API Lepton + botón Cotizar
│   │   ├── cambiar_empresa.php    # Cambio de empresa activa (solo rol=2)
│   │   ├── set_empresa_activa.php # Setter: cambia empresa activa en sesión + persiste en BD
│   │   ├── empresas_asociadas.php # Lista de empresas asociadas al usuario
│   │   ├── perfil.php             # Perfil del usuario
│   │   ├── usuarios.php           # Panel de usuarios de la empresa
│   │   └── Soporte/               # ⚠️ Panel exclusivo del usuario soporte (rol=3)
│   │       ├── dashboard_soporte.php          # Panel principal de soporte con búsqueda de empresas
│   │       ├── ajax_buscar_empresas.php        # AJAX: busca empresas por razón social/CUIT
│   │       ├── ajax_buscar_operarios.php       # AJAX: busca personas/operarios
│   │       ├── ajax_crear_persona.php          # AJAX: crea persona nueva
│   │       ├── ajax_editar_empresa.php         # AJAX: edita datos de empresa
│   │       ├── ajax_editar_persona.php         # AJAX: edita datos de persona
│   │       ├── ajax_eliminar_empresa.php       # AJAX: elimina empresa
│   │       ├── ajax_eliminar_persona.php       # AJAX: elimina persona
│   │       ├── ajax_asociar_persona_empresa.php # AJAX: vincula persona a empresa
│   │       └── ajax_persona_empresas.php       # AJAX: lista empresas de una persona
│   ├── apifact/
│   │   ├── api.php                # Helper HTTP para comunicarse con SGA (sga_http_post, syncClienteFacturacion)
│   │   ├── existecli.php          # Verifica si un cliente ya existe en SGA
│   │   ├── test_cliente.php       # Script de prueba para /clientes de SGA
│   │   ├── test_port.php          # Script de prueba de conectividad con SGA
│   │   └── test_token.php         # Script de prueba para obtener token de SGA
│   ├── endpoints/
│   │   ├── gestion_empresa.php    # Endpoint: crear/actualizar empresa desde SGA
│   │   ├── gestion_cf.php         # Endpoint: crear/actualizar CF desde SGA (incluye calcularCuil)
│   │   ├── act_fecha_ult.php      # Endpoint: actualizar fecha_ult_contacto desde SGA
│   │   └── eliminar_cliente.php   # Endpoint: dar de baja una empresa
│   └── ecommerce/
│       └── login.php              # API de login para ecommerce deherrajes.com (CORS habilitado)
├── src/
│   └── Entities/                  # Entidades Doctrine (Personas, Empresas, etc.)
├── config/
│   └── doctrine.php               # Configuración del Entity Manager
├── vendor/                        # Dependencias Composer
├── PHPMailer/                     # Librería de email
└── logs/
    └── sga_sync.log               # Logs de sincronización con SGA
```

---

## 3. BASE DE DATOS — Tablas Principales

### `Personas`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | int PK | ID autoincremental |
| `nombre` | varchar | Nombre |
| `apellido` | varchar | Apellido |
| `genero` | varchar | `'M'` o `'F'` |
| `dni` | varchar | DNI (hasta 8 dígitos) |
| `email` | varchar | Email (credencial de login) |
| `num_tel` | bigint | Teléfono |
| `pass` | varchar | Contraseña hasheada con `password_hash()` |
| `id_empresa` | int FK | Empresa **principal** (la de cuando se registró) |
| `rol` | int | `1` = admin, `2` = usuario común, `3` = soporte |
| `estado_persona` | int | `1` = activo, `2/3` = bloqueado, `4` = pendiente validación |
| `token_OPT` | varchar(20) | Token alfanumérico para autenticar en Lepton OPT |
| `reset_token` | varchar | Token para restablecimiento de contraseña |
| `validacion_token` | varchar | Token de validación de email |
| `empresa_activa_id` | int FK | Empresa seleccionada actualmente (puede cambiar) |

### `Empresas`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | int PK | ID autoincremental |
| `cod_cliente` | varchar | Código en sistema de facturación (SGA) |
| `razon_social` | varchar | Razón social / nombre |
| `cuit` | varchar | CUIT (11 dígitos sin guiones) |
| `dni` | varchar | DNI (para Consumidores Finales) |
| `observacion` | text | Notas internas |
| `CodCondIVA` | varchar | `CF`, `MT`, `RI`, `EX` |
| `num_tel` | varchar | Teléfono |
| `email` | varchar | Email de la empresa |
| `estado` | tinyint | `1`=activo, `2`=existente modificado, `3`=nueva online, `4`=creado por SGA |
| `fecha_inicial` | date | Fecha de creación |
| `fecha_alta` | date | Fecha de alta en sistema |
| `fecha_ult_contacto` | date | Última fecha de contacto |
| `validado` | tinyint | `1` = empresa validada por admin |
| `validacion_token` | varchar | Token para validación |
| `baja` | tinyint | `1` = empresa dada de baja |

### `empresas_personas` (pivot N:N)
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | int PK | |
| `empresa_id` | int FK | |
| `persona_id` | int FK | |
| `estado` | tinyint | `1` = activo |
| `fecha_alta` | datetime | |

### Otras tablas relevantes
- `Direcciones` — Domicilio de empresas (FK a `Empresas`)
- `Provincias`, `Localidades`, `Paises` — Datos geográficos

---

## 4. FLUJO COMPLETO DE AUTENTICACIÓN

### Mecanismo `redirectMap` (verificado en código)
`login.php` soporta un parámetro `?from=` que permite redirigir a un destino específico después del login:
```php
$redirectMap = [
    'opt'     => '/public/Dashboard/opt.php',
    'pedidos' => '/public/Dashboard/opt.php', // por ahora también va a OPT
    // 'cot'  => '/public/Dashboard/cotizaciones.php', // cuando exista
];
```
El parámetro `from` se puede pasar por GET o POST y queda guardado en `$_SESSION['post_login_redirect']`.

```
Usuario accede a login.php [?from=opt]
        ↓
¿Tiene sesión activa? → Sí → Redirige a $redirectMap[from] o /public/Dashboard/opt.php
        ↓ No
[GET] Guarda destino en $_SESSION['post_login_redirect']
        ↓
POST: email + password
        ↓
── SOPORTE HARDCODEADO ─────────────────────────────────────────────────
  email = soporte@online.malkoni.com.ar (y clave hardcodeada)
  → Login directo, $_SESSION['rol'] = 3
  → Redirige a Dashboard/Soporte/dashboard_soporte.php
── FIN SOPORTE ──────────────────────────────────────────────────────────
        ↓
Busca Persona por email en BD
        ↓
¿Encontró?
  No → Error "No encontramos cuenta asociada a ese correo"
  Sí ↓
password_verify(pass, hash)
  No → Error "La contraseña ingresada es incorrecta"
  Sí ↓
Verificar estado_persona:
  = 4 → "Pendiente de validación"
  = 2|3 → "Cuenta inactiva"
             ↳ Busca admin (rol=1) de la misma empresa para mostrar contacto
  Empresa principal no validada → "Empresa no validada"
  Cualquier otro → ✅ LOGIN EXITOSO
        ↓
Setear variables de sesión:
  $_SESSION['usuario']  = email
  $_SESSION['id']       = id_persona
  $_SESSION['nombre']   = nombre
  $_SESSION['apellido'] = apellido
  $_SESSION['rol']      = rol
  $_SESSION['empresa_id'] = empresa_activa (verificada)
        ↓
Redirigir a: $_SESSION['post_login_redirect'] ?? '/public/Dashboard/opt.php'
```

### Lógica de empresa activa en login (verificada en código)
1. Solo aplica para `rol = 2` (usuario común).
2. Intenta cargar `empresa_activa_id` de la BD via `$persona->getEmpresaActiva()`.
3. Verifica que la empresa activa esté validada (`isValidado() === true`).
4. Verifica que la persona esté asociada: primero compara con `id_empresa` (empresa principal), luego busca en `empresas_personas` con `estado = 1`.
5. Si no es válida o no está asociada → usa `$persona->getEmpresa()->getId()` como fallback.
6. Guarda el ID resultante en `$_SESSION['empresa_id']`.

---

## 5. FLUJO DE CREACIÓN DE USUARIOS

### Punto de entrada: `tipo_identidad.php`
El usuario elige entre:
- **Empresa** → `registro_cuit.php`
- **Persona Física (Consumidor Final)** → `registro_cf.php`

---

### CASO 1: Consumidor Final (`registro_cf.php`)

**Proceso (2 pasos):**

**Paso 1 — Datos personales:**
- Apellido, Nombre, Género, DNI, Teléfono, Email personal, Contraseña.
- Validaciones: DNI numérico 7-8 dígitos, email válido, contraseña fuerte.

**Paso 2 — Dirección:**
- País, Provincia, Localidad (obligatoria si es Argentina), Código Postal.

**Al confirmar:**
1. Calcula CUIL desde DNI + Género (`calcularCuil()`).
2. Crea `Empresa` con:
   - `CodCondIVA = 'CF'`
   - `cuit` = CUIL calculado
   - `estado = 3` (nueva online)
   - `validado = false`
3. Crea `Persona` con `rol = 2`, vinculada a esa empresa.
4. Genera `token_OPT` (20 caracteres alfanuméricos).
5. Envía email de validación.
6. Llama a `syncClienteFacturacion()` → registra en SGA.

---

### CASO 2: Usuario se une a empresa existente (`registro_cuit.php` → `registro.php`)

1. Ingresa CUIT → sistema busca empresa existente.
2. Si existe: muestra cartelito "CUIT ya registrado" y precarga datos.
3. El usuario completa solo datos personales.
4. Se crea `Persona` con `rol = 2` asociada a la empresa existente.

---

### CASO 3: Usuario crea empresa nueva (`registro_cuit.php` → `registro.php`)

**Proceso (3 pasos):**

**Paso 1 — Datos de empresa:**
- CUIT, Razón Social, Condición de IVA, Email empresa, Teléfono.
- Valida: CUIT formato argentino, sin duplicados por CUIT ni email.

**Paso 2 — Dirección de empresa:**
- País, Provincia, Localidad, CP, Barrio, Domicilio.

**Paso 3 — Datos del usuario (dueño):**
- Apellido, Nombre, Género, DNI, Teléfono, Email personal, Contraseña.

**Al confirmar:**
1. Crea `Empresa` nueva con `estado = 3`.
2. Crea `Direccion` asociada.
3. Crea `Persona` administradora con `rol = 1` y email = email empresa. Contraseña autogenerada + enviada por email.
4. Crea `Persona` usuario común con `rol = 2` y email = email personal. Contraseña elegida por el usuario.
5. Genera `token_OPT` para el usuario común.
6. Vincula ambas personas a la empresa.
7. Llama a `syncClienteFacturacion()` → registra en SGA.
8. Envía emails de bienvenida y contraseña.

---

## 6. RELACIÓN PERSONAS ↔ EMPRESAS

### Modelo completo

```
Persona (id, email, pass, rol, estado_persona, id_empresa, empresa_activa_id, token_OPT, ...)
  │
  ├── id_empresa ──────────────────── Empresa (empresa PRINCIPAL de registro)
  │
  └── empresa_activa_id ───────────── Empresa (empresa ACTUALMENTE seleccionada, puede cambiar)
  
Empresa ←──── empresas_personas ────→ Persona
               (tabla pivot N:N)
               empresa_id, persona_id, estado
```

### Reglas importantes
- `id_empresa` es la empresa del momento del registro. **No cambia** después de creado.
- `empresa_activa_id` es la empresa que el usuario tiene seleccionada actualmente. **Sí puede cambiar** (solo `rol = 2`).
- `empresas_personas` registra TODAS las empresas a las que una persona puede pertenecer.
- **Solo usuarios con `rol = 2` pueden cambiar de empresa activa.**
- **Consumidores Finales** (`CodCondIVA = 'CF'`) solo pertenecen a su empresa CF, no pueden tener multi-empresa.
- **Empresas reales** (`MT`, `RI`, `EX`) pueden tener múltiples usuarios.

---

## 7. MANEJO DE ROLES

| Rol | Valor | Descripción | Puede cambiar empresa |
|-----|-------|-------------|----------------------|
| Admin | `1` | Administrador de empresa. Email = email empresa. Contraseña autogenerada. | No |
| Usuario | `2` | Usuario operativo. Email personal. Contraseña propia. | **Sí** |
| Soporte | `3` | Usuario hardcodeado. Acceso al panel de soporte. | No aplica |

**Nota**: El rol `3` está hardcodeado en `login.php` (email y contraseña fija). No existe en BD.

---

## 8. FUNCIONAMIENTO DEL OPT (Optimizador de Cortes)

### ¿Qué es?
El OPT es un sistema externo desarrollado por **Lepton** (`optimizadoronline.com`). Malkoni tiene una cuenta empresarial (`empresa/malkoni`) en ese sistema.

### ¿Cómo accede el usuario?
```
Usuario autenticado → opt.php
        ↓
Carga token_OPT del usuario desde Personas.token_OPT
        ↓
Construye URL:
  https://www.optimizadoronline.com/empresa/malkoni/opti?access_token={token_OPT}
        ↓
Renderiza IFRAME con esa URL
        ↓
Lepton recibe el access_token, valida el usuario
y muestra su interfaz personalizada dentro del iframe
```

### ¿Cómo valida Lepton al usuario? (verificado en código)
Lepton llama a nuestro endpoint `usuario_token.php?access_token={token}`:
- Buscamos la `Persona` con ese `tokenOpt` (atributo Doctrine: `tokenOpt`).
- Construimos la dirección desde `$empresa->getDirecciones()->toArray()[0]->getDomicilio()`.
- Respondemos JSON con campos fijos:
  ```json
  {
    "email": "...",
    "name": "...",
    "lastname": "...",
    "idCountry": 1,
    "idRegion": 16,
    "address": "...",
    "telephone": "..."
  }
  ```
- `idCountry = 1` y `idRegion = 16` son **valores fijos hardcodeados** (Argentina, Córdoba).
- ⚠️ **No cambiar la estructura de respuesta** sin coordinar con Lepton.

### ¿Qué datos quedan guardados cuando un usuario hace cortes?
- Los cortes y pedidos quedan almacenados en la infraestructura de **Lepton** (no en nuestra BD).
- Los PDFs de los planos se almacenan en **S3 de Amazon** con la URL: `https://optionline-prod-files.s3.amazonaws.com/planos/{id_pedido}_.pdf`
- Solo guardamos el `token_OPT` en nuestra BD para identificar al usuario.

---

## 9. CÓMO SE GENERAN Y USAN LOS TOKENS

### `token_OPT`
**Generación** (función `generarTokenOPT($length = 20)` — usada en los archivos de registro):
```php
function generarTokenOPT(int $length = 20): string {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_';
    $out = '';
    for ($i = 0; $i < $length; $i++) $out .= $chars[random_int(0, strlen($chars) - 1)];
    return $out;
}
```
- 20 caracteres alfanuméricos + guión bajo.
- Se genera al crear el usuario (registro).
- Se almacena en `Personas.token_OPT`.
- **No tiene expiración** (es persistente).

**Uso:**
1. Para cargar el iframe del OPT: `?access_token={token_OPT}` en la URL de Lepton.
2. Lepton valida el token llamando a `usuario_token.php?access_token={token}`.
3. Se usa para consultar pedidos: `GET /empresa/malkoni/proyectos?access_token={token}`.

### `validacion_token` (Personas y Empresas)
- Token para validar cuenta por email.
- Se usa en `validar_usuario_mail.php`.

### `reset_token` (Personas)
- Token para restablecer contraseña.
- Se genera en `restablecer_contraseña.php`.

---

## 10. FLUJO: COTIZAR MIS PEDIDOS (`cotizar_mis_pedidos.php`)

### Estado actual
El flujo está **parcialmente implementado**. El botón "Cotizar" muestra un mensaje de "en desarrollo".

### Lo que ya funciona (verificado en código)
1. Verifica sesión activa. Si no hay sesión → redirige a `login.php` (relativo).
2. Carga Persona desde `$_SESSION['id']` (con fallback por email).
3. Verifica que `token_OPT` no esté vacío (si lo está → HTTP 500).
4. Carga empresa activa desde `$_SESSION['empresa_id']`.
5. Consulta API de Lepton con cURL:
   ```
   GET https://www.optimizadoronline.com/empresa/malkoni/proyectos?access_token={tokenOpt}
   Timeout: 15 segundos
   ```
6. Filtra respuesta: solo pedidos con `cant_placas > 0` y `id > 0`.
7. Convierte `createdDate` (timestamp en milisegundos) a formato `d/m/Y H:i` con zona horaria `America/Argentina/Buenos_Aires`.
8. Construye URL del PDF: `https://optionline-prod-files.s3.amazonaws.com/planos/{id}_.pdf`.
9. Botón **"Plano"** → abre PDF en nueva pestaña.
10. Botón **"Cotizar"** → muestra SweetAlert con mensaje:
    > *"Cotización online en desarrollo — Muy pronto vas a poder generar la cotización directamente desde este panel"*
    > Muestra el número del pedido seleccionado (`data-pedido-id`).
11. Buscador en tiempo real por ID, proyecto o material (filtrado JS del lado cliente).

### Estructura de datos de un pedido (API Lepton — verificada)
```json
{
  "id": 4926740,
  "createdDate": 1751270895000,
  "project": "Proyecto A",
  "mat_descri": "Melamina Blanco 18mm",
  "cant_placas": 4
}
```

> **Nota**: La navbar en esta pantalla tiene `$navbarShowCotizarBtn = false`. El botón "Generar Cotización" no aparece en la navbar del listado de pedidos (evita loop).

### Lo que falta implementar
Al hacer clic en "Cotizar", reemplazar el SweetAlert por:
1. Leer `data-pedido-id` del botón clickeado.
2. Preparar payload con persona, empresa activa, token_OPT, pedido_id, pdf_url, material, proyecto, cant_placas.
3. Hacer POST al endpoint del sistema de cotizaciones.
4. Redirigir al usuario a la URL devuelta.

---

## 11. CÓMO FUNCIONAN LAS APIs ACTUALES

### API hacia SGA (Sistema de Facturación)
**Archivo**: `public/apifact/api.php`

**URL base**: `http://malkonihnos.ddns.net:9000/sga/rest/tep`

**Autenticación**: Token dinámico. Primero se hace un `POST /connect` con credenciales de terminal para obtener un token temporal. Luego se usa ese token en cada llamada.

**Funciones exportadas**:
- `sga_http_post(string $path, array $payload): array` — Función base de comunicación HTTP
- `sga_obtener_token(): string` — Obtiene token dinámico via `POST /connect`
- `syncClienteFacturacion(array $clienteData, ?array &$debug = null): ?string` — Inserta/actualiza cliente y retorna `codcli`

**Endpoints usados**:
```
POST /connect  → Obtiene token temporal
POST /clientes → Crea o actualiza cliente en SGA
```

**Protocolo**:
1. `sga_obtener_token()` → `POST /connect` con `{cred: {terminal: "100", code: "063f..."}}`
2. `syncClienteFacturacion($data)` → `POST /clientes` con `{cred: {terminal, token}, data: {...}}`

**Normalización por tipo de cliente (verificada)**:
- `pfj = 1` (empresa): envía `rsocial`, CUIT. Descarta `apellido`, `nombre`, `genero`.
- `pfj = 2` (persona física CF): envía `apellido`, `nombre`, `genero`, `dni`. Descarta `rsocial` y CUIT vacío.

**Payload para empresa**:
```json
{
  "cred": {"terminal": "100", "token": "..."},
  "data": {
    "codcli": "",
    "pfj": 1,
    "rsocial": "EMPRESA S.A.",
    "cuit": "30123456789",
    "email": "...",
    "telefono": "...",
    "domicilio": "..."
  }
}
```

**Comunicación forzada**: usa `CURLOPT_INTERFACE = '50.31.177.150'` (IP de origen fija requerida por SGA). Sin esta IP las llamadas son rechazadas.

**Manejo de errores**: SGA puede responder HTTP 200 con `body.data.error` no vacío. La función lanza `RuntimeException` en ese caso. El `$debug` por referencia devuelve la respuesta completa para logs.

---

### Endpoints que recibe (desde SGA hacia malkoni-online)
**Carpeta**: `public/endpoints/`

Todos usan el mismo patrón:
1. Reciben `?param=` como Base64 URL-encoded de un JSON (`rawurldecode` + `base64_decode`).
2. Parsean con `json_decode(..., JSON_THROW_ON_ERROR)` — lanzan error 400 si el JSON es inválido.
3. Realizan operaciones en BD con Doctrine dentro de **transacción** (`beginTransaction` / `commit`).
4. Devuelven JSON de respuesta con `Content-Type: application/json`.

| Archivo | Función | Campos obligatorios | Disparado por |
|---------|---------|---------------------|---------------|
| `gestion_empresa.php` | Crea/actualiza empresa por `cod_cli` | `cod_cli`, `razon_social`, `CodCondIVA`, `cuit` (11 dígitos) | SGA cuando alta/mod cliente empresa |
| `gestion_cf.php` | Crea/actualiza CF por `cod_cli` (incluye `calcularCuil()`) | `cod_cli`, `nombre`, `apellido`, `dni`, `genero` | SGA cuando alta/mod cliente CF |
| `act_fecha_ult.php` | Actualiza `fecha_ult_contacto` | `cod_cli` o `cuit` | SGA cuando último contacto con cliente |
| `eliminar_cliente.php` | Baja empresa (`baja = 1`) | `cod_cli` | SGA cuando baja cliente |

**Función `calcularCuil(string $dni, string $genero): string`** (definida en `gestion_cf.php`):
```php
// Prefijo: 20 = masculino, 27 = femenino
// Si dígito verificador = 10 → prefijo cambia a 23/24 y dígito = 9
// Si dígito verificador = 11 → dígito = 0
```
Esta misma función también la usan `registro_cf.php` y `validar_usuario_cf.php`.

**Upsert por `cod_cliente`**: `gestion_empresa.php` busca la empresa con `findOneBy(['cod_cliente' => $cod_cli])`. Si no existe crea una nueva. El `estado` se setea siempre a `4` (creado/sincronizado por SGA).

---

### API para ecommerce (`public/ecommerce/login.php`)
- Recibe `?param=` Base64 URL-encoded con `{data: {email, password}}`.
- Valida credenciales igual que `login.php` (misma lógica: `findOneBy(['email'])` + `password_verify`).
- Devuelve JSON con datos del usuario y empresa.
- **CORS habilitado** explícitamente para:
  - `https://deherrajes.com`
  - `https://www.deherrajes.com`
  - `http://localhost:5173` (dev)
- Responde a `OPTIONS` con HTTP 204 (preflight).
- Usa `JSON_THROW_ON_ERROR` para parsear el payload.

### Panel de Soporte (`public/Dashboard/Soporte/`)
Accesible ÚNICAMENTE con `$_SESSION['rol'] == 3` (usuario soporte hardcodeado en login).

- `dashboard_soporte.php`: Panel con búsqueda de empresas paginada (20 por página). Permite buscar por razón social, CUIT, email o teléfono. Tiene QueryBuilder con `leftJoin` hacia `EmpresasPersonas` y `Personas`.
- Los demás archivos son **endpoints AJAX** (llamados con JS desde el dashboard): crean, editan, eliminan personas y empresas, y gestionan la relación `empresas_personas`.
- Estos AJAX endpoints **tampoco tienen autenticación de API**; confían en que solo el Soporte accede al panel.

---

## 12. PATRONES DE INTEGRACIÓN EXISTENTES

### Patrón general de los endpoints (verificado en código)
```
Caller → GET /endpoint.php?param=rawurlencode(base64(JSON))
Endpoint:
  1. rawurldecode($param)         ← importante: puede venir URL-encoded
  2. base64_decode($param)        ← estricto: false si no es Base64 válido
  3. json_decode($json, true, 512, JSON_THROW_ON_ERROR)  ← lanza JsonException
  4. Verifica existencia de campo "data" en el JSON
  5. Extrae campos de $data['data']
  6. Valida campos obligatorios (cod_cli, razon_social, etc.)
  7. $em->getConnection()->beginTransaction()
  8. Operación BD con Doctrine (upsert por cod_cliente/cuit)
  9. $em->flush() + commit
  10. Responde JSON {status: "ok", empresa_id: ..., direccion_id: ...}
  11. En catch → rollback + HTTP 500 + JSON {error: ...}
```

### Seguridad de los endpoints
⚠️ **IMPORTANTE**: Los endpoints de `/endpoints/` **no tienen autenticación explícita**.

Esto funciona porque:
- El SGA usa una IP fija de origen (`50.31.177.150`).
- Los endpoints no están documentados públicamente.
- Están detrás de cPanel en producción.

Para la nueva integración con el sistema de cotizaciones **SÍ debemos agregar autenticación** (Bearer token o HMAC signature).

### Navbar configurable (`navbar.php`)
`navbar.php` es un archivo include que lee variables PHP opcionales antes del `require`:
```php
$navbarTitle = $navbarTitle ?? 'SERVICIOS ONLINE';
$navbarShowCotizarBtn = $navbarShowCotizarBtn ?? false;  // muestra botón "Generar Cotización"
$navbarCotizarHref = $navbarCotizarHref ?? 'cotizar_mis_pedidos.php';
$navbarContext = '';  // 'opt' → usa altura navbar-opt (10vh para el iframe)
```
- En `opt.php`: `$navbarShowCotizarBtn = true` → muestra el botón "GENERAR COTIZACIÓN".
- En `cotizar_mis_pedidos.php`: `$navbarShowCotizarBtn = false` → oculta el botón (evita loop).
- El navbar distingue Consumidor Final (`CF`) con clase CSS especial.

---

## 13. INFORMACIÓN REUTILIZABLE PARA EL NUEVO SISTEMA

### ✅ Datos que podemos obtener de malkoni-online

Dado que `usuario_token.php` ya existe como endpoint de consulta, el nuevo sistema puede extenderse para exponer más datos:

#### De `Personas`
- `id` → ID único del sistema externo (usar como `external_id`)
- `nombre`, `apellido`
- `email`
- `num_tel`
- `dni`
- `genero`
- `rol`
- `estado_persona`
- `token_OPT` → para identificar en contexto OPT
- `empresa_activa_id` → **CRÍTICO** para asociar cotización a empresa correcta
- `id_empresa` → empresa principal

#### De `Empresas`
- `id` → ID único (usar como `external_id`)
- `razon_social`
- `cuit`
- `CodCondIVA` → distingue CF vs empresa real
- `email`
- `num_tel`
- `estado`, `validado`, `baja`

#### Del contexto de cotización (a obtener desde OPT al cotizar)
- `persona_id` → quién genera la cotización
- `empresa_activa_id` → para qué empresa
- `token_OPT` → para identificar en Lepton
- `pedido_id` → ID del pedido en Lepton
- `pdf_url` → `https://optionline-prod-files.s3.amazonaws.com/planos/{id}_.pdf`
- `material`, `proyecto`, `cant_placas` → datos del pedido

### ⚠️ Campo `empresa_activa_id` — CRÍTICO
Este campo **no existe actualmente en el sistema de cotizaciones** y es fundamental. Al momento de generar una cotización desde OPT:
- Una persona puede pertenecer a múltiples empresas.
- La cotización debe quedar asociada a la empresa que el usuario tiene **activa en ese momento**.
- `empresa_activa_id` es el campo que lo indica.
- No usar `id_empresa` para esto (es solo la empresa inicial).

---

## 14. GAPS Y CONSIDERACIONES PARA LA INTEGRACIÓN

### Diferencias de IDs
| Concepto | Sistema Online | Sistema Cotizaciones |
|----------|---------------|----------------------|
| PK Persona | `Personas.id` (autoincrement) | `personas.id_persona` (autoincrement) |
| PK Empresa | `Empresas.id` (autoincrement) | `empresas.id_empresa` (autoincrement) |

Los IDs son **independientes**. Para vincularlos se necesita agregar un campo `external_id` en las tablas del sistema de cotizaciones.

### Diferencia de estructuras
| Campo | Sistema Online | Sistema Cotizaciones |
|-------|---------------|----------------------|
| `nombre` | ✅ Personas | ❌ No existe |
| `apellido` | ✅ Personas | ❌ No existe |
| `email` | ✅ Personas | ❌ No existe |
| `CodCondIVA` | ✅ Empresas | ❌ No existe |
| `empresa_activa_id` | ✅ Personas | ❌ No existe |
| `multi-empresa (N:N)` | ✅ empresas_personas | ❌ No implementado |

### Autenticación pendiente
- En malkoni-online la sesión se maneja con `$_SESSION`.
- En proyectoMalkoni2 actualmente no hay autenticación real.
- La integración debería usar el `token_OPT` o un token de API dedicado para comunicar ambos sistemas de forma segura.

---

## 15. FLUJO FUTURO: OPT → COTIZACIÓN

```
[malkoni-online] Usuario en opt.php
        ↓
Hace click en "Generar Cotización" (navbar)
        ↓
Redirige a cotizar_mis_pedidos.php
        ↓
Lista pedidos de Lepton API (con token_OPT)
        ↓
Usuario selecciona pedido + clic "Cotizar"
        ↓
[malkoni-online] Prepara payload:
  {
    persona_id: $_SESSION['id'],
    empresa_id: $_SESSION['empresa_id'],  // ← empresa_activa_id
    token_opt: $persona->getTokenOpt(),
    pedido_id: {id del pedido},
    pdf_url: "https://s3.amazonaws.com/planos/{id}_.pdf",
    material: "Melamina Blanco 18mm",
    cant_placas: 4
  }
        ↓
POST a sistema de cotizaciones:
  POST https://cotizaciones.malkoni.com.ar/api/v1/cotizacion/crear-desde-opt
  Authorization: Bearer {API_SHARED_TOKEN}
        ↓
[proyectoMalkoni2] Recibe request
  1. Valida API token
  2. Sincroniza persona (crea/actualiza por external_id)
  3. Sincroniza empresa (crea/actualiza por external_id)
  4. Crea cotización con items de madera desde OPT
  5. Retorna {cotizacion_id, redirect_url}
        ↓
[malkoni-online] Redirige al usuario a:
  https://cotizaciones.malkoni.com.ar/cliente/cotizacion/{id}/ver?token={one_time_token}
        ↓
[proyectoMalkoni2] Muestra cotización al cliente
(puede agregar herrajes, ver estado, etc.)
```

---

*Este documento fue generado en base al análisis del código fuente de `malkoni-online`. Para preguntas o aclaraciones, consultar al equipo de desarrollo.*
