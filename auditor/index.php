<?php
declare(strict_types=1);

require_once __DIR__ . '/../funciones/conexion.php';
require_once __DIR__ . '/middleware.php';

$pdo = conectar();
$identity = auditorRequerirRol($pdo);

if (!auditorTieneSegundoFactorValido()) {
    header('Location: validar_token.php');
    exit;
}

$nombre = auditorSesionValor('nombre')
    ?? trim($identity['nombre'] . ' ' . $identity['apellido']);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Auditoría integral | MarketPlus</title>
    <link rel="stylesheet" href="../css/auditoria.css?v=<?= (string)filemtime(__DIR__ . '/../css/auditoria.css') ?>">
    <script src="../js/auditoria.js?v=<?= (string)filemtime(__DIR__ . '/../js/auditoria.js') ?>" defer data-api="../funciones/api_auditoria.php"></script>
</head>
<body class="auditoria-page">
    <div class="audit-layout">
        <div class="audit-workspace">
            <header class="audit-header">
                <div class="topbar-title">
                    <span>MARKETPLUS <span aria-hidden="true">/</span> AUDITORÍA</span>
                    <strong>Panel de control</strong>
                </div>
                <div class="header-user">
                    <span class="read-only-label">Solo lectura</span>
                    <span class="user-avatar" aria-hidden="true">AU</span>
                    <span class="user-name"><?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?></span>
                    <form class="logout-form" method="post" action="../logout.php">
                        <button class="button button-secondary logout-button" type="submit">Cerrar sesión</button>
                    </form>
                </div>
            </header>
            <main class="audit-main">
                <section class="page-heading" id="resumen">
                    <div>
                        <p class="eyebrow">Control y trazabilidad</p>
                        <h1>Auditoría integral</h1>
                        <p>Supervisa los eventos registrados en los módulos del sistema.</p>
                    </div>
                    <span class="secure-pill"><span aria-hidden="true">●</span> Registro inmutable</span>
                </section>

                <section class="overview-grid" aria-label="Resumen de auditoría">
                    <article class="overview-card overview-card-primary">
                        <span class="overview-icon" aria-hidden="true">↗</span>
                        <div>
                            <span class="overview-label">Eventos encontrados</span>
                            <strong id="total-events">—</strong>
                            <small>Según los filtros aplicados</small>
                        </div>
                    </article>
                </section>

                <section class="filter-panel" id="filtros" aria-label="Filtros de auditoría">
                    <div class="section-heading">
                        <div>
                            <p class="eyebrow">Búsqueda avanzada</p>
                            <h2>Filtrar eventos</h2>
                        </div>
                        <span class="section-note">Combina uno o varios criterios</span>
                    </div>
                    <form id="audit-filters" class="filter-grid">
                        <div class="field">
                            <label for="fecha_inicio">Desde</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio">
                        </div>
                        <div class="field">
                            <label for="fecha_fin">Hasta</label>
                            <input type="date" id="fecha_fin" name="fecha_fin">
                        </div>
                        <div class="field">
                            <label for="modulo">Módulo</label>
                            <select id="modulo" name="modulo">
                                <option value="">Todos</option>
                                <option>Ventas</option>
                                <option>Devoluciones</option>
                                <option>Productos</option>
                                <option>Funcionarios</option>
                                <option>Autenticacion</option>
                                <option>Seguridad</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="tipo_evento">Tipo de evento</label>
                            <input type="text" id="tipo_evento" name="tipo_evento" maxlength="80" placeholder="Ej. edicion">
                        </div>
                        <div class="field">
                            <label for="usuario">Usuario o ID</label>
                            <input type="search" id="usuario" name="usuario" maxlength="150" placeholder="Nombre o identificador">
                        </div>
                        <div class="field">
                            <label for="id_registro_afectado">ID de registro</label>
                            <input type="search" id="id_registro_afectado" name="id_registro_afectado" maxlength="100">
                        </div>
                        <div class="filter-actions">
                            <button class="button button-primary" type="submit">Aplicar filtros</button>
                            <button class="button button-secondary" type="reset">Limpiar</button>
                        </div>
                    </form>
                </section>

                <section class="events-panel" id="eventos" aria-label="Eventos de auditoría">
                    <div class="table-heading">
                        <div>
                            <p class="eyebrow">Actividad del sistema</p>
                            <h2>Eventos registrados</h2>
                            <p id="results-summary" aria-live="polite">Cargando eventos…</p>
                        </div>
                        <span class="sort-hint"><span aria-hidden="true">↓</span> Más recientes primero</span>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th scope="col">Fecha y hora</th>
                                    <th scope="col">Módulo</th>
                                    <th scope="col">Evento</th>
                                    <th scope="col">Descripción</th>
                                    <th scope="col">Usuario</th>
                                    <th scope="col">Registro</th>
                                    <th scope="col" aria-label="Detalle"></th>
                                </tr>
                            </thead>
                            <tbody id="events-body">
                                <tr><td class="table-message" colspan="7">Cargando eventos…</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination">
                        <span id="page-status">Página 1</span>
                        <div>
                            <button class="button button-secondary" id="previous-page" type="button" disabled>Anterior</button>
                            <button class="button button-secondary" id="next-page" type="button" disabled>Siguiente</button>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <dialog id="event-modal" class="event-modal" aria-labelledby="modal-title">
        <div class="modal-header">
            <div><p class="eyebrow">Detalle de auditoría</p><h2 id="modal-title">Evento</h2></div>
            <button class="modal-close" id="close-modal" type="button" aria-label="Cerrar detalle">×</button>
        </div>
        <div id="modal-metadata" class="metadata-grid"></div>
        <p id="modal-description" class="modal-description"></p>
        <div class="diff-grid">
            <section class="json-pane">
                <h3>Antes</h3>
                <pre id="json-before">{}</pre>
            </section>
            <section class="json-pane">
                <h3>Después</h3>
                <pre id="json-after">{}</pre>
            </section>
        </div>
    </dialog>
</body>
</html>
