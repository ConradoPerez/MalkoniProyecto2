# 📚 ÍNDICE DE DOCUMENTACIÓN - PROYECTO MALKONI

**Fecha**: 22 de junio de 2026  
**Proyecto**: Sistema de Cotizaciones + Integración con malkoni-online

---

## 📋 DOCUMENTACIÓN DISPONIBLE

### 🚀 PLAN DE IMPLEMENTACIÓN

**[PLAN_IMPLEMENTACION.md](PLAN_IMPLEMENTACION.md)** — **DOCUMENTO PRINCIPAL**

Plan completo de trabajo para implementar la integración entre proyectoMalkoni2 y malkoni-online:
- 6 fases de desarrollo
- Tareas detalladas con código de ejemplo
- Asignación de roles sugerida
- Dependencias entre tareas
- Riesgos y mitigaciones
- Cronograma estimado: 6-8 semanas

**👉 Empezar por aquí si vas a trabajar en la implementación.**

---

### 🏗️ ARQUITECTURA Y MODELOS

#### [ESTRUCTURA_PERSONAS_EMPRESAS.md](ESTRUCTURA_PERSONAS_EMPRESAS.md)

Documenta la relación N:N entre Personas y Empresas:
- Por qué existe `id_empresa` vs `empresa_activa_id`
- Tabla pivot `persona_empresa`
- Consumidores finales (CF) vs empresas reales
- Casos de uso y ejemplos

**Útil para**: Entender el modelo de datos y relaciones.

---

### 🔧 FUNCIONALIDADES ESPECÍFICAS

#### [COTIZACIONES_VENDEDOR_DOCUMENTACION.md](COTIZACIONES_VENDEDOR_DOCUMENTACION.md)

Flujo completo del dashboard del vendedor:
- Cómo se asignan cotizaciones
- Cómo agregar productos y precios
- Estados de cotizaciones
- Interfaz de usuario (vistas Blade)

**Útil para**: Desarrollar el frontend del vendedor.

---

#### [AGREGAR_PRODUCTO_DOCUMENTACION.md](AGREGAR_PRODUCTO_DOCUMENTACION.md)

Sistema de catálogo de productos con búsqueda jerárquica:
- Categorías → Subcategorías → Tipos → Subtipos
- Filtros dinámicos con AJAX
- Endpoint `/api/productos/*`
- Componente reutilizable

**Útil para**: Implementar el selector de productos.

---

#### [SEEDERS_GUIDE.md](SEEDERS_GUIDE.md)

Guía de seeders para datos de prueba:
- Cómo ejecutar seeders
- Qué datos crea cada seeder
- Cómo agregar nuevos datos de prueba

**Útil para**: Poblar BD de desarrollo/testing.

---

#### [CAMBIO_PERSONA_ID.md](CAMBIO_PERSONA_ID.md)

Guía de migración de `?persona_id=` en URLs a autenticación real:
- Problema actual (sin auth)
- Cómo implementar Laravel Auth
- Cambios necesarios en controladores
- Middleware de roles

**Útil para**: Implementar Fase 2 del plan (Autenticación).

---

### 🌐 SISTEMA MALKONI-ONLINE (Producción)

Toda la documentación del sistema externo en PHP/Doctrine está en la subcarpeta:

#### [malkoni-online/ANALISIS_MALKONI_ONLINE.md](malkoni-online/ANALISIS_MALKONI_ONLINE.md)

Análisis técnico COMPLETO del sistema en producción:
- Arquitectura general (PHP puro + Doctrine)
- Estructura de 42 archivos PHP
- Flujos de registro (3 casos)
- Flujo de login con redirectMap
- Integraciones: Lepton OPT, SGA, deherrajes.com
- Panel de Soporte (rol=3)
- 9 correcciones verificadas con código real

**Útil para**: Entender cómo funciona malkoni-online antes de modificarlo.

---

#### [malkoni-online/README.md](malkoni-online/README.md)

Overview rápido de malkoni-online:
- Estructura de carpetas
- Endpoints principales
- Detalles verificados (formato de APIs, timestamps, etc.)
- Advertencias importantes

**Útil para**: Referencia rápida del sistema externo.

---

#### [malkoni-online/COPILOT_INSTRUCTIONS.md](malkoni-online/COPILOT_INSTRUCTIONS.md)

Reglas para futuros cambios en malkoni-online:
- Qué NO modificar sin analizar (tokens, SGA, Lepton)
- Convenciones de código
- Patrones de endpoints
- Validaciones requeridas
- Integración pendiente (botón Cotizar)

**Útil para**: GitHub Copilot y desarrolladores que modifiquen malkoni-online.

---

## 🎯 GUÍA RÁPIDA: ¿QUÉ LEER SEGÚN TU ROL?

### Backend Developer (Laravel)
1. ✅ [PLAN_IMPLEMENTACION.md](PLAN_IMPLEMENTACION.md) — Fase 1, 2, 4
2. ✅ [ESTRUCTURA_PERSONAS_EMPRESAS.md](ESTRUCTURA_PERSONAS_EMPRESAS.md)
3. ✅ [CAMBIO_PERSONA_ID.md](CAMBIO_PERSONA_ID.md)

### Backend Developer (PHP/Doctrine)
1. ✅ [PLAN_IMPLEMENTACION.md](PLAN_IMPLEMENTACION.md) — Fase 3
2. ✅ [malkoni-online/ANALISIS_MALKONI_ONLINE.md](malkoni-online/ANALISIS_MALKONI_ONLINE.md)
3. ✅ [malkoni-online/COPILOT_INSTRUCTIONS.md](malkoni-online/COPILOT_INSTRUCTIONS.md)

### Frontend Developer
1. ✅ [PLAN_IMPLEMENTACION.md](PLAN_IMPLEMENTACION.md) — Fase 5
2. ✅ [COTIZACIONES_VENDEDOR_DOCUMENTACION.md](COTIZACIONES_VENDEDOR_DOCUMENTACION.md)
3. ✅ [AGREGAR_PRODUCTO_DOCUMENTACION.md](AGREGAR_PRODUCTO_DOCUMENTACION.md)

### QA Engineer
1. ✅ [PLAN_IMPLEMENTACION.md](PLAN_IMPLEMENTACION.md) — Fase 6
2. ✅ [SEEDERS_GUIDE.md](SEEDERS_GUIDE.md)
3. ✅ Todos los documentos para entender flujos

### DevOps
1. ✅ [PLAN_IMPLEMENTACION.md](PLAN_IMPLEMENTACION.md) — Fase 6, Tarea 6.4
2. ✅ [malkoni-online/README.md](malkoni-online/README.md) — URLs de producción

---

## 🔄 FLUJO DE INTEGRACIÓN (Resumen Visual)

```
Usuario en malkoni-online (OPT)
         ↓
  Click "Cotizar"
         ↓
POST /api/crear-cotizacion (malkoni-online)
         ↓
  Valida usuario/empresa
         ↓
  Genera token firmado
         ↓
Redirige a proyectoMalkoni2
         ↓
POST /api/opt/webhook (proyectoMalkoni2)
         ↓
  Valida token HMAC
         ↓
  Sincroniza persona/empresa (on-demand)
         ↓
  Crea cotización
         ↓
Redirige a /cliente/dashboard?cotizacion_id=123
         ↓
Cliente ve cotización (sin precios)
         ↓
Vendedor recibe notificación
         ↓
Vendedor agrega productos y precios
         ↓
Vendedor envía cotización
```

---

## 📞 CONTACTO Y SOPORTE

- **Repositorio**: (agregar URL si existe)
- **Documentación técnica**: Esta carpeta (`docs/`)
- **Código fuente**:
  - proyectoMalkoni2: `C:\Users\Santiago Intili\Desktop\MalkoniProyecto2\proyectoMalkoni2\`
  - malkoni-online: `C:\Users\Santiago Intili\Desktop\MalkoniProyecto2\proyectoMalkoni2\malkoni-online\`

---

## 🔧 MANTENIMIENTO DE ESTA DOCUMENTACIÓN

Cuando modifiques el código, **actualiza la documentación correspondiente**:

| Cambio en código | Actualizar documento |
|------------------|---------------------|
| Nuevo modelo o relación | `ESTRUCTURA_PERSONAS_EMPRESAS.md` |
| Nueva funcionalidad de vendedor | `COTIZACIONES_VENDEDOR_DOCUMENTACION.md` |
| Cambio en catálogo de productos | `AGREGAR_PRODUCTO_DOCUMENTACION.md` |
| Nuevo endpoint en malkoni-online | `malkoni-online/ANALISIS_MALKONI_ONLINE.md` |
| Nueva regla de negocio | `malkoni-online/COPILOT_INSTRUCTIONS.md` |
| Nueva fase/tarea de implementación | `PLAN_IMPLEMENTACION.md` |

---

**Última actualización**: 22 de junio de 2026  
**Mantenido por**: Equipo de Desarrollo Malkoni
