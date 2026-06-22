# 🤖 Instrucciones para Copilot — Sistema malkoni-online

## LECTURA OBLIGATORIA antes de cualquier modificación en esta carpeta

---

## 1. CONTEXTO DEL NEGOCIO

**Empresa**: Malkoni Hnos. — Comercialización de maderas y herrajes.

**Este sistema** (`malkoni-online`) es el sistema de registro, autenticación y acceso al Optimizador de Cortes (OPT). Está desplegado en **online.malkoni.com.ar** y es usado por los **clientes** de Malkoni.

**El sistema hermano** (`proyectoMalkoni2`) es el sistema de cotizaciones en desarrollo, que eventualmente recibirá pedidos desde este sistema.

---

## 2. ARQUITECTURA: LO QUE HAY QUE ENTENDER

### NO es un framework moderno
Este sistema **no usa Laravel, Symfony, ni ningún framework PHP moderno**.

Cada archivo `.php` dentro de `/public` es un endpoint independiente. No hay:
- Controladores en el sentido MVC.
- Rutas definidas en un archivo central.
- Middleware de autenticación automatizado.

La autenticación se controla **manualmente** al principio de cada archivo:
```php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../login.php');
    exit;
}
```

### Sí usa Doctrine ORM
Todas las operaciones de base de datos se hacen a través de **Doctrine Entity Manager** (`$entityManager`), que se instancia en cada archivo:
```php
require_once __DIR__ . '/../vendor/autoload.php';
$entityManager = require __DIR__ . '/../config/doctrine.php';
```

Las entidades están en `src/Entities/`.

---

## 3. ENTIDADES PRINCIPALES Y SUS REGLAS

### `Personas` (usuarios del sistema)
- `id` → PK autoincremental.
- `email` → Credencial de login. **Único**.
- `pass` → Hash con `password_hash()`. Verificar con `password_verify()`.
- `rol` → `1` = admin, `2` = usuario común. **NO confundir con roles del sistema de cotizaciones**.
- `estado_persona` → `1` activo, `2/3` bloqueado, `4` pendiente validación.
- `id_empresa` → Empresa del momento del **registro**. **No cambia**. No representa la empresa activa.
- `empresa_activa_id` → Empresa **actualmente seleccionada**. **Sí cambia** (solo rol=2). **CAMPO CRÍTICO** para cotizaciones.
- `token_OPT` → Token de 20 chars para identificar al usuario en Lepton. Generado al crear usuario. **No tiene expiración**.
  - En Doctrine el atributo se llama `tokenOpt` (camelCase): `$persona->getTokenOpt()`.
  - En la BD la columna es `token_OPT`.
  - Al consultar en BD por este campo usar: `findOneBy(['tokenOpt' => $token])`.

### `Empresas` (clientes corporativos y personas físicas)
- `id` → PK autoincremental.
- `CodCondIVA` → **CAMPO CRÍTICO**. Determina tipo de cliente:
  - `CF` = Consumidor Final (persona física). Su CUIT es un CUIL calculado.
  - `MT` = Monotributo
  - `RI` = Responsable Inscripto
  - `EX` = Exento
- `cuit` → 11 dígitos sin guiones. Para CF es el CUIL calculado desde DNI + género.
- `estado` → `1` activo, `2` modificado online, `3` creado online, `4` creado por SGA.
- `validado` → `true/false`. Si es `false`, la empresa no puede loguearse.
- `baja` → `true/false`. Si es `true`, empresa dada de baja.

### `EmpresasPersonas` (tabla pivot N:N)
- Relación muchos a muchos entre `Personas` y `Empresas`.
- `estado = 1` significa vinculación activa.
- Solo aplica para empresas con `CodCondIVA != 'CF'`.
- Las personas físicas CF solo pertenecen a su empresa CF.

---

## 4. REGLAS DE NEGOCIO CRÍTICAS

### Sobre `id_empresa` vs `empresa_activa_id`
```
❌ NUNCA usar Personas.id_empresa para saber la empresa actual del usuario.
✅ SIEMPRE usar Personas.empresa_activa_id para la empresa de trabajo actual.
```

Esto es especialmente importante para cotizaciones: al cotizar desde OPT, la cotización debe asociarse a `empresa_activa_id`, NO a `id_empresa`.

### Sobre el `token_OPT`
- Es el identificador del usuario dentro del sistema de Lepton.
- Se usa en la URL del iframe: `?access_token={token_OPT}`.
- Lepton valida el token llamando a `usuario_token.php`.
- **No modificar el token_OPT** de un usuario activo sin coordinar con Lepton.
- Se genera con `generarTokenOPT()` al registrar el usuario.

### Sobre el login
- El login tiene un `$redirectMap` que mapea `?from=` → destino después del login.
- El destino se guarda en `$_SESSION['post_login_redirect']` y se respeta al autenticar.
- El usuario soporte (`rol=3`) está **hardcodeado** en `login.php`. No existe en BD. No buscarlo en `Personas`.
- El soporte redirige exclusivamente a `Dashboard/Soporte/dashboard_soporte.php`.
- Los usuarios normales siempre redirigen a OPT por defecto.
  - Accede al panel `Dashboard/Soporte/dashboard_soporte.php`.
  - Ese panel verifica `$_SESSION['rol'] == 3` al inicio.
  - No confundir con los roles de `proyectoMalkoni2` (supervisor/vendedor/cliente).

### Sobre el estado de empresas en registro
- Empresa existente modificada online → `estado = 2`
- Empresa nueva creada online → `estado = 3`
- Empresa creada/sincronizada por SGA → `estado = 4`

### Sobre el CUIL de Consumidores Finales
- Se calcula con `calcularCuil(dni, genero)`.
- Prefijo `20` para masculino, `27` para femenino (con excepciones en dígito verificador).
- El CUIL calculado se guarda como `cuit` en la empresa CF.

---

## 5. FLUJOS QUE NO SE DEBEN ROMPER

### Login
El login verifica en orden:
1. ¿Es el usuario soporte hardcodeado? → Login directo sin tocar BD.
2. ¿Existe la persona por email?
3. ¿La contraseña es correcta?
4. ¿El estado_persona permite acceso? (`1` = ok, otros = bloqueado/pendiente)
5. ¿La empresa principal está validada?
6. ¿Cuál es la empresa activa válida? (solo rol=2; fallback a empresa principal)
7. Guarda destino en sesión y redirige.

**No modificar este orden** sin revisar todos los estados posibles.

### Registro (3 casos)
Los 3 flujos de registro (`registro.php`, `registro_cf.php`, `registro_cuit.php`) están coordinados y **cada uno al finalizar llama a `syncClienteFacturacion()`** para sincronizar con SGA. Si se modifica el registro, verificar que la sincronización siga funcionando.

### Token OPT en `/public/usuario_token.php`
Este endpoint es llamado por el sistema externo de Lepton. Su respuesta tiene un formato específico que Lepton espera:
```json
{"email": "", "name": "", "lastname": "", "idCountry": 1, "idRegion": 16, "address": "", "telephone": ""}
```
- `idCountry = 1` y `idRegion = 16` son **valores fijos hardcodeados**. No cambiarlos.
- La dirección se obtiene de `$empresa->getDirecciones()->toArray()[0]->getDomicilio()`.
- El atributo Doctrine es `tokenOpt` (no `token_OPT`): `findOneBy(['tokenOpt' => $token])`.
- **No cambiar la estructura de la respuesta** sin coordinar con Lepton.

---

## 6. APIs EXTERNAS — REGLAS DE INTEGRACIÓN

### API de Lepton (OPT)
- **Base URL**: `https://www.optimizadoronline.com`
- **Empresa**: `malkoni`
- Los pedidos se consultan con: `GET /empresa/malkoni/proyectos?access_token={token_OPT}` (cURL, timeout 15s)
- Los PDFs están en: `https://optionline-prod-files.s3.amazonaws.com/planos/{id_pedido}_.pdf`
- Lepton valida usuarios en: `GET /public/usuario_token.php?access_token={token}`
- La respuesta de pedidos incluye `createdDate` en **milisegundos** (dividir por 1000 para obtener timestamp Unix).
- Solo procesar pedidos con `cant_placas > 0`.

### API de SGA (Facturación)
- **Base URL**: `http://malkonihnos.ddns.net:9000/sga/rest/tep`
- **IP de origen obligatoria**: `50.31.177.150` (configurada con `CURLOPT_INTERFACE`)
- **Sin esta IP**, las llamadas son rechazadas.
- **Flujo**: primero obtener token con `POST /connect`, luego usar el token en `POST /clientes`.
- Toda la lógica está en `public/apifact/api.php`. No duplicar este código.

### Endpoints de SGA hacia malkoni-online (`public/endpoints/`)
- No tienen autenticación propia.
- Están protegidos solo por no ser públicamente conocidos.
- **No agregar autenticación** sin coordinar con el equipo que mantiene SGA.
- El patrón de todos ellos es: `?param=rawurlencode(base64(JSON))` → operación en BD → respuesta JSON.
- Todos usan `json_decode(..., JSON_THROW_ON_ERROR)` y transacciones Doctrine.
- `gestion_cf.php` define `calcularCuil(string $dni, string $genero): string` — la misma lógica de cálculo que usa el registro.

---

## 7. QUÉ NO MODIFICAR SIN ANALIZAR

| Elemento | Riesgo si se modifica |
|----------|----------------------|
| `usuario_token.php` | Rompe integración con Lepton OPT (formato fijo esperado) |
| `public/apifact/api.php` | Rompe sincronización con SGA |
| `public/endpoints/*.php` | Rompe sincronización bidireccional con SGA |
| `public/ecommerce/login.php` | Rompe login de `deherrajes.com` |
| Entidades Doctrine en `src/Entities/` | Puede romper toda la persistencia |
| Función `generarTokenOPT()` | Cambiar el formato puede romper tokens existentes de Lepton |
| Función `calcularCuil()` en `gestion_cf.php` | Rompe CUIL de CFs existentes y nuevos |
| Campo `empresa_activa_id` | Es CRÍTICO para el futuro flujo de cotizaciones |
| Campo `token_OPT` / atributo Doctrine `tokenOpt` | Identificador de usuario en Lepton. Persistente. |
| `idCountry = 1` e `idRegion = 16` en `usuario_token.php` | Hardcodeados. Cambiarlos rompe el contrato con Lepton. |
| `CURLOPT_INTERFACE = '50.31.177.150'` en `api.php` | Sin esta IP las llamadas a SGA son rechazadas. |
| Credenciales SMTP en `opt.php` | Rompe el formulario de soporte. |

---

## 8. CÓMO ESTÁN RELACIONADOS LOS DATOS

### Persona → Empresa activa (al cotizar desde OPT)
```php
// ✅ Empresa activa correcta
$empresaActiva = $persona->getEmpresaActiva(); // empresa_activa_id
$empresaId     = $_SESSION['empresa_id'];       // persistido en sesión

// ❌ Empresa incorrecta para cotizaciones
$empresaPrincipal = $persona->getEmpresa();    // id_empresa (solo del registro)
```

### Persona → Todas sus empresas
```php
// Empresa principal (registro)
$empresaPrincipal = $persona->getEmpresa();

// Todas las empresas (incluyendo multi-empresa)
$empresasPersonas = $em->getRepository(EmpresasPersonas::class)
    ->findBy(['persona' => $persona, 'estado' => 1]);
```

### Pedidos de OPT → PDF
```php
$pdfUrl = 'https://optionline-prod-files.s3.amazonaws.com/planos/' . $pedidoId . '_.pdf';
```

---

## 9. INTEGRACIÓN PENDIENTE CON SISTEMA DE COTIZACIONES

### El archivo clave
`public/Dashboard/cotizar_mis_pedidos.php`

### Estado real del botón Cotizar (verificado en código)
```javascript
// El botón tiene:
dataset.pedidoId = '{id del pedido}'  // ya disponible en el DOM

// Actualmente solo dispara un SweetAlert:
Swal.fire({ title: 'Cotización online en desarrollo', ... })
```

### Lo que tiene que pasar al implementar
Reemplazar el SweetAlert por:
```javascript
const id = btn.getAttribute('data-pedido-id');
// 1. Preparar payload
const payload = {
  persona_id: SESSION_ID,           // ya viene del servidor via PHP
  empresa_id: SESSION_EMPRESA_ID,   // empresa_activa_id
  token_opt: TOKEN_OPT,             // ya viene del servidor via PHP
  pedido_id: parseInt(id),
  pdf_url: `https://optionline-prod-files.s3.amazonaws.com/planos/${id}_.pdf`,
  // material, proyecto, cant_placas: sacar de la misma fila de la tabla
};
// 2. POST al sistema de cotizaciones
// 3. Redirigir a la URL devuelta
```

### Variables PHP ya disponibles en la página
- `$tokenOpt` → el token_OPT del usuario
- `$empresaIdActiva` → el ID de empresa activa
- `$userId` → el ID de la persona
- `$rows[]` → cada pedido con `id`, `mat`, `project`, `cant`, `pdf`

Estos datos se pueden pasar al JS con `json_encode()` en el bloque PHP.

---

## 10. CONSIDERACIONES PARA NUEVAS FUNCIONALIDADES

### Si agregás un nuevo endpoint (estilo API)
- Seguir el patrón existente: `?param=BASE64(JSON)` o body JSON.
- Agregar validación de campos obligatorios al principio.
- Responder siempre JSON con `Content-Type: application/json`.
- Usar transacciones Doctrine para operaciones que toquen múltiples tablas.
- Si es para la integración con el sistema de cotizaciones, agregar autenticación con `Bearer token`.

### Si modificás el registro
- Verificar que sigue llamando a `syncClienteFacturacion()` al finalizar.
- Verificar que genera `token_OPT` para usuarios nuevos.
- No cambiar el valor del `estado` de empresa sin revisar el impacto en SGA.

### Si modificás el login
- Respetar el orden de verificaciones (estado_persona, empresa validada, empresa activa).
- El usuario soporte hardcodeado debe seguir funcionando.
- La sesión debe seguir seteando: `usuario`, `id`, `nombre`, `apellido`, `rol`, `empresa_id`.

### Si modificás el cambio de empresa
- Solo debe funcionar para `rol = 2`.
- Al cambiar empresa, persistir `empresa_activa_id` en BD (no solo en sesión).
- Verificar que la empresa destino está validada antes de setearla.

---

## 11. RELACIÓN CON proyectoMalkoni2

| Concepto | Responsabilidad |
|----------|----------------|
| Registro de clientes | malkoni-online |
| Autenticación de clientes | malkoni-online |
| token_OPT | malkoni-online (generado y validado aquí) |
| Datos de personas y empresas | malkoni-online (fuente de verdad) |
| Cotizaciones | proyectoMalkoni2 |
| Precios | proyectoMalkoni2 |
| Vendedores y supervisores | proyectoMalkoni2 (solo aquí) |
| Productos y catálogo | proyectoMalkoni2 |

---

**Última actualización**: 22 de junio de 2026 (revisado con código fuente real)
