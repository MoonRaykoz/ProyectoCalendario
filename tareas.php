<?php
// tareas.php
require_once 'includes/auth.php';
requireLogin();
$user = currentUser();
$csrf = generate_csrf();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>TaskMaster - Tareas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
  <meta name="csrf-token" content="<?= htmlspecialchars($csrf) ?>">
  <meta name="user-id" content="<?= $user['id'] ?>">
</head>
<body class="<?= ($user['tema'] ?? 'light') === 'dark' ? 'dark-mode' : 'light-mode' ?>">
<?php
// header simple
?>
<nav class="navbar navbar-expand-lg" style="background-color:#4361ee;">
  <div class="container">
    <a class="navbar-brand text-white" href="#"><i class="bi bi-check-circle-fill me-2"></i>TaskMaster</a>
    <div class="ms-auto d-flex align-items-center">
      <div class="me-3 text-white"><?= htmlspecialchars($user['nombre']) ?></div>
      <a class="btn btn-outline-light me-2" href="calendario.php">Calendario</a>
      <a class="btn btn-outline-light" href="logout.php">Cerrar sesión</a>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <div class="row mb-3">
    <div class="col-md-6">
      <h2>Mis Tareas</h2>
    </div>
    <div class="col-md-6 text-end">
      <button class="btn btn-primary" id="btnNewTask"><i class="bi bi-plus-circle"></i> Nueva Tarea</button>
      <button class="btn btn-outline-secondary" id="exportBtn"><i class="bi bi-download"></i> Exportar CSV</button>
    </div>
  </div>

  <!-- filtros y búsqueda -->
  <div class="row mb-3">
    <div class="col-md-3">
      <select id="filterStatus" class="form-select">
        <option value="all">Todas</option>
        <option value="pendiente">Pendientes</option>
        <option value="completada">Completadas</option>
      </select>
    </div>
    <div class="col-md-3">
      <select id="filterPriority" class="form-select">
        <option value="all">Todas prioridades</option>
        <option value="alta">Alta</option>
        <option value="media">Media</option>
        <option value="baja">Baja</option>
      </select>
    </div>
    <div class="col-md-4">
      <input id="searchInput" class="form-control" placeholder="Buscar por título, descripción o etiqueta...">
    </div>
    <div class="col-md-2 text-end">
      <button class="btn btn-sm btn-outline-secondary" id="themeToggle"><i class="bi bi-moon-fill"></i></button>
    </div>
  </div>

  <div id="tasksRow" class="row"></div>
</div>

<!-- Modal tarea -->
<div class="modal fade" id="taskModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="taskForm">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
        <input type="hidden" id="taskId" name="id">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Nueva Tarea</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Título</label>
            <input id="taskTitle" class="form-control" name="titulo" required>
          </div>
          <div class="mb-3">
            <label>Descripción</label>
            <textarea id="taskDescription" class="form-control" name="descripcion" rows="3"></textarea>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label>Fecha de vencimiento</label>
              <input id="taskDueDate" class="form-control" type="datetime-local" name="fecha_vencimiento">
            </div>
            <div class="col-md-6 mb-3">
              <label>Prioridad</label>
              <select id="taskPriority" class="form-select" name="prioridad">
                <option value="baja">Baja</option>
                <option value="media" selected>Media</option>
                <option value="alta">Alta</option>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label>Etiquetas (separadas por comas)</label>
            <input id="taskTags" class="form-control" name="tags" placeholder="trabajo, personal, urgente">
          </div>
          <div class="form-check">
            <input id="taskCompleted" class="form-check-input" type="checkbox" name="estado" value="completada">
            <label class="form-check-label">Marcar como completada</label>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button>
          <button class="btn btn-primary" id="saveTaskBtn" type="submit">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js"></script>
<script>window.user = <?= json_encode($user) ?>;</script>
<script src="assets/js/app.js"></script>
</body>
</html>

<script>
// Pedir permiso al navegador
if (Notification.permission !== "granted") {
    Notification.requestPermission();
}

// Función para revisar tareas y notificar
function checkTasks() {
    fetch('get_notifications.php')
    .then(response => response.json())
    .then(tareas => {
        tareas.forEach(t => {
            // Crear notificación por cada tarea
            new Notification('Tarea próxima a vencer', {
                body: t.titulo + ' - vence: ' + t.fecha_vencimiento,
                icon: 'icono.png' // opcional: ícono de la app
            });
        });
    });
}

// Revisar cada minuto
setInterval(checkTasks, 60 * 1000);
</script>
