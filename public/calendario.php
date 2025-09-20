<?php
// Cookies/Session seguras
if (session_status() === PHP_SESSION_NONE) {
  session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
  ]);
  session_start();
}

require_once __DIR__ . '/../includes/auth.php';
require_login();
$user = current_user();

// CSRF
if (empty($_SESSION['_csrf'])) { $_SESSION['_csrf'] = bin2hex(random_bytes(32)); }
$csrf = $_SESSION['_csrf'];
?>
<!doctype html>
<html lang="es" data-bs-theme="<?= htmlspecialchars($user['tema'] ?? 'light') ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Calendario — Chronos</title>
  <meta name="description" content="Sistema de gestión de tareas y calendario personal">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/icon.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">

  <meta name="csrf" content="<?= htmlspecialchars($csrf) ?>">
  <style>
    /* Variables CSS para consistencia */
    :root {
      --border-radius-sm: 0.375rem;
      --border-radius-md: 0.5rem;
      --border-radius-lg: 0.75rem;
      --transition-speed: 0.2s;
      --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
      --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
      --sidebar-width: 280px;
    }

    /* Layout principal */
    .app-container {
      display: flex;
      min-height: 100vh;
    }
    
    /* Sidebar para filtros */
    .sidebar {
      width: var(--sidebar-width);
      background-color: var(--bs-body-bg);
      border-right: 1px solid var(--bs-border-color);
      padding: 1.5rem;
      display: flex;
      flex-direction: column;
      box-shadow: var(--shadow-sm);
      transition: all var(--transition-speed) ease;
    }
    
    .sidebar-header {
      margin-bottom: 1.5rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid var(--bs-border-color);
    }
    
    .sidebar-section {
      margin-bottom: 1.5rem;
    }
    
    .sidebar-section-title {
      font-size: 0.9rem;
      font-weight: 600;
      text-transform: uppercase;
      color: var(--bs-secondary);
      margin-bottom: 0.75rem;
      letter-spacing: 0.5px;
    }
    
    /* Contenido principal */
    .main-content {
      flex: 1;
      display: flex;
      flex-direction: column;
      overflow: auto;
    }
    
    /* Navbar mejorada */
    .navbar {
      box-shadow: var(--shadow-sm);
      padding: 0.75rem 1.5rem;
    }
    
    .navbar-brand {
      font-weight: 700;
      letter-spacing: -0.5px;
    }
    
    /* Calendario con grid mejorado */
    .calendar-container {
      flex: 1;
      padding: 1.5rem;
      overflow: auto;
    }
    
    .cal-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 0.6rem;
    }
    
    .cal-day {
      background: var(--bs-body-bg);
      border: 1px solid var(--bs-border-color);
      border-radius: var(--border-radius-md);
      min-height: 140px;
      padding: 0.75rem;
      position: relative;
      transition: all var(--transition-speed) ease;
      box-shadow: var(--shadow-sm);
    }
    
    .cal-day:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
    }
    
    .cal-day.today {
      border: 2px solid var(--bs-info);
      background-color: rgba(var(--bs-info-rgb), 0.05);
    }
    
    .cal-day .daynum {
      font-weight: 700;
      opacity: 0.9;
      font-size: 0.95rem;
    }
    
    .cal-day .items {
      margin-top: 0.5rem;
      display: flex;
      flex-direction: column;
      gap: 0.35rem;
    }
    
    .cal-day .pill {
      display: inline-flex;
      align-items: center;
      gap: 0.3rem;
      font-size: 0.78rem;
      border-radius: 999px;
      padding: 0.2rem 0.6rem;
      border: 1px solid var(--bs-border-color);
      white-space: nowrap;
      max-width: 100%;
      overflow: hidden;
      text-overflow: ellipsis;
      cursor: grab;
      background-color: var(--bs-body-bg);
      transition: all var(--transition-speed) ease;
    }
    
    .cal-day .pill:hover {
      transform: scale(1.02);
    }
    
    .cal-day .pill:active {
      cursor: grabbing;
      transform: scale(0.98);
    }
    
    .cal-day.muted {
      opacity: 0.5;
    }
    
    .cal-head {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 0.6rem;
      margin-bottom: 0.75rem;
    }
    
    .cal-head .w {
      font-weight: 600;
      opacity: 0.8;
      text-transform: capitalize;
      text-align: center;
      padding: 0.5rem;
      font-size: 0.9rem;
    }
    
    .badge-pos {
      position: absolute;
      right: 0.75rem;
      top: 0.75rem;
      font-size: 0.7rem;
      padding: 0.25rem 0.5rem;
    }
    
    /* Toolbar mejorada */
    .cal-toolbar {
      padding: 1.25rem;
      background-color: var(--bs-body-bg);
      border-radius: var(--border-radius-lg);
      border: 1px solid var(--bs-border-color);
      margin-bottom: 1.5rem;
      box-shadow: var(--shadow-sm);
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .cal-toolbar .btn {
      min-width: 42px;
      border-radius: var(--border-radius-sm);
      transition: all var(--transition-speed) ease;
    }
    
    .cal-toolbar .btn:hover {
      transform: translateY(-1px);
    }
    
    /* Título del calendario */
    #calTitle {
      font-weight: 700;
      letter-spacing: -0.5px;
      margin-bottom: 0;
      color: var(--bs-body-color);
      font-size: 1.5rem;
    }
    
    /* Mejoras para tareas sin fecha */
    .no-date-container {
      background-color: var(--bs-body-bg);
      border-radius: var(--border-radius-md);
      border: 1px solid var(--bs-border-color);
      padding: 1.25rem;
      margin-top: 1.5rem;
      box-shadow: var(--shadow-sm);
    }
    
    /* Modal mejorado */
    .modal-content {
      border-radius: var(--border-radius-lg);
      border: 1px solid var(--bs-border-color);
      box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    
    .modal-header {
      border-bottom: 1px solid var(--bs-border-color);
      padding: 1.25rem;
    }
    
    .modal-body {
      padding: 1.25rem;
    }
    
    .modal-footer {
      border-top: 1px solid var(--bs-border-color);
      padding: 1rem 1.25rem;
    }
    
    .btn {
      border-radius: var(--border-radius-sm);
      transition: all var(--transition-speed) ease;
    }
    
    .btn:hover {
      transform: translateY(-1px);
    }
    
    /* Formularios mejorados */
    .form-control, .form-select {
      border-radius: var(--border-radius-sm);
      padding: 0.5rem 0.75rem;
      transition: all var(--transition-speed) ease;
    }
    
    .form-control:focus, .form-select:focus {
      box-shadow: 0 0 0 0.2rem rgba(var(--bs-info-rgb), 0.25);
    }
    
    /* List group mejorada */
    .list-group-item {
      border: 1px solid var(--bs-border-color);
      padding: 0.75rem 1rem;
      border-radius: var(--border-radius-sm) !important;
      margin-bottom: 0.5rem;
      transition: all var(--transition-speed) ease;
    }
    
    .list-group-item:hover {
      transform: translateX(2px);
    }
    
    /* Mejoras de botones */
    .btn-group .btn {
      border-radius: var(--border-radius-sm);
    }
    
    /* Estilos para elementos siendo arrastrados */
    .dragging {
      opacity: 0.7;
      transform: scale(0.97) rotate(2deg);
    }
    
    .drop-zone {
      background-color: rgba(var(--bs-info-rgb), 0.1);
      border: 2px dashed var(--bs-info);
      transform: scale(1.02);
    }
    
    /* Mejora visual para tareas completadas */
    .text-decoration-line-through {
      text-decoration: line-through !important;
      opacity: 0.7;
    }
    
    /* Quick actions */
    .quick-actions {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0.5rem;
      margin-top: auto;
    }
    
    /* Toggle sidebar para móviles */
    .sidebar-toggle {
      display: none;
      position: fixed;
      bottom: 1rem;
      right: 1rem;
      z-index: 1000;
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background-color: var(--bs-info);
      color: white;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }
    
    /* Mejoras específicas para modo oscuro */
    [data-bs-theme="dark"] {
      --bs-body-color: #dee2e6;
      --bs-body-color-rgb: 222, 226, 230;
      --bs-body-bg: #212529;
      --bs-body-bg-rgb: 33, 37, 41;
      --bs-border-color: #495057;
    }
    
    [data-bs-theme="dark"] .cal-day {
      background-color: var(--bs-dark-bg-subtle);
      border-color: var(--bs-border-color);
    }
    
    [data-bs-theme="dark"] .cal-day .pill {
      background-color: rgba(var(--bs-dark-rgb), 0.2);
      border-color: var(--bs-border-color);
      color: var(--bs-body-color);
    }
    
    [data-bs-theme="dark"] .cal-day.muted {
      opacity: 0.5;
    }
    
    [data-bs-theme="dark"] .list-group-item {
      background-color: var(--bs-dark-bg-subtle);
      border-color: var(--bs-border-color);
      color: var(--bs-body-color);
    }
    
    [data-bs-theme="dark"] .bg-secondary-subtle {
      background-color: rgba(108, 117, 125, 0.3) !important;
    }
    
    [data-bs-theme="dark"] .text-secondary-emphasis {
      color: rgba(255, 255, 255, 0.8) !important;
    }
    
    [data-bs-theme="dark"] .form-control,
    [data-bs-theme="dark"] .form-select {
      background-color: var(--bs-body-bg);
      border-color: var(--bs-border-color);
      color: var(--bs-body-color);
    }
    
    [data-bs-theme="dark"] .form-control:focus,
    [data-bs-theme="dark"] .form-select:focus {
      background-color: var(--bs-body-bg);
      border-color: #86b7fe;
      color: var(--bs-body-color);
      box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    [data-bs-theme="dark"] .btn-outline-secondary {
      color: var(--bs-body-color);
      border-color: var(--bs-border-color);
    }
    
    [data-bs-theme="dark"] .btn-outline-secondary:hover {
      color: #000;
      background-color: var(--bs-secondary);
      border-color: var(--bs-secondary);
    }
    
    [data-bs-theme="dark"] .text-secondary {
      color: rgba(255, 255, 255, 0.6) !important;
    }
    
    /* FIX: Asegurar que los botones tengan el color correcto en modo oscuro */
    [data-bs-theme="dark"] .btn-outline-secondary,
    [data-bs-theme="dark"] .btn-outline-danger {
      --bs-btn-color: var(--bs-body-color);
      --bs-btn-border-color: var(--bs-border-color);
      --bs-btn-hover-color: #000;
    }

    /* FIX: Asegurar que el texto "Hoy" sea visible en modo oscuro */
    [data-bs-theme="dark"] #todayBtn {
      color: var(--bs-body-color) !important;
    }
    
    [data-bs-theme="dark"] #todayBtn:hover {
      color: #000 !important;
    }
    
    /* FIX: Asegurar que los botones de la barra de navegación funcionen en modo oscuro */
    [data-bs-theme="dark"] .navbar .btn-outline-secondary,
    [data-bs-theme="dark"] .navbar .btn-outline-danger {
      --bs-btn-color: #fff;
      --bs-btn-border-color: #fff;
      --bs-btn-hover-color: #000;
      --bs-btn-hover-bg: #fff;
    }
    
    [data-bs-theme="dark"] .navbar .btn-outline-secondary:hover,
    [data-bs-theme="dark"] .navbar .btn-outline-danger:hover {
      color: #000 !important;
    }
    
    /* FIX: Asegurar que el título del calendario sea visible */
    [data-bs-theme="dark"] #calTitle {
      color: var(--bs-body-color);
    }
    
    /* FIX: Asegurar que los textos secundarios sean visibles */
    [data-bs-theme="dark"] .text-secondary {
      color: rgba(255, 255, 255, 0.6) !important;
    }
    
    /* FIX: Asegurar que los elementos de la lista sin fecha sean visibles */
    [data-bs-theme="dark"] .list-unstyled-tight .text-secondary {
      color: rgba(255, 255, 255, 0.6) !important;
    }
    
    /* Mejoras para botones en modo oscuro */
    [data-bs-theme="dark"] .btn {
      border-color: var(--bs-border-color);
    }
    
    [data-bs-theme="dark"] .btn-info {
      color: #000;
    }
    
    /* Responsividad mejorada */
    @media (max-width: 992px) {
      .sidebar {
        position: fixed;
        left: -280px;
        top: 0;
        bottom: 0;
        z-index: 999;
        overflow-y: auto;
      }
      
      .sidebar.active {
        left: 0;
        box-shadow: 5px 0 15px rgba(0,0,0,0.1);
      }
      
      .main-content {
        margin-left: 0;
      }
      
      .sidebar-toggle {
        display: flex;
      }
      
      .cal-day {
        min-height: 110px;
        padding: 0.6rem;
      }
    }
    
    @media (max-width: 768px) {
      .cal-grid {
        gap: 0.4rem;
      }
      
      .cal-day {
        min-height: 90px;
        padding: 0.5rem;
      }
      
      .cal-day .daynum {
        font-size: 0.85rem;
      }
      
      .badge-pos {
        right: 0.5rem;
        top: 0.5rem;
        font-size: 0.65rem;
        padding: 0.2rem 0.4rem;
      }
      
      .cal-head .w {
        font-size: 0.8rem;
        padding: 0.4rem;
      }
      
      .cal-toolbar {
        flex-direction: column;
        align-items: stretch;
      }
      
      .cal-toolbar .btn-group {
        align-self: center;
      }
    }
    
    @media (max-width: 576px) {
      .cal-day {
        min-height: 80px;
      }
      
      .cal-day .pill {
        font-size: 0.7rem;
        padding: 0.15rem 0.4rem;
      }
      
      .navbar-brand {
        font-size: 1rem;
      }
      
      .quick-actions {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body class="bg-body-tertiary">
<div class="app-container">
  <!-- Sidebar para filtros y acciones rápidas -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <h5 class="sidebar-section-title mb-0">Filtros y Acciones</h5>
    </div>
    
    <div class="sidebar-section">
      <div class="sidebar-section-title">Búsqueda</div>
      <input id="q" class="form-control mb-3" placeholder="Buscar tareas...">
    </div>
    
    <div class="sidebar-section">
      <div class="sidebar-section-title">Filtros</div>
      <div class="mb-3">
        <label class="form-label">Estado</label>
        <select id="fStatus" class="form-select">
          <option value="">Todos los estados</option>
          <option value="pendiente">Pendientes</option>
          <option value="completada">Completadas</option>
        </select>
      </div>
      
      <div class="mb-3">
        <label class="form-label">Prioridad</label>
        <select id="fPriority" class="form-select">
          <option value="">Todas las prioridades</option>
          <option value="alta">Alta</option>
          <option value="media">Media</option>
          <option value="baja">Baja</option>
        </select>
      </div>
    </div>
    
    <div class="sidebar-section">
      <div class="sidebar-section-title">Vista Rápida</div>
      <div class="list-group">
        <a href="#" class="list-group-item align-items-left">
          Tareas de hoy
          <span class="badge bg-primary rounded-pill" id="today-count">0</span>
        </a>
        <a href="#" class="list-group-item align-items-left">
          Tareas pendientes
          <span class="badge bg-warning rounded-pill" id="pending-count">0</span>
        </a>
        <a href="#" class="list-group-item align-items-left">
          Tareas completadas
          <span class="badge bg-success rounded-pill" id="completed-count">0</span>
        </a>
      </div>
    </div>
    
    <div class="quick-actions">
      <button id="btnNew" class="btn btn-info text-white">
        <i class="bi bi-plus-circle me-1"></i> Nueva
      </button>
      <a id="todayBtn" class="btn btn-outline-primary" href="lista.php">
        <i class="bi bi-calendar-event me-1"></i> Lista de Tareas
      </a>
    </div>
  </aside>

  <!-- Contenido principal -->
  <div class="main-content">
    <nav class="navbar bg-body border-bottom">
      <div class="container-fluid">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
          <img src="assets/img/Cro.png" alt="Chronos" height="32" class="me-2">
        </a>
        <div class="d-flex align-items-center gap-2">
          <button class="btn btn-sm btn-outline-secondary d-lg-none" id="mobileFilterToggle">
            <i class="bi bi-funnel"></i>
          </button>
          <a class="btn btn-sm" href="lista.php">
            <i class="bi bi-card-checklist me-1"></i> Lista
          </a>
          <button id="btnTheme" class="btn btn-sm" title="Cambiar tema">
            <i class="bi" id="themeIcon"></i> 
          </button>
          <a class="btn btn-sm btn-outline-danger" href="logout.php">Salir</a>
        </div>
      </div>
    </nav>

    <div class="calendar-container">
      <!-- Toolbar mejorada -->
      <div class="cal-toolbar">
        <div class="btn-group">
          <button class="btn btn-sm" id="prevMonth" title="Mes anterior">
            <i class="bi bi-chevron-left"></i>
          </button>
          <h1 class="h4 mb-0 mx-3" id="calTitle"></h1>
          <button class="btn btn-sm" id="nextMonth" title="Mes siguiente">
            <i class="bi bi-chevron-right"></i>
          </button>
        </div>
        
        <div class="ms-auto d-flex align-items-center gap-2">
          <span class="badge bg-info me-2">
            <i class="bi bi-calendar-check me-1"></i>
            <span id="total-tasks">0</span> tareas
          </span>
        </div>
      </div>

      <!--Parte de arriba de dias -->
      <div class="cal-head mb-3">
        <div class="w text-center">Lun</div>
        <div class="w text-center">Mar</div>
        <div class="w text-center">Mié</div>
        <div class="w text-center">Jue</div>
        <div class="w text-center">Vie</div>
        <div class="w text-center">Sáb</div>
        <div class="w text-center">Dom</div>
      </div>

      <!-- Grid -->
      <div id="calGrid" class="cal-grid"></div>

      <!-- Tareas sin fecha -->
      <div class="no-date-container">
        <h2 class="h6 text-secondary mb-3">
          <i class="bi bi-calendar-x me-1"></i> Tareas sin fecha
        </h2>
        <ul id="noDateList" class="list-unstyled mb-0"></ul>
      </div>
    </div>
  </div>
</div>

<!-- Botón para toggle sidebar en móviles -->
<button class="sidebar-toggle" id="sidebarToggle">
  <i class="bi bi-funnel"></i>
</button>

<!-- Modal Día mejorado -->
<div class="modal fade" id="dayModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="dayTitle" class="modal-title">
          <i class="bi bi-calendar-day me-2"></i> Tareas del día
        </h5>
        <button class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="small text-secondary" id="dayMeta"></div>
          <button id="addOnDay" class="btn btn-sm btn-info text-white">
            <i class="bi bi-plus-circle me-1"></i> Añadir tarea
          </button>
        </div>
        <ul id="dayTasks" class="list-group list-group-flush"></ul>
      </div>
    </div>
  </div>
</div>

<!-- Modal Crear/editar mejorado -->
<div class="modal fade" id="taskModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="taskForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="taskTitle">
          <i class="bi bi-card-checklist me-2"></i> Nueva tarea
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" name="id" id="fId">
        <div class="mb-3">
          <label class="form-label">Título</label>
          <input class="form-control" name="titulo" id="fTitulo" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Descripción</label>
          <textarea class="form-control" name="descripcion" id="fDesc" rows="3"></textarea>
        </div>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Prioridad</label>
            <select class="form-select" name="prioridad" id="fPri">
              <option value="alta">Alta</option>
              <option value="media" selected>Media</option>
              <option value="baja">Baja</option>
            </select>
          </div>
          <div class="col-md-8">
            <label class="form-label">Vence</label>
            <input class="form-control" type="datetime-local" name="fecha_vencimiento" id="fFecha">
          </div>
        </div>
        <div class="mt-3">
          <label class="form-label">Etiquetas</label>
          <input class="form-control" name="tags" id="fTags" placeholder="ej: trabajo, universidad">
        </div>
        <div class="mt-3">
          <label class="form-label">Estado</label>
          <select class="form-select" name="estado" id="fEstado">
            <option value="pendiente" selected>Pendiente</option>
            <option value="completada">Completada</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
        <button class="btn btn-info text-white" type="submit" id="btnSave">
          <i class="bi bi-check-circle me-1"></i> Guardar
        </button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// El código JavaScript permanece igual con pequeñas adaptaciones para la nueva UI
const CSRF = document.querySelector('meta[name="csrf"]').content;
const calTitle = document.getElementById('calTitle');
const calGrid  = document.getElementById('calGrid');
const noDateList = document.getElementById('noDateList');

const q = document.getElementById('q');
const fStatus = document.getElementById('fStatus');
const fPriority = document.getElementById('fPriority');
const btnNew = document.getElementById('btnNew');

const prevMonth = document.getElementById('prevMonth');
const nextMonth = document.getElementById('nextMonth');
const todayBtn  = document.getElementById('todayBtn');

let tasks = [];
let view = /* fecha centrada*/ new Date(new Date().getFullYear(), new Date().getMonth(), 1);

let dayModal, taskModal, editing = false, selectedDate = null;
let draggedTask = null; // Para almacenar la tarea que se está arrastrando

// Toggle sidebar en móviles
document.getElementById('sidebarToggle').addEventListener('click', function() {
  document.getElementById('sidebar').classList.toggle('active');
});

document.getElementById('mobileFilterToggle').addEventListener('click', function() {
  document.getElementById('sidebar').classList.toggle('active');
});

document.addEventListener('DOMContentLoaded', async () => {
  dayModal  = new bootstrap.Modal(document.getElementById('dayModal'));
  taskModal = new bootstrap.Modal(document.getElementById('taskModal'));

  // Actualizar icono del tema
  updateThemeIcon();

  await loadTasks(); 
  render();
  updateTaskCounts();

  q.addEventListener('input', debounce(reload, 300));
  fStatus.addEventListener('change', reload);
  fPriority.addEventListener('change', reload);

  prevMonth.addEventListener('click', () => { view = new Date(view.getFullYear(), view.getMonth()-1, 1); render(); });
  nextMonth.addEventListener('click', () => { view = new Date(view.getFullYear(), view.getMonth()+1, 1); render(); });
  todayBtn.addEventListener('click', () => { view = new Date(new Date().getFullYear(), new Date().getMonth(), 1); render(); });

  btnNew.addEventListener('click', () => {
    editing = false;
    document.getElementById('taskTitle').textContent = 'Nueva tarea';
    document.getElementById('taskForm').reset();
    document.getElementById('fId').value = '';
    document.getElementById('fFecha').value = ''; // sin fecha por defecto
    taskModal.show();
  });

  document.getElementById('taskForm').addEventListener('submit', submitTaskForm);
  document.getElementById('addOnDay').addEventListener('click', () => openCreateOnDay(selectedDate));
});

async function reload(){ 
  await loadTasks(); 
  render(); 
  updateTaskCounts();
}

function updateTaskCounts() {
  const today = new Date().toISOString().slice(0, 10);
  const todayTasks = tasks.filter(t => t.fecha_vencimiento && t.fecha_vencimiento.startsWith(today));
  const pendingTasks = tasks.filter(t => t.estado === 'pendiente');
  const completedTasks = tasks.filter(t => t.estado === 'completada');
  
  document.getElementById('today-count').textContent = todayTasks.length;
  document.getElementById('pending-count').textContent = pendingTasks.length;
  document.getElementById('completed-count').textContent = completedTasks.length;
  document.getElementById('total-tasks').textContent = tasks.length;
}

async function loadTasks() {
  const params = new URLSearchParams();
  if (q.value.trim()) params.set('search', q.value.trim());
  if (fStatus.value) params.set('status', fStatus.value);
  if (fPriority.value) params.set('priority', fPriority.value);

  const r = await fetch('api/tasks.php?action=list&' + params.toString());
  const j = await r.json();
  tasks = j.tasks || [];
}

function render() {
  // Título
  const m = view.getMonth(), y = view.getFullYear();
  const meses = ["enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre"];
  calTitle.textContent = `${meses[m][0].toUpperCase()+meses[m].slice(1)} ${y}`;

  // Calcular inicio (lunes) y fin (siempre 6 semanas)
  const first = new Date(y, m, 1);
  const start = new Date(first);
  const day = (first.getDay() + 6) % 7; // 0 lunes ... 6 domingo
  start.setDate(first.getDate() - day);
  calGrid.innerHTML = '';

  const end = new Date(start);
  end.setDate(start.getDate() + 42); // 6 semanas * 7 días

  // Preparar mapa por YYYY-MM-DD
  const byDate = new Map();
  const nodate = [];
  tasks.forEach(t => {
    if (!t.fecha_vencimiento) { nodate.push(t); return; }
    const key = t.fecha_vencimiento.slice(0,10);
    if (!byDate.has(key)) byDate.set(key, []);
    byDate.get(key).push(t);
  });

  // Render 42 días
  let cursor = new Date(start);
  const today = new Date().toISOString().slice(0, 10);
  
  for (let i=0;i<42;i++){
    const key = toKey(cursor);
    const div = document.createElement('div');
    div.className = 'cal-day' + (cursor.getMonth()===m ? '' : ' muted');
    if (key === today) div.classList.add('today');
    div.setAttribute('data-date', key); // Para identificar la fecha al soltar
    div.innerHTML = `
      <span class="daynum">${cursor.getDate()}</span>
      <span class="badge bg-secondary-subtle text-secondary-emphasis badge-pos">${(byDate.get(key)||[]).length}</span>
      <div class="items"></div>
    `;
    const items = div.querySelector('.items');

    (byDate.get(key)||[]).slice(0,3).forEach(t => {
      const pill = document.createElement('div');
      pill.className = 'pill';
      pill.setAttribute('draggable', 'true');
      pill.setAttribute('data-task-id', t.id);
      pill.innerHTML = `
        <span class="me-1 ${t.estado==='completada'?'text-decoration-line-through':''}">${escapeHtml(t.titulo)}</span>
        <span class="badge bg-${prioColor(t.prioridad)}">${t.prioridad||''}</span>
      `;
      
      // Eventos de arrastre
      pill.addEventListener('dragstart', handleDragStart);
      pill.addEventListener('dragend', handleDragEnd);
      
      items.append(pill);
    });

    // Eventos para soltar
    div.addEventListener('dragover', handleDragOver);
    div.addEventListener('dragenter', handleDragEnter);
    div.addEventListener('dragleave', handleDragLeave);
    div.addEventListener('drop', handleDrop);
    
    div.addEventListener('click', () => openDay(key, byDate.get(key)||[]));

    calGrid.append(div);
    cursor.setDate(cursor.getDate()+1);
  }

  // Sin fecha
  noDateList.innerHTML = '';
  if (!nodate.length) {
    noDateList.innerHTML = '<li class="text-secondary small">No hay tareas sin fecha.</li>';
  } else {
    nodate.forEach(t => {
      const li = document.createElement('li');
      li.className = 'mb-2 p-2 bg-primary rounded';
      li.innerHTML = `
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <span class="${t.estado==='completada'?'text-decoration-line-through text-secondary':''}">${escapeHtml(t.titulo)}</span>
            <span class="badge bg-${prioColor(t.prioridad)} ms-1">${t.prioridad}</span>
          </div>
          <div>
            <button class="btn btn-sm btn-outline-sm">Editar</button>
            <button class="btn btn-sm btn-outline-danger ms-1">Borrar</button>
            <button class="btn btn-sm btn-outline-success">${t.estado==='pendiente'?'Completar':'Reabrir'}</button>
          </div>
        </div>
      `;
      const [btnEdit, btnDel, btnTog] = li.querySelectorAll('button');
      btnEdit.addEventListener('click', () => openEdit(t));
      btnDel.addEventListener('click', () => delTask(t.id));
      btnTog.addEventListener('click', () => toggleTask(t.id));
      noDateList.append(li);
    });
  }
}

// Resto del código JavaScript permanece igual...
// [Todas las funciones restantes se mantienen igual que en el código original]

// Funciones para manejar el arrastre de tareas
function handleDragStart(e) {
  draggedTask = this;
  this.classList.add('dragging');
  e.dataTransfer.setData('text/plain', this.getAttribute('data-task-id'));
  e.dataTransfer.effectAllowed = 'move';
}

function handleDragEnd(e) {
  this.classList.remove('dragging');
  // Remover clases de zona de destino de todos los días
  document.querySelectorAll('.cal-day').forEach(day => {
    day.classList.remove('drop-zone');
  });
}

function handleDragOver(e) {
  e.preventDefault();
  e.dataTransfer.dropEffect = 'move';
}

function handleDragEnter(e) {
  e.preventDefault();
  this.classList.add('drop-zone');
}

function handleDragLeave(e) {
  this.classList.remove('drop-zone');
}

async function handleDrop(e) {
  e.preventDefault();
  this.classList.remove('drop-zone');
  
  const taskId = e.dataTransfer.getData('text/plain');
  const targetDate = this.getAttribute('data-date');
  
  if (!taskId || !targetDate) return;
  
  try {
    // Actualizar la fecha de la tarea
    const r = await fetch('api/tasks.php?action=update_date', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: new URLSearchParams({
        _csrf: CSRF,
        id: taskId,
        fecha_vencimiento: targetDate + 'T12:00' // Establecer hora por defecto
      })
    });
    
    const j = await r.json();
    if (!j.success) throw new Error(j.error || 'Error al actualizar la fecha');
    
    // Recargar las tareas y renderizar
    await loadTasks();
    render();
    updateTaskCounts();
  } catch (err) {
    alert(err.message);
  }
}

function openDay(key, ts) {
  selectedDate = key; // YYYY-MM-DD
  document.getElementById('dayTitle').textContent = `Tareas del ${fmtDate(key)}`;
  document.getElementById('dayMeta').textContent = `${ts.length} tarea(s)`;

  const list = document.getElementById('dayTasks');
  list.innerHTML = '';
  if (!ts.length) {
    list.innerHTML = '<li class="list-group-item text-secondary small">No hay tareas este día.</li>';
  } else {
    ts.forEach(t => {
      const li = document.createElement('li');
      li.className = 'list-group-item d-flex justify-content-between align-items-start';
      li.innerHTML = `
        <div class="me-3">
          <div class="fw-semibold ${t.estado==='completada'?'text-decoration-line-through text-secondary':''}">${escapeHtml(t.titulo)}</div>
          <div class="small text-secondary">${escapeHtml(t.descripcion||'')}</div>
          <div class="small text-secondary">${t.tags?('Etiquetas: '+escapeHtml(t.tags)) : ''}</div>
        </div>
        <div class="btn-group btn-group-sm">
          <button class="btn btn-outline-secondary" title="Editar"><i class="bi bi-pencil"></i></button>
          <button class="btn btn-outline-danger" title="Borrar"><i class="bi bi-trash"></i></button>
          <button class="btn btn-outline-secondary" title="${t.estado==='pendiente'?'Completar':'Reabrir'}">
            ${t.estado==='pendiente'?'✓':'↺'}
          </button>
        </div>
      `;
      const [btnEdit, btnDel, btnTog] = li.querySelectorAll('button');
      btnEdit.addEventListener('click', () => { dayModal.hide(); openEdit(t); });
      btnDel.addEventListener('click', () => delTask(t.id));
      btnTog.addEventListener('click', () => toggleTask(t.id));
      list.append(li);
    });
  }
  dayModal.show();
}

function openCreateOnDay(key) {
  dayModal.hide();
  editing = false;
  document.getElementById('taskTitle').textContent = 'Nueva tarea';
  document.getElementById('taskForm').reset();
  document.getElementById('fId').value = '';
  // Prellenar 09:00 local
  document.getElementById('fFecha').value = key + 'T09:00';
  taskModal.show();
}

function openEdit(t) {
  editing = true;
  document.getElementById('taskTitle').textContent = 'Editar tarea';
  document.getElementById('fId').value = t.id;
  document.getElementById('fTitulo').value = t.titulo || '';
  document.getElementById('fDesc').value = t.descripcion || '';
  document.getElementById('fPri').value = t.prioridad || 'media';
  document.getElementById('fTags').value = t.tags || '';
  document.getElementById('fEstado').value = t.estado || 'pendiente';
  document.getElementById('fFecha').value = t.fecha_vencimiento ? t.fecha_vencimiento.replace(' ', 'T').slice(0,16) : '';
  taskModal.show();
}

async function submitTaskForm(e){
  e.preventDefault();
  const fd = new FormData(e.currentTarget);
  const action = editing ? 'update' : 'create';
  try {
    const r = await fetch('api/tasks.php?action=' + action, {
      method: 'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams(fd)
    });
    const j = await r.json();
    if (!j.success) throw new Error(j.error || 'Error');
    taskModal.hide();
    await loadTasks(); 
    render();
    updateTaskCounts();
  } catch (err) { alert(err.message); }
}

async function toggleTask(id){
  try{
    const r = await fetch('api/tasks.php?action=toggle', {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:new URLSearchParams({_csrf:CSRF, id})
    });
    const j = await r.json();
    if(!j.success) throw new Error(j.error||'Error');
    await loadTasks(); 
    render();
    updateTaskCounts();
  }catch(e){ alert(e.message); }
}

async function delTask(id){
  if(!confirm('¿Eliminar esta tarea?')) return;
  try{
    const r = await fetch('api/tasks.php?action=delete', {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:new URLSearchParams({_csrf:CSRF, id})
    });
    const j = await r.json();
    if(!j.success) throw new Error(j.error||'Error');
    await loadTasks(); 
    render();
    updateTaskCounts();
  }catch(e){ alert(e.message); }
}

// Actualizar icono del tema
function updateThemeIcon() {
  const themeIcon = document.getElementById('themeIcon');
  const currentTheme = document.documentElement.getAttribute('data-bs-theme');
  if (currentTheme === 'dark') {
    themeIcon.className = 'bi bi-sun-fill';
  } else {
    themeIcon.className = 'bi bi-moon-fill';
  }
}

// Helpers
function toKey(d){ return d.toISOString().slice(0,10); }
function prioColor(p){ return p==='alta'?'danger':(p==='baja'?'secondary':'primary'); }
function fmtDate(key){
  const [y,m,d] = key.split('-').map(n=>+n);
  const date = new Date(y, m-1, d);
  return date.toLocaleDateString('es-ES',{weekday:'long', year:'numeric', month:'long', day:'numeric'});
}
function debounce(fn, ms){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); } }
function escapeHtml(s){ return (s||'').replace(/[&<>"']/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&#034;',"'":'&#039;' }[c])); }

// Cambiar tema (API user.php)
document.getElementById('btnTheme').addEventListener('click', async () => {
  const next = (document.documentElement.getAttribute('data-bs-theme') === 'dark') ? 'light' : 'dark';
  try {
    const r = await fetch('api/user.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: new URLSearchParams({ action: 'save_theme', _csrf: CSRF, theme: next })
    });
    const j = await r.json();
    if (!j.success) throw new Error(j.error || 'Error');
    document.documentElement.setAttribute('data-bs-theme', j.theme);
    updateThemeIcon();
  } catch (e) { alert(e.message); }
});
</script>
</body>
</html>