# 🌐 malkoni-online

Sistema web de registro, autenticación y acceso al Optimizador de Cortes (OPT) para clientes de **Malkoni Hnos.**

Desplegado en producción en: **online.malkoni.com.ar** (cPanel)

---

## ¿Qué hace este sistema?

- Registro de usuarios (personas físicas y empresas).
- Login / logout con manejo de sesiones PHP.
- Integración con el Optimizador de Cortes (OPT) de Lepton (iframe con `token_OPT`).
- Consulta de pedidos generados en el OPT (vía API de Lepton).
- Gestión de multi-empresa: un usuario puede pertenecer a varias empresas.
- Cambio de empresa activa para usuarios con rol de usuario común.
- Sincronización bidireccional con el sistema de facturación (SGA).
- Login para el ecommerce externo `deherrajes.com`.

---

## Relación con el sistema de cotizaciones

Este sistema es el **origen** de los clientes que luego generan cotizaciones.

El sistema de cotizaciones (`proyectoMalkoni2`) **NO crea clientes directamente**. Los clientes vienen de aquí.

**Flujo previsto:**
```
Usuario en malkoni-online → Usa OPT → Clic "Cotizar" → Se crea cotización en proyectoMalkoni2
```

Ver [ANALISIS_MALKONI_ONLINE.md](./ANALISIS_MALKONI_ONLINE.md) para documentación técnica detallada.

---

## Estructura de carpetas importantes

```
malkoni-online/
├── public/
│   ├── login.php               # Autenticación (con redirectMap, soporte hardcodeado)
│   ├── registro.php            # Registro empresa (3 pasos, Casos 2 y 3)
│   ├── registro_cf.php         # Registro consumidor final (2 pasos, Caso 1)
│   ├── registro_cuit.php       # Primer paso: ingreso de CUIT
│   ├── tipo_identidad.php      # Punto de entrada: empresa o persona física
│   ├── usuario_token.php       # Endpoint consultado por Lepton para validar token_OPT
│   ├── crear_localidad.php     # Crea localidades en BD
│   ├── validar_usuario.php     # Verifica si empresa ya tiene usuarios
│   ├── validar_usuario_cf.php  # Verifica si CF ya existe
│   ├── SolicitarUsuario.php    # Recuperación de usuario por email
│   ├── restablecer_contraseña.php # Reset de contraseña
│   ├── logout.php              # Cierre de sesión
│   ├── Dashboard/
│   │   ├── opt.php             # Dashboard con iframe del OPT + formulario de soporte
│   │   ├── navbar.php          # Navbar configurable (include PHP con variables opcionales)
│   │   ├── cotizar_mis_pedidos.php  # Lista pedidos OPT + botón Cotizar (pendiente)
│   │   ├── set_empresa_activa.php   # Cambia empresa activa en sesión + persiste en BD
│   │   ├── empresas_asociadas.php   # Lista empresas del usuario
│   │   ├── cambiar_empresa.php      # Flujo de cambio de empresa
│   │   ├── perfil.php, usuarios.php
│   │   └── Soporte/            # ⚠️ Panel exclusivo rol=3 (soporte hardcodeado)
│   │       ├── dashboard_soporte.php    # Panel CRUD de empresas y personas
│   │       └── ajax_*.php               # Endpoints AJAX para operaciones de soporte
│   ├── apifact/
│   │   ├── api.php             # Helper SGA: sga_http_post(), sga_obtener_token(), syncClienteFacturacion()
│   │   └── existecli.php, test_*.php  # Scripts de prueba (no usar en producción)
│   ├── endpoints/
│   │   ├── gestion_empresa.php # Recibe alta/mod de empresa desde SGA
│   │   ├── gestion_cf.php      # Recibe alta/mod de CF desde SGA (incluye calcularCuil)
│   │   ├── act_fecha_ult.php   # Actualiza fecha último contacto
│   │   └── eliminar_cliente.php
│   └── ecommerce/
│       └── login.php           # API de login para deherrajes.com (CORS habilitado)
├── src/Entities/               # Entidades Doctrine (Personas, Empresas, etc.)
├── config/doctrine.php         # Configuración Entity Manager
├── PHPMailer/                  # Librería de emails
├── logs/sga_sync.log           # Logs de sincronización con SGA
└── ANALISIS_MALKONI_ONLINE.md  # Documentación técnica completa
```

---

## Stack tecnológico

| Capa | Tecnología |
|------|-----------|
| Lenguaje | PHP 8.x |
| ORM | Doctrine (Entity Manager) |
| Email | PHPMailer |
| Frontend | HTML/CSS + SweetAlert2, Bootstrap (algunas páginas) |
| Servidor | cPanel (online.malkoni.com.ar) |
| BD | MySQL (`malkoni_online`) |
| Sin framework | Cada archivo PHP es un endpoint independiente |

---

## Tablas principales

| Tabla | Descripción |
|-------|-------------|
| `Personas` | Usuarios del sistema. Contiene `token_OPT` y `empresa_activa_id`. |
| `Empresas` | Clientes corporativos. Contiene `CodCondIVA` (`CF`, `MT`, `RI`, `EX`). |
| `empresas_personas` | Relación N:N entre personas y empresas. |
| `Direcciones` | Domicilios asociados a empresas. |
| `Provincias`, `Localidades`, `Paises` | Datos geográficos. |

---

## Roles de usuarios

| Valor | Rol | Descripción |
|-------|-----|-------------|
| `1` | Admin | Administrador de empresa. Contraseña autogenerada. |
| `2` | Usuario | Usuario operativo. Puede cambiar de empresa activa. |
| `3` | Soporte | Hardcodeado en login.php. No existe en BD. |

---

## Tipos de empresa (`CodCondIVA`)

| Código | Tipo | Descripción |
|--------|------|-------------|
| `CF` | Consumidor Final | Persona física. CUIL calculado desde DNI + género. |
| `MT` | Monotributo | Empresa real. |
| `RI` | Responsable Inscripto | Empresa real. |
| `EX` | Exento | Empresa real. |

---

## Token OPT

Cada usuario tiene un `token_OPT` de 20 caracteres generado al registrarse.

Este token es usado por Lepton para identificar al usuario dentro del Optimizador de Cortes.

**Endpoint de validación** (consultado por Lepton):
```
GET /public/usuario_token.php?access_token={token_OPT}
Respuesta: { email, name, lastname, telephone, address, idCountry, idRegion }
```

---

## APIs externas

### API de Lepton (OPT)
- **URL base**: `https://www.optimizadoronline.com`
- **Empresa**: `malkoni`
- **Autenticación**: `?access_token={token_OPT}`
- **Endpoint de pedidos**: `GET /empresa/malkoni/proyectos?access_token={token}`
- **PDFs de planos**: `https://optionline-prod-files.s3.amazonaws.com/planos/{id_pedido}_.pdf`

### API de SGA (Facturación)
- **URL base**: `http://malkonihnos.ddns.net:9000/sga/rest/tep`
- **Autenticación**: Token dinámico obtenido con `POST /connect`
- **IP requerida**: `50.31.177.150`

---

## Configuración del entorno

El Entity Manager se configura en `config/doctrine.php`. Las credenciales de BD no deben subirse al repositorio.

Para instalar dependencias:
```bash
composer install
```

## Detalles verificados del código

### Respuesta de `usuario_token.php` (formato fijo exigido por Lepton)
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
`idCountry = 1` y `idRegion = 16` son valores **hardcodeados** (Argentina, Córdoba). No cambiar sin coordinar con Lepton.

### Datos de un pedido desde la API de Lepton
```json
{
  "id": 4926740,
  "createdDate": 1751270895000,
  "project": "Proyecto A",
  "mat_descri": "Melamina Blanco 18mm",
  "cant_placas": 4
}
```
`createdDate` es un timestamp en **milisegundos**. Solo se muestran pedidos con `cant_placas > 0`.

### Estado actual del botón "Cotizar"
Muestra un SweetAlert con el mensaje "Cotización online en desarrollo". El `data-pedido-id` del pedido seleccionado ya está disponible en el DOM. Solo falta reemplazar el SweetAlert por un POST al sistema de cotizaciones.

- ⚠️ Los endpoints en `/public/endpoints/` **no tienen autenticación**. Están protegidos implícitamente por IP y por no ser públicos. No agregar autenticación sin coordinar con SGA.
- ⚠️ El token de soporte está **hardcodeado** en `login.php`. No es un usuario de BD.
- ⚠️ `Personas.id_empresa` representa la empresa **inicial** de registro. No usarlo para saber la empresa activa.
- ⚠️ `Personas.empresa_activa_id` representa la empresa **actualmente seleccionada**. Este es el campo correcto para asociar cotizaciones.
- ⚠️ Los logs de sincronización con SGA se escriben en `logs/sga_sync.log`.
