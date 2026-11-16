# MarketPlus

Sistema web de gestión comercial y ventas **MarketPlus**, estructurado por roles de usuario (Administrador, Supervisor y Vendedor) y módulos operativos (Clientes, Productos y Ventas), orientado al desarrollo colaborativo, control de calidad (QA) y despliegue continuo.

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

**MarketPlus** permite administrar el flujo comercial completo mediante vistas y permisos diferenciados por perfil:

* **Administrador (`administrador/`):** Gestión global del sistema, usuarios, parámetros y reportes generales.
* **Supervisor (`Supervisor/`):** Supervisión operativa, control de movimientos y validación de procesos.
* **Vendedor (`vendedor/`):** Registro de operaciones comerciales, atención a clientes y gestión de ventas.

---

## Stack Tecnológico

* **Backend:** PHP
* **Frontend:** HTML5, CSS3, JavaScript
* **Base de Datos:** MySQL / MariaDB (scripts y conexión en `bd/`)
* **Control de Versiones:** Git & GitHub (`Ebrizuela/MarketPlus`)

---

## Estructura del Proyecto

```text
MarketPlus/
├── administrador/       # Vistas, paneles y controladores del rol Administrador
├── bd/                  # Conexión a base de datos y scripts SQL de estructura/datos
├── clientes/            # Módulo de gestión y mantenimiento de clientes
├── css/                 # Hojas de estilo del sistema
├── funciones/           # Funciones auxiliares y lógica reutilizable en PHP
├── img/                 # Recursos gráficos, logotipos e íconos
├── includes/            # Componentes compartidos (cabeceras, menús, pie de página, sesión)
├── js/                  # Scripts del lado del cliente y validaciones
├── productos/           # Módulo de catálogo, stock y gestión de productos
├── public/              # Recursos públicos accesibles del sistema
├── Supervisor/          # Vistas y operaciones del rol Supervisor
├── uploads/             # Archivos e imágenes cargadas por los usuarios
├── vendedor/            # Vistas y operaciones del rol Vendedor
├── ventas/              # Módulo de registro, detalle y seguimiento de ventas
├── index.php            # Página principal / punto de entrada del sistema
├── login.php            # Formulario de inicio de sesión
├── logout.php           # Cierre de sesión y limpieza de variables
├── procesar_login.php   # Validación de credenciales y autenticación de roles
├── registro.php         # Registro de nuevos usuarios
└── README.md            # Documentación principal del proyecto
```

---

## Instalación y Entorno Local

### 1. Requisitos previos
* Git instalado y configurado (`user.name` y `user.email`)
* Servidor web local con soporte PHP (contenedor Docker)
* Motor de base de datos MySQL / MariaDB activo

### 2. Clonar el repositorio y cambiar a `dev`
```bash
git clone [https://github.com/Ebrizuela/MarketPlus.git](https://github.com/Ebrizuela/MarketPlus.git)
cd MarketPlus
git checkout dev
```

### 3. Configurar la base de datos
1. Crea la base de datos local e importa el script SQL ubicado en la carpeta `bd/`.
2. Configura las credenciales de conexión en el archivo correspondiente dentro de `bd/` (o `includes/`).

> **Importante:** No subas credenciales reales de producción, archivos de registro (`error_log`) ni respaldos `.sql` con datos sensibles al repositorio.

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

* `feat:` Nueva funcionalidad o módulo (ej. `feat: agregar filtro de búsqueda en ventas`)
* `fix:` Corrección de errores (ej. `fix: validar stock disponible al registrar venta`)
* `refactor:` Mejora interna de código sin alterar funcionalidad
* `docs:` Actualización de documentación
* `test:` Incorporación o ajuste de pruebas de QA

---

## Administración del Repositorio

* **Mantenedor y Revisor Principal:** Eduardo Brizuela (`@Ebrizuela`)
* **Repositorio Oficial:** [https://github.com/Ebrizuela/MarketPlus](https://github.com/Ebrizuela/MarketPlus)