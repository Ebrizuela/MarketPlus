# MarketPlus

Sistema web de comercio electrónico y gestión comercial **MarketPlus**, orientado al desarrollo colaborativo, pruebas de calidad (QA) y despliegue continuo en entornos contenerizados.

---

## Tabla de Contenidos

1. [Descripción General](#descripción-general)
2. [Stack Tecnológico](#stack-tecnológico)
3. [Estructura del Proyecto](#estructura-del-proyecto)
4. [Instalación y Entorno Local](#instalación-y-entorno-local)
5. [Flujo de Trabajo Git (Ramas y Pull Requests)](#flujo-de-trabajo-git-ramas-y-pull-requests)
6. [Convenciones de Commits](#convenciones-de-commits)
7. [Administración del Repositorio](#administración-del-repositorio)

---

## Descripción General

**MarketPlus** centraliza la administración de catálogo de productos, inventario, usuarios, carrito de compras y procesamiento de pedidos en una arquitectura modular pensada para escalar hacia entornos de producción en contenedores.

---

## Stack Tecnológico

* **Frontend:** HTML5, CSS3, JavaScript (ES6+) / Bootstrap
* **Backend:** PHP / API REST
* **Base de Datos:** MySQL / PostgreSQL
* **Control de Versiones:** Git & GitHub (`Ebrizuela/marketplus`)
* **Infraestructura y Pruebas:** Docker / Kubernetes, pruebas funcionales y de integración (QA)

*(Ajusta las versiones específicas del lenguaje o motor de base de datos según tu configuración local en `.env`).*

---

## Estructura del Proyecto

```text
marketplus/
├── public/              # Archivos públicos, punto de entrada (index.php) y assets
├── src/                 # Lógica de negocio, controladores, modelos y servicios
├── config/              # Archivos de configuración de la aplicación y base de datos
├── database/            # Scripts SQL, migraciones y datos de prueba (seeders)
├── tests/               # Pruebas automatizadas y casos de verificación QA
├── .env.example         # Plantilla de variables de entorno
├── .gitignore           # Exclusiones de Git (.env, vendor, node_modules, etc.)
└── README.md            # Documentación principal del proyecto
```

---

## Instalación y Entorno Local

### 1. Requisitos previos
* Git instalado y configurado (`user.name` y `user.email`)
* Servidor web local o entorno Docker operativo
* Motor de base de datos relacional activo

### 2. Clonar el repositorio y cambiar a `dev`
```bash
git clone [https://github.com/Ebrizuela/marketplus.git](https://github.com/Ebrizuela/marketplus.git)
cd marketplus
git checkout dev
```

### 3. Configurar variables de entorno
Crea tu archivo local a partir de la plantilla y completa las credenciales de tu entorno:
```bash
cp .env.example .env
```
> **Importante:** Nunca subas el archivo `.env` ni respaldos `.sql` con credenciales reales al repositorio.

---

## Flujo de Trabajo Git (Ramas y Pull Requests)

El repositorio aplica reglas estrictas de protección de ramas para garantizar la estabilidad del código en cada etapa:

| Rama | Entorno | Permisos de escritura | Regla de integración |
| :--- | :--- | :--- | :--- |
| **`main`** | Producción | Bloqueado (`push` directo prohibido) | Solo mediante **Pull Request** aprobado desde `qa` |
| **`qa`** | Control de Calidad | Bloqueado (`push` directo prohibido) | Solo mediante **Pull Request** aprobado desde `dev` |
| **`dev`** | Desarrollo | Habilitado para colaboradores | Integración continua del equipo de desarrollo |

### Procedimiento obligatorio para colaboradores

1. **Actualizar la rama de desarrollo antes de comenzar:**
   ```bash
   git checkout dev
   git pull origin dev
   ```
2. **Realizar los cambios en local y registrar los commits:**
   ```bash
   git add .
   git commit -m "feat: descripción clara del cambio realizado"
   ```
3. **Subir los cambios únicamente a `dev`:**
   ```bash
   git push origin dev
   ```
4. **Solicitar paso a QA:**
   * Ingresa al repositorio en GitHub → pestaña **Pull requests** → **New pull request**.
   * Selecciona **base:** `qa` ← **compare:** `dev`.
   * Detalla los cambios realizados y los pasos para probarlos.
   * Espera la revisión y aprobación del administrador antes del *merge*.

---

## Convenciones de Commits

Utiliza prefijos estándar para mantener un historial legible:

* `feat:` Nueva funcionalidad o módulo (ej. `feat: agregar filtro por categoría en catálogo`)
* `fix:` Corrección de errores (ej. `fix: validar stock antes de confirmar pedido`)
* `refactor:` Mejora interna de código sin alterar funcionalidad
* `docs:` Actualización de documentación
* `test:` Incorporación o ajuste de pruebas de QA

---

## Administración del Repositorio

* **Mantenedor y Revisor Principal:** Eduardo Brizuela (`@Ebrizuela`)
* **Repositorio Oficial:** [https://github.com/Ebrizuela/marketplus](https://github.com/Ebrizuela/marketplus)