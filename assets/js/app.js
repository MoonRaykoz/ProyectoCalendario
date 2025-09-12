// assets/js/app.js
document.addEventListener('DOMContentLoaded', () => {
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const userId = document.querySelector('meta[name="user-id"]').getAttribute('content');
  const tasksRow = document.getElementById('tasksRow');
  const taskModalEl = document.getElementById('taskModal');
  const taskModal = new bootstrap.Modal(taskModalEl);
  const saveTaskBtn = document.getElementById('saveTaskBtn');
  const btnNewTask = document.getElementById('btnNewTask');
  const taskForm = document.getElementById('taskForm');

  const filterStatus = document.getElementById('filterStatus');
  const filterPriority = document.getElementById('filterPriority');
  const searchInput = document.getElementById('searchInput');

  const themeToggle = document.getElementById('themeToggle');
  const exportBtn = document.getElementById('exportBtn');

  let tasks = [];

  // inicial
  loadTasks();

  // eventos
  btnNewTask.addEventListener('click', () => openModalForNew());
  taskForm.addEventListener('submit', submitTaskForm);
  filterStatus.addEventListener('change', loadTasks);
  filterPriority.addEventListener('change', loadTasks);
  searchInput.addEventListener('keyup', (e) => { if (e.key === 'Enter') loadTasks(); });
  themeToggle.addEventListener('click', toggleTheme);
  exportBtn.addEventListener('click', () => {
    window.location.href = `api/tasks.php?action=export_csv`;
  });

  // Notifications permission (optional)
  if (Notification && Notification.permission !== 'granted') {
    Notification.requestPermission();
  }

  // loadTasks
  async function loadTasks() {
    const params = new URLSearchParams();
    const status = filterStatus.value;
    const priority = filterPriority.value;
    const search = searchInput.value.trim();

    if (status !== 'all') params.append('status', status);
    if (priority !== 'all') params.append('priority', priority);
    if (search) params.append('search', search);

    const res = await fetch('api/tasks.php?action=list&' + params.toString(), { credentials: 'same-origin' });
    const data = await res.json();
    if (data.success) {
      tasks = data.tasks;
      renderTasks(tasks);
      checkUpcomingTasks(tasks);
    } else {
      tasksRow.innerHTML = '<div class="col-12">Error cargando tareas</div>';
    }
  }

  function renderTasks(list) {
    tasksRow.innerHTML = '';
    if (!list.length) {
      tasksRow.innerHTML = `<div class="col-12 text-center py-5"><i class="bi bi-inbox" style="font-size:3rem;"></i><p class="mt-3">No hay tareas</p></div>`;
      return;
    }
    list.forEach(task => {
      const col = document.createElement('div');
      col.className = 'col-md-4 mb-3';
      col.innerHTML = `
        <div class="card task-card ${task.prioridad === 'alta' ? 'priority-high' : task.prioridad === 'media' ? 'priority-medium' : 'priority-low'} ${task.estado === 'completada' ? 'completed' : ''}" draggable="true" data-id="${task.id}">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <h5 class="card-title">${escapeHtml(task.titulo)}</h5>
              <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"></button>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item edit-task" href="#" data-id="${task.id}">Editar</a></li>
                  <li><a class="dropdown-item delete-task" href="#" data-id="${task.id}">Eliminar</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item toggle-task" href="#" data-id="${task.id}">${task.estado === 'pendiente' ? 'Marcar completada' : 'Marcar pendiente'}</a></li>
                </ul>
              </div>
            </div>
            <p class="card-text">${escapeHtml(task.descripcion || 'Sin descripción')}</p>
            ${task.fecha_vencimiento ? `<p class="card-text"><small class="text-muted">Vence: ${formatDate(task.fecha_vencimiento)}</small></p>` : ''}
            ${task.tags ? `<div class="mt-2">${task.tags.split(',').map(t => `<span class="badge bg-secondary me-1">${escapeHtml(t)}</span>`).join('')}</div>` : ''}
          </div>
        </div>
      `;
      tasksRow.appendChild(col);

      // listeners
      const card = col.querySelector('.card');
      col.querySelector('.edit-task').addEventListener('click', (e) => { e.preventDefault(); openEdit(task); });
      col.querySelector('.delete-task').addEventListener('click', (e) => { e.preventDefault(); deleteTask(task.id); });
      col.querySelector('.toggle-task').addEventListener('click', (e) => { e.preventDefault(); toggleTask(task.id); });

      // drag
      card.addEventListener('dragstart', dragStart);
      card.addEventListener('dragover', dragOver);
      card.addEventListener('drop', drop);
      card.addEventListener('dragend', dragEnd);
    });
  }

  // Form handlers
  function openModalForNew() {
    document.getElementById('modalTitle').textContent = 'Nueva Tarea';
    taskForm.reset();
    document.getElementById('taskId').value = '';
    taskModal.show();
  }

  function openEdit(task) {
    document.getElementById('modalTitle').textContent = 'Editar Tarea';
    document.getElementById('taskId').value = task.id;
    document.getElementById('taskTitle').value = task.titulo;
    document.getElementById('taskDescription').value = task.descripcion || '';
    document.getElementById('taskDueDate').value = task.fecha_vencimiento ? toDatetimeLocal(task.fecha_vencimiento) : '';
    document.getElementById('taskPriority').value = task.prioridad;
    document.getElementById('taskTags').value = task.tags || '';
    document.getElementById('taskCompleted').checked = task.estado === 'completada';
    taskModal.show();
  }

  async function submitTaskForm(e) {
    e.preventDefault();
    const formData = new FormData(taskForm);
    // set estado correctly
    if (document.getElementById('taskCompleted').checked) formData.set('estado','completada');
    else formData.delete('estado');

    // add csrf
    formData.set('_csrf', csrf);

    const id = formData.get('id');
    const action = id ? 'update' : 'create';
    formData.append('action', action);

    const res = await fetch('api/tasks.php?action=' + action, {
      method: 'POST',
      body: formData
    });
    if (res.headers.get('Content-Type') && res.headers.get('Content-Type').includes('application/json')) {
      const data = await res.json();
      if (data.success) {
        taskModal.hide();
        loadTasks();
      } else {
        alert(data.error || 'Error guardando tarea');
      }
    } else {
      alert('Respuesta inesperada');
    }
  }

  async function deleteTask(id) {
    if (!confirm('¿Eliminar esta tarea?')) return;
    const form = new FormData();
    form.append('id', id);
    form.append('_csrf', csrf);
    form.append('action', 'delete');
    const res = await fetch('api/tasks.php?action=delete', { method: 'POST', body: form });
    const data = await res.json();
    if (data.success) loadTasks(); else alert(data.error || 'Error');
  }

  async function toggleTask(id) {
    const form = new FormData();
    form.append('id', id);
    form.append('_csrf', csrf);
    form.append('action','toggle');
    const res = await fetch('api/tasks.php?action=toggle', { method: 'POST', body: form });
    const data = await res.json();
    if (data.success) loadTasks(); else alert(data.error || 'Error');
  }

  // Drag & drop functions (simple: swap order in DOM, then send order to server)
  let dragSrcEl = null;
  function dragStart(e) {
    dragSrcEl = e.currentTarget;
    e.dataTransfer.effectAllowed = 'move';
    e.currentTarget.classList.add('dragging');
  }
  function dragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    const el = e.currentTarget;
    el.classList.add('drag-over');
  }
  function drop(e) {
    e.preventDefault();
    const target = e.currentTarget;
    if (dragSrcEl && target !== dragSrcEl) {
      // swap nodes in DOM (we use parent elements)
      const srcParent = dragSrcEl.parentElement;
      const tgtParent = target.parentElement;
      const srcIndex = Array.from(srcParent.parentElement.children).indexOf(srcParent);
      const tgtIndex = Array.from(tgtParent.parentElement.children).indexOf(tgtParent);
      if (srcIndex < tgtIndex) {
        tgtParent.parentElement.insertBefore(srcParent, tgtParent.nextSibling);
      } else {
        tgtParent.parentElement.insertBefore(srcParent, tgtParent);
      }
      // enviar nuevo orden al servidor
      sendOrderToServer();
    }
  }
  function dragEnd(e) {
    e.currentTarget.classList.remove('dragging');
    document.querySelectorAll('.card').forEach(c => c.classList.remove('drag-over'));
  }

  async function sendOrderToServer() {
    // recopilamos IDs en orden visual
    const ids = Array.from(document.querySelectorAll('#tasksRow .card')).map(c => c.getAttribute('data-id'));
    const form = new FormData();
    form.append('_csrf', csrf);
    form.append('action','reorder');
    ids.forEach(id => form.append('ids[]', id));
    const res = await fetch('api/tasks.php?action=reorder', { method: 'POST', body: form });
    const result = await res.json();
    if (!result.success) console.error('Error reordenando', result);
  }

  // Theme toggle
  async function toggleTheme() {
    const current = document.body.classList.contains('dark-mode') ? 'dark' : 'light';
    const newTheme = current === 'dark' ? 'light' : 'dark';
    document.body.classList.toggle('dark-mode');
    document.body.classList.toggle('light-mode');
    // post to server
    const form = new FormData();
    form.append('action','save_theme');
    form.append('theme', newTheme);
    form.append('_csrf', csrf);
    await fetch('api/user.php', { method:'POST', body: form });
  }

  // Notificaciones de tareas próximas
  function checkUpcomingTasks(list) {
    const now = Date.now();
    const upcoming = list.filter(t => t.fecha_vencimiento && new Date(t.fecha_vencimiento) - now > 0 && new Date(t.fecha_vencimiento) - now <= 60 * 60 * 1000); // próximas 1h
    if (Notification && Notification.permission === 'granted') {
      upcoming.forEach(t => {
        const when = new Date(t.fecha_vencimiento);
        const title = `Tarea próxima: ${t.titulo}`;
        const body = `Vence ${formatDateTime(when)}.`;
        new Notification(title, { body });
      });
    }
  }

  // ayuda visual / util
  function escapeHtml(s) {
    return String(s || '').replace(/[&<>"']/g, function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m];});
  }
  function formatDate(d) {
    if (!d) return '';
    const dt = new Date(d);
    return dt.toLocaleDateString('es-ES', { year:'numeric', month:'short', day:'numeric' });
  }
  function formatDateTime(dt) {
    return dt.toLocaleString('es-ES');
  }
  function toDatetimeLocal(dateStr) {
    if (!dateStr) return '';
    const dt = new Date(dateStr);
    const off = dt.getTimezoneOffset();
    const local = new Date(dt.getTime() - (off * 60000));
    return local.toISOString().slice(0,16);
  }
});
