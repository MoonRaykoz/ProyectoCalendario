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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/litera/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/icon.png">

  <meta name="csrf" content="<?= htmlspecialchars($csrf) ?>">
  <style>
    /* Calendario con grid */
    .cal-grid{
      display:grid;grid-template-columns:repeat(7,1fr);gap:.5rem
    }
    .cal-day{
      background:var(--bs-body-bg);border:1px solid var(--bs-border-color);
      border-radius:.5rem;min-height:110px;padding:.5rem;position:relative
    }
    .cal-day .daynum{font-weight:600;opacity:.85}
    .cal-day .items{margin-top:.35rem;display:flex;flex-direction:column;gap:.25rem}
    .cal-day .pill{
      display:inline-flex;align-items:center;gap:.25rem;font-size:.78rem;
      border-radius:999px;padding:.15rem .5rem;border:1px solid var(--bs-border-color);
      white-space:nowrap;max-width:100%;overflow:hidden;text-overflow:ellipsis;
      cursor: grab; /* Indicador de que se puede arrastrar */
      background-color: var(--bs-body-bg);
    }
    .cal-day .pill:active {
      cursor: grabbing; /* Cambia el cursor cuando se está arrastrando */
    }
    .cal-day.muted{opacity:.55}
    .cal-head{display:grid;grid-template-columns:repeat(7,1fr);gap:.5rem}
    .cal-head .w{font-weight:600;opacity:.7;text-transform:capitalize}
    .badge-pos{position:absolute;right:.5rem;top:.5rem}
    .cal-toolbar .btn{min-width:40px}
    .list-unstyled-tight{margin:0;padding-left:1rem}
    .list-unstyled-tight li{margin:.15rem 0}
    
    /* Mejoras para modo oscuro */
    [data-bs-theme="dark"] {
      --bs-body-color: #e9ecef;
      --bs-body-color-rgb: 233, 236, 239;
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
      opacity: 0.6;
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
    
    /* Estilos para elementos siendo arrastrados */
    .dragging {
      opacity: 0.5;
      transform: scale(0.95);
    }
    
    .drop-zone {
      background-color: rgba(var(--bs-info-rgb), 0.1);
      border: 2px dashed var(--bs-info);
    }
    
    /* Mejora visual para tareas completadas */
    .text-decoration-line-through {
      text-decoration: line-through !important;
    }
    
    /* Mejoras específicas para la barra de herramientas */
    .cal-toolbar {
      padding: 1rem;
      background-color: var(--bs-body-bg);
      border-radius: 0.5rem;
      border: 1px solid var(--bs-border-color);
      margin-bottom: 1rem;
    }
    
    /* Mejoras para los botones en modo oscuro */
    [data-bs-theme="dark"] .btn {
      border-color: var(--bs-border-color);
    }
    
    [data-bs-theme="dark"] .btn-info {
      color: #000;
    }
  </style>
</head>
<body class="bg-body-tertiary">
<nav class="navbar navbar-expand-lg bg-body border-bottom">
  <div class="container">
   <div class="ms-auto d-flex align-items-center gap-2">
  <a class="btn btn-sm btn-outline-secondary" href="lista.php">
    <i class="bi bi-card-checklist me-1"></i> Lista de tareas
  </a>
  <button id="btnTheme" class="btn btn-sm btn-outline-secondary" title="Cambiar tema">
    <i class="bi" id="themeIcon"></i> Tema
  </button>
  <a class="btn btn-sm btn-outline-danger" href="logout.php">Salir</a>
</div>

  </div>
</nav>

<main class="container py-4">
  <!-- Toolbar -->
  <div class="cal-toolbar d-flex flex-wrap align-items-center gap-2 mb-3">
    <div class="btn-group">
      <button class="btn btn-outline-secondary" id="prevMonth" title="Mes anterior">«</button>
      <button class="btn btn-outline-secondary" id="todayBtn">Hoy</button>
      <button class="btn btn-outline-secondary" id="nextMonth" title="Mes siguiente">»</button>
    </div>
    <h1 class="h4 mb-0 ms-2" id="calTitle"></h1>

    <div class="ms-auto d-flex gap-2 flex-wrap">
      <input id="q" class="form-control" placeholder="Buscar (título, descripción, tags)">
      <select id="fStatus" class="form-select" style="max-width: 180px;">
        <option value="">Estado: todos</option>
        <option value="pendiente">Pendientes</option>
        <option value="completada">Completadas</option>
      </select>
      <select id="fPriority" class="form-select" style="max-width: 180px;">
        <option value="">Prioridad: todas</option>
        <option value="alta">Alta</option>
        <option value="media">Media</option>
        <option value="baja">Baja</option>
      </select>
      <button id="btnNew" class="btn btn-info text-white">Nueva tarea</button>
    </div>
  </div>

  <!--Parte de arriba de dias -->
  <div class="cal-head mb-2">
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

  <!-- Tareas sin fecha por alguna razon-->
  <div class="mt-4">
    <h2 class="h6 text-secondary">Tareas sin fecha</h2>
    <ul id="noDateList" class="list-unstyled-tight"></ul>
  </div>
</main>

<!-- Día -->
<div class="modal fade" id="dayModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="dayTitle" class="modal-title">Tareas del día</h5>
        <button class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="small text-secondary" id="dayMeta"></div>
          <button id="addOnDay" class="btn btn-sm btn-info text-white">Añadir tarea en este día</button>
        </div>
        <ul id="dayTasks" class="list-group"></ul>
      </div>
    </div>
  </div>
</div>

<!--Crear/editar -->
<div class="modal fade" id="taskModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="taskForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="taskTitle">Nueva tarea</h5>
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
        <button class="btn btn-info text-white" type="submit" id="btnSave">Guardar</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
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

document.addEventListener('DOMContentLoaded', async () => {
  dayModal  = new bootstrap.Modal(document.getElementById('dayModal'));
  taskModal = new bootstrap.Modal(document.getElementById('taskModal'));

  // Actualizar icono del tema
  updateThemeIcon();

  await loadTasks(); render();

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

async function reload(){ await loadTasks(); render(); }

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
  for (let i=0;i<42;i++){
    const key = toKey(cursor);
    const div = document.createElement('div');
    div.className = 'cal-day' + (cursor.getMonth()===m ? '' : ' muted');
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
      li.innerHTML = `
        <span class="${t.estado==='completada'?'text-decoration-line-through text-secondary':''}">${escapeHtml(t.titulo)}</span>
        <span class="badge bg-${prioColor(t.prioridad)} ms-1">${t.prioridad}</span>
        <button class="btn btn-sm btn-outline-secondary ms-2">Editar</button>
        <button class="btn btn-sm btn-outline-danger ms-1">Borrar</button>
        <button class="btn btn-sm btn-outline-secondary ms-1">${t.estado==='pendiente'?'Completar':'Reabrir'}</button>
      `;
      const [btnEdit, btnDel, btnTog] = li.querySelectorAll('button');
      btnEdit.addEventListener('click', () => openEdit(t));
      btnDel.addEventListener('click', () => delTask(t.id));
      btnTog.addEventListener('click', () => toggleTask(t.id));
      noDateList.append(li);
    });
  }
}

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
    await loadTasks(); render();
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
    await loadTasks(); render();
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
    await loadTasks(); render();
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