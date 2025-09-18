<?php
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

//CSRF
if (empty($_SESSION['_csrf'])) { $_SESSION['_csrf'] = bin2hex(random_bytes(32)); }
$csrf = $_SESSION['_csrf'];

// Asegurar que el tema tenga un valor por defecto si no está establecido
$userTheme = $user['tema'] ?? 'light';
?>
<!doctype html>
<html lang="es" data-bs-theme="<?= htmlspecialchars($userTheme) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mis tareas — Chronos</title>

  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/litera/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">

  <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/icon.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <meta name="csrf" content="<?= htmlspecialchars($csrf) ?>">
  
  <style>
    /* Transición suave para el cambio de tema */
body, .navbar, .list-group-item, .modal-content, .form-control, .form-select, .btn {
  transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
}

/* Ajustes específicos para modo oscuro */
[data-bs-theme="dark"] {
  --bs-body-bg: #212529;
  --bs-body-color: #f8f9fa;
  --bs-border-color-translucent: rgba(255,255,255,.15);
}

[data-bs-theme="dark"] .list-group-item {
  background-color: var(--bs-body-bg);
  color: var(--bs-body-color);
  border-color: var(--bs-border-color-translucent);
}

[data-bs-theme="dark"] .modal-content {
  background-color: var(--bs-body-bg);
  color: var(--bs-body-color);
}

[data-bs-theme="dark"] .form-control,
[data-bs-theme="dark"] .form-select {
  background-color: #2b3035;
  color: #f8f9fa;
  border-color: var(--bs-border-color-translucent);
}

[data-bs-theme="dark"] .form-control:focus,
[data-bs-theme="dark"] .form-select:focus {
  background-color: #343a40;
  color: #fff;
  border-color: #0dcaf0;
  box-shadow: 0 0 0 .25rem rgba(13, 202, 240, .25);
}

[data-bs-theme="dark"] .btn-outline-secondary {
  color: #f8f9fa;
  border-color: var(--bs-border-color-translucent);
}

[data-bs-theme="dark"] .btn-outline-secondary:hover {
  background-color: #495057;
  border-color: #6c757d;
}

[data-bs-theme="dark"] .text-secondary {
  color: #adb5bd !important;
}

[data-bs-theme="dark"] .text-muted {
  color: #8b9cb1 !important;
}
  </style>
</head>
<body class="bg-body-tertiary">
<nav class="navbar navbar-expand-lg bg-body border-bottom">
  <div class="container">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
      <img src="assets/img/chronoBig.png" alt="Chronos" height="32" class="me-2">
    </a>
    <div class="ms-auto d-flex align-items-center gap-2">
    <a class="btn btn-sm btn-outline-secondary" href="calendario.php"><i class="bi bi-calendar3 me-1"></i> Calendario</a>
  <button id="btnTheme" class="btn btn-sm btn-outline-secondary" title="Cambiar tema">
    <i class="bi <?= $userTheme === 'dark' ? 'bi-sun' : 'bi-moon' ?>"></i>
  </button>
  <a class="btn btn-sm btn-outline-danger" href="logout.php">Salir</a>
</div>
  </div>
</nav>

<main class="container py-4">
  <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
    <h1 class="h4 mb-0">Mis tareas</h1>
    <div class="d-flex gap-2">
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
      <button id="btnExport" class="btn btn-outline-secondary">Exportar CSV</button>
      <div class="form-check form-switch align-self-center">
    <input class="form-check-input" type="checkbox" id="notifyToggle">
    <label class="form-check-label" for="notifyToggle">Notificaciones</label>
    </div>
    </div>
  </div>

  <div class="alert alert-info d-none" id="emptyState">
    No hay tareas que coincidan con el filtro. ¡Crea una nueva!
  </div>

  <ul id="taskList" class="list-group shadow-sm"></ul>

  <div class="text-end mt-3">
    <button id="btnSaveOrder" class="btn btn-outline-secondary btn-sm">Guardar orden</button>
  </div>
</main>

<!-- Crear tarea -->
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
const list = document.getElementById('taskList');
const emptyState = document.getElementById('emptyState');
const q = document.getElementById('q');
const fStatus = document.getElementById('fStatus');
const fPriority = document.getElementById('fPriority');
const btnNew = document.getElementById('btnNew');
const btnSaveOrder = document.getElementById('btnSaveOrder');
const btnTheme = document.getElementById('btnTheme');

let tasks = [];
let modal, editing = false;

document.addEventListener('DOMContentLoaded', () => {
  modal = new bootstrap.Modal(document.getElementById('taskModal'));
  loadTasks();
});

q.addEventListener('input', debounce(loadTasks, 300));
fStatus.addEventListener('change', loadTasks);
fPriority.addEventListener('change', loadTasks);

btnNew.addEventListener('click', () => {
  editing = false;
  document.getElementById('taskTitle').textContent = 'Nueva tarea';
  document.getElementById('taskForm').reset();
  document.getElementById('fId').value = '';
  modal.show();
});

document.getElementById('taskForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.currentTarget);
  const action = editing ? 'update' : 'create';
  const body = new URLSearchParams(fd);
  try {
    const r = await fetch('api/tasks.php?action=' + action, {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body
    });
    const j = await r.json();
    if (!j.success) throw new Error(j.error || 'Error');
    modal.hide();
    loadTasks();
  } catch (err) {
    alert(err.message);
  }
});

btnSaveOrder.addEventListener('click', async () => {
  const ids = [...list.querySelectorAll('li[data-id]')].map(li => li.dataset.id);
  if (!ids.length) return;
  try {
    const r = await fetch('api/tasks.php?action=reorder', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: new URLSearchParams({ _csrf: CSRF, 'ids[]': ids })
    });
    const j = await r.json();
    if (!j.success) throw new Error(j.error || 'Error');
    loadTasks();
  } catch (e) { alert(e.message); }
});

document.getElementById('btnExport').addEventListener('click', () => {
  const params = new URLSearchParams();
  if (q.value.trim()) params.set('search', q.value.trim());
  if (fStatus.value) params.set('status', fStatus.value);
  if (fPriority.value) params.set('priority', fPriority.value);

  const url = 'api/tasks.php?action=export_csv&' + params.toString();
  window.open(url, '_blank'); // abrir en nueva pestaña
});

// Notificaciones 1h antes de vencer
const notifyToggle = document.getElementById('notifyToggle');
const NOTIF_KEY = 'chrono_notify_1h_enabled';

// recuerda la preferencia del usuario
notifyToggle.checked = localStorage.getItem(NOTIF_KEY) === '1';
notifyToggle.addEventListener('change', async () => {
  if (notifyToggle.checked) {
    const ok = await ensureNotificationPermission();
    if (!ok) {
      notifyToggle.checked = false;
      return;
    }
    localStorage.setItem(NOTIF_KEY, '1');
  } else {
    localStorage.removeItem(NOTIF_KEY);
  }
});

// pide permiso si no lo tiene
async function ensureNotificationPermission() {
  if (!('Notification' in window)) {
    alert('Tu navegador no soporta notificaciones.');
    return false;
  }
  if (Notification.permission === 'granted') return true;
  if (Notification.permission === 'denied') {
    alert('Las notificaciones están bloqueadas. Habilítalas en la configuración del navegador.');
    return false;
  }
  const perm = await Notification.requestPermission();
  return perm === 'granted';
}

// de-duplicar: marca por (tarea, fecha)
function notifiedKey(t) { return `notif1h_${t.id}_${t.fecha_vencimiento||'nofecha'}`; }

// muestra notificación
function fireNotification(t, minutesLeft) {
  const title = `Tarea próxima: ${t.titulo}`;
  const body  = [
    t.fecha_vencimiento ? `Vence a las ${t.fecha_vencimiento.slice(11,16)} (${minutesLeft} min)` : 'Sin fecha fija',
    t.prioridad ? `Prioridad: ${t.prioridad}` : ''
  ].filter(Boolean).join(' • ');

  try {
    const n = new Notification(title, {
      body,
      icon: 'assets/icons/icon.png', // opcional
      badge: 'assets/icons/icon.png' // opcional
    });
    n.onclick = () => {
      window.focus();
      n.close();
    };
  } catch(e) {
    // Errores generales
    console.log('No se pudo mostrar Notification:', e);
  }
}

// ejecuta chequeo cada minuto si el toggle está activo
let notifyInterval = null;
function startNotifier() {
  if (notifyInterval) clearInterval(notifyInterval);
  notifyInterval = setInterval(checkImminentTasks, 60 * 1000);
  // corre uno inmediato al cargar
  checkImminentTasks();
}

// calcula qué tareas vencen dentro de 60 min
function checkImminentTasks() {
  if (!notifyToggle.checked) return;
  if (!('Notification' in window) || Notification.permission !== 'granted') return;

  const now = Date.now();
  const oneHour = 60 * 60 * 1000;

  (tasks || []).forEach(t => {
    if (!t || t.estado !== 'pendiente' || !t.fecha_vencimiento) return;

    // parsea 'YYYY-MM-DD HH:MM:SS' como local
    const due = new Date(t.fecha_vencimiento.replace(' ', 'T'));
    const delta = due.getTime() - now; // ms hasta vencer

    // si falta entre 0 y 60 min, y no se ha notificado
    if (delta > 0 && delta <= oneHour) {
      const k = notifiedKey(t);
      if (!localStorage.getItem(k)) {
        const minutesLeft = Math.max(1, Math.round(delta / 60000));
        fireNotification(t, minutesLeft);
        localStorage.setItem(k, '1');
      }
    }
  });
}

// arranca el notificador cuando la página carga y cuando se recarga la lista
document.addEventListener('visibilitychange', () => {
  // Cuando vuelve a la pestaña, dispara un chequeo
  if (document.visibilityState === 'visible') checkImminentTasks();
});

document.addEventListener('DOMContentLoaded', () => {
  if (notifyToggle.checked) ensureNotificationPermission().then(ok => { if (ok) startNotifier(); });
});
(async () => {
  setTimeout(() => { if (notifyToggle.checked) startNotifier(); }, 1000);
})();


async function loadTasks() {
  const params = new URLSearchParams();
  if (q.value.trim()) params.set('search', q.value.trim());
  if (fStatus.value) params.set('status', fStatus.value);
  if (fPriority.value) params.set('priority', fPriority.value);

  const r = await fetch('api/tasks.php?action=list&' + params.toString());
  const j = await r.json();
  tasks = j.tasks || [];
  render();
}

function render() {
  list.innerHTML = '';
  if (!tasks.length) { emptyState.classList.remove('d-none'); return; }
  emptyState.classList.add('d-none');

  tasks.forEach(t => {
    const li = document.createElement('li');
    li.className = 'list-group-item d-flex align-items-start gap-3';
    li.dataset.id = t.id;
    li.draggable = true;

    const chk = document.createElement('input');
    chk.type = 'checkbox';
    chk.className = 'form-check-input mt-1';
    chk.checked = (t.estado === 'completada');
    chk.addEventListener('change', () => toggleTask(t.id));

    const body = document.createElement('div');
    body.className = 'flex-grow-1';
    const title = document.createElement('div');
    title.className = 'fw-semibold';
    title.textContent = t.titulo;
    if (t.estado === 'completada') title.classList.add('text-decoration-line-through', 'text-secondary');

    const meta = document.createElement('div');
    meta.className = 'small text-secondary';
    meta.textContent = [
      t.prioridad ? ('Prioridad: ' + t.prioridad) : '',
      t.fecha_vencimiento ? ('Vence: ' + t.fecha_vencimiento) : '',
      t.tags ? ('Etiquetas: ' + t.tags) : ''
    ].filter(Boolean).join(' • ');

    const desc = document.createElement('div');
    if (t.descripcion) {
      desc.className = 'text-muted small mt-1';
      desc.textContent = t.descripcion;
    }

    body.append(title, meta, desc);

    const actions = document.createElement('div');
    actions.className = 'btn-group btn-group-sm';
    actions.innerHTML = `
      <button class="btn btn-outline-secondary" title="Editar"><i class="bi bi-pencil"></i></button>
      <button class="btn btn-outline-danger" title="Borrar"><i class="bi bi-trash"></i></button>
      <button class="btn btn-outline-secondary" title="Arrastrar para reordenar"><i class="bi bi-arrows-move"></i></button>
    `;
    const [btnEdit, btnDel] = actions.querySelectorAll('button');
    btnEdit.addEventListener('click', () => openEdit(t));
    btnDel.addEventListener('click', () => delTask(t.id));

    li.append(chk, body, actions);
    list.append(li);
  });

  enableDnd();
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
  document.getElementById('fFecha').value = t.fecha_vencimiento ? toLocalInput(t.fecha_vencimiento) : '';
  modal.show();
}

async function toggleTask(id) {
  try {
    const r = await fetch('api/tasks.php?action=toggle', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: new URLSearchParams({ _csrf: CSRF, id })
    });
    const j = await r.json();
    if (!j.success) throw new Error(j.error || 'Error');
    loadTasks();
  } catch (e) { alert(e.message); }
}

async function delTask(id) {
  if (!confirm('¿Eliminar esta tarea?')) return;
  try {
    const r = await fetch('api/tasks.php?action=delete', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: new URLSearchParams({ _csrf: CSRF, id })
    });
    const j = await r.json();
    if (!j.success) throw new Error(j.error || 'Error');
    loadTasks();
  } catch (e) { alert(e.message); }
}

// Drag and drop 
function enableDnd() {
  let dragEl = null;
  list.querySelectorAll('li').forEach(li => {
    li.addEventListener('dragstart', e => { dragEl = li; li.classList.add('opacity-50'); });
    li.addEventListener('dragend',   e => { li.classList.remove('opacity-50'); });
    li.addEventListener('dragover',  e => {
      e.preventDefault();
      const after = (e.clientY - li.getBoundingClientRect().top) > (li.offsetHeight/2);
      if (dragEl && dragEl !== li) {
        li[ after ? 'after' : 'before' ](dragEl);
      }
    });
  });
}

// Utilidad: convertir fechas
function toLocalInput(s) {
  const p = s.replace(' ', 'T').slice(0,16);
  return p;
}

// Debounce helper
function debounce(fn, ms){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }}

// Cambiar tema (guarda en BD vía API user.php)
btnTheme.addEventListener('click', async () => {
  const currentTheme = document.documentElement.getAttribute('data-bs-theme');
  const next = currentTheme === 'dark' ? 'light' : 'dark';
  
  try {
    const r = await fetch('api/user.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: new URLSearchParams({ action: 'save_theme', _csrf: CSRF, theme: next })
    });
    const j = await r.json();
    if (!j.success) throw new Error(j.error || 'Error');
    
    // Actualizar el tema en la interfaz
    document.documentElement.setAttribute('data-bs-theme', next);
    
    // Actualizar el icono del botón
    const icon = btnTheme.querySelector('i');
    icon.className = next === 'dark' ? 'bi bi-sun' : 'bi bi-moon';
    
  } catch (e) { 
    console.error('Error cambiando tema:', e);
    alert('Error al cambiar el tema: ' + e.message); 
  }
});
</script>
</body>
</html>