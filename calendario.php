<?php
require_once 'includes/auth.php';
requireLogin();
$user = currentUser();
$csrf = generate_csrf();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Calendario</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.css" rel="stylesheet">
</head>
<body class="<?= ($user['tema'] ?? 'light') === 'dark' ? 'dark-mode' : 'light-mode' ?>">
<nav class="navbar" style="background:#4361ee;"><div class="container"><a class="navbar-brand text-white" href="tareas.php">Volver a Tareas</a></div></nav>
<div class="container mt-3">
  <div id="calendar"></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', async function(){
  const calendarEl = document.getElementById('calendar');
  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    headerToolbar: { left:'prev,next today', center:'title', right:'dayGridMonth,timeGridWeek,timeGridDay' },
    events: async function(info, successCallback, failureCallback) {
      const params = new URLSearchParams();
      const res = await fetch('api/tasks.php?action=list');
      const data = await res.json();
      if (data.success) {
        const events = data.tasks.filter(t => t.fecha_vencimiento).map(t => ({
          title: t.titulo,
          start: t.fecha_vencimiento,
          extendedProps: { id: t.id, prioridad: t.prioridad }
        }));
        successCallback(events);
      } else failureCallback([]);
    },
    eventClick: function(info) {
      const id = info.event.extendedProps.id;
      // abrir modal de edición: para simplicidad redirigimos a tareas y se editará ahí
      window.location.href = 'tareas.php';
    }
  });
  calendar.render();
});
</script>
</body>
</html>
