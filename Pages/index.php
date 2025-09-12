<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Chronos — Organiza tu día sin estrés</title>
  <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/litera/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/style.css" rel="stylesheet">
  <style>
   
    .hero {
      background: linear-gradient(180deg, rgba(0,123,255,.08), rgba(0,0,0,0));
      border-bottom: 1px solid rgba(0,0,0,.05);
    }
    .badge-soft {
      background: rgba(13,110,253,.1);
      color: #0d6efd;
      border-radius: 999px;
      padding: .35rem .75rem;
      font-weight: 600;
      font-size: .9rem;
    }
    .shadow-soft { box-shadow: 0 10px 30px rgba(0,0,0,.08); }
    .feature-icon {
      width: 48px; height: 48px; border-radius: 12px;
      display: inline-flex; align-items: center; justify-content: center;
      background: rgba(13,110,253,.1);
    }
    .check { color: #198754; font-weight: 700; }
    .screenshot {
      background: linear-gradient(135deg, #f8f9fa, #eef3ff);
      border: 1px solid rgba(0,0,0,.08);
      border-radius: 1rem;
      min-height: 260px;
    }
    .logos img { height: 26px; opacity:.7; margin: 0 10px; }
    .faq button { text-align: left; }
    .pricing .card { border: 1px solid rgba(0,0,0,.08); }
    .divider { height: 1px; background: rgba(0,0,0,.08); }
  </style>
</head>
<body class="bg-body-tertiary">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg bg-body py-3">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#">Chronos</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div id="nav" class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="#features">Características</a></li>
        <li class="nav-item"><a class="nav-link" href="#how">Cómo funciona</a></li>
        <li class="nav-item"><a class="nav-link" href="#pricing">Planes</a></li>
        <li class="nav-item"><a class="nav-link" href="#faq">Preguntas</a></li>
      </ul>
      <div class="d-flex gap-2">
        <!-- Ajusta rutas a tus páginas reales -->
        <a class="btn btn-outline-primary" href="login.php">Iniciar sesión</a>
        <a class="btn btn-primary" href="register.php">Crear cuenta</a>
      </div>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="container py-5">
    <div class="row align-items-center gy-4">
      <div class="col-lg-6">
        <span class="badge-soft">Tu tiempo, bajo control</span>
        <h1 class="display-5 fw-bold mt-3">Organiza tus tareas y proyectos con <span class="text-primary">Chronos</span></h1>
        <p class="lead text-secondary mt-3">
          Crea listas, asigna fechas límite, recibe recordatorios y enfócate en lo importante.
          Menos caos, más resultados.
        </p>
        <div class="d-flex gap-2 mt-3">
          <a href="register.php" class="btn btn-primary btn-lg">Comenzar gratis</a>
          <a href="#features" class="btn btn-outline-primary btn-lg">Ver características</a>
        </div>
        <div class="d-flex align-items-center gap-3 mt-4 text-secondary">
          <div class="d-flex align-items-center">
            <span class="check me-2">✓</span> Sin tarjeta de crédito
          </div>
          <div class="d-flex align-items-center">
            <span class="check me-2">✓</span> Cancela cuando quieras
          </div>
        </div>
        <div class="logos mt-4">
          <!-- Coloca logos reales si quieres -->
          <img src="data:image/svg+xml,%3Csvg width='120' height='24' xmlns='http://www.w3.org/2000/svg'%3E%3Ctext x='0' y='18' font-size='18'%3EProductHunt%3C/text%3E%3C/svg%3E" alt="Logo 1">
          <img src="data:image/svg+xml,%3Csvg width='90' height='24' xmlns='http://www.w3.org/2000/svg'%3E%3Ctext x='0' y='18' font-size='18'%3EDev.to%3C/text%3E%3C/svg%3E" alt="Logo 2">
          <img src="data:image/svg+xml,%3Csvg width='110' height='24' xmlns='http://www.w3.org/2000/svg'%3E%3Ctext x='0' y='18' font-size='18'%3EPocket%3C/text%3E%3C/svg%3E" alt="Logo 3">
        </div>
      </div>
      <div class="col-lg-6">
        <div class="screenshot p-3 shadow-soft">
          <!-- Reemplaza este bloque por una captura real de tu app -->
          <div class="bg-white h-100 w-100 rounded-3 p-3 shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
              <div class="fw-semibold">Mis tareas de hoy</div>
              <span class="badge text-bg-primary">3 pendientes</span>
            </div>
            <div class="divider my-3"></div>
            <div class="d-flex align-items-start gap-3 mb-3">
              <div class="feature-icon">🗓️</div>
              <div>
                <div class="fw-semibold">Preparar informe de ventas</div>
                <div class="text-secondary small">Vence: hoy 5:00 PM • Prioridad: Alta</div>
              </div>
            </div>
            <div class="d-flex align-items-start gap-3 mb-3">
              <div class="feature-icon">✅</div>
              <div>
                <div class="fw-semibold">Revisar tareas del equipo</div>
                <div class="text-secondary small">Proyecto: Marketing Q4</div>
              </div>
            </div>
            <div class="d-flex align-items-start gap-3">
              <div class="feature-icon">🔔</div>
              <div>
                <div class="fw-semibold">Enviar propuesta a cliente</div>
                <div class="text-secondary small">Recordatorio: 3:30 PM</div>
              </div>
            </div>
            <div class="text-center mt-4">
              <a href="register.php" class="btn btn-primary">Prueba Chronos</a>
            </div>
          </div>
        </div>
      </div>
    </div><!-- row -->
  </div>
</section>

<!-- FEATURES -->
<section id="features" class="py-5">
  <div class="container">
    <div class="text-center mb-4">
      <h2 class="fw-bold">Todo lo que necesitas para cumplir</h2>
      <p class="text-secondary">Herramientas simples, resultados potentes.</p>
    </div>

    <div class="row g-4">
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-soft">
          <div class="card-body">
            <div class="feature-icon mb-3">🧩</div>
            <h5 class="card-title">Listas y proyectos</h5>
            <p class="card-text text-secondary">Organiza por áreas, proyectos o contextos. Arrastra, suelta y prioriza con facilidad.</p>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-soft">
          <div class="card-body">
            <div class="feature-icon mb-3">🔔</div>
            <h5 class="card-title">Recordatorios inteligentes</h5>
            <p class="card-text text-secondary">Recibe avisos en el momento preciso para no olvidar entregas ni reuniones.</p>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-soft">
          <div class="card-body">
            <div class="feature-icon mb-3">⏱️</div>
            <h5 class="card-title">Fechas y prioridades</h5>
            <p class="card-text text-secondary">Define vencimientos, categorías y prioridades para enfocar tu energía donde importa.</p>
          </div>
        </div>
      </div>

      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-soft">
          <div class="card-body">
            <div class="feature-icon mb-3">👥</div>
            <h5 class="card-title">Colaboración</h5>
            <p class="card-text text-secondary">Comparte proyectos y asigna tareas. Mantén a tu equipo alineado y avanzando.</p>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-soft">
          <div class="card-body">
            <div class="feature-icon mb-3">🔐</div>
            <h5 class="card-title">Seguro por diseño</h5>
            <p class="card-text text-secondary">Protección de cuentas con contraseñas cifradas y buenas prácticas desde el primer día.</p>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-soft">
          <div class="card-body">
            <div class="feature-icon mb-3">📊</div>
            <h5 class="card-title">Panel de progreso</h5>
            <p class="card-text text-secondary">Visualiza tu avance semanal, hitos y cargas de trabajo en un vistazo.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section id="how" class="py-5 bg-body">
  <div class="container">
    <div class="text-center mb-4">
      <h2 class="fw-bold">Cómo funciona</h2>
      <p class="text-secondary">De cero a productivo en 3 pasos.</p>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="p-4 bg-white rounded-3 shadow-soft h-100">
          <div class="feature-icon mb-3">📝</div>
          <h5>1. Anota todo</h5>
          <p class="text-secondary mb-0">Captura tareas, ideas y pendientes. No te preocupes por el orden al principio.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-4 bg-white rounded-3 shadow-soft h-100">
          <div class="feature-icon mb-3">🎯</div>
          <h5>2. Prioriza</h5>
          <p class="text-secondary mb-0">Agrupa por proyectos, añade vencimientos y prioridades. Claridad instantánea.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-4 bg-white rounded-3 shadow-soft h-100">
          <div class="feature-icon mb-3">🚀</div>
          <h5>3. Ejecuta</h5>
          <p class="text-secondary mb-0">Enfócate en lo de hoy, recibe recordatorios y marca avances. Siente el progreso.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- SOCIAL PROOF / TESTIMONIOS -->
<section class="py-5">
  <div class="container">
    <div class="text-center mb-4">
      <h2 class="fw-bold">Historias de usuarios</h2>
      <p class="text-secondary">Equipos y freelancers que ya ahorran horas cada semana.</p>
    </div>
    <div class="row g-4">
      <div class="col-md-6">
        <div class="card shadow-soft h-100">
          <div class="card-body">
            <p class="mb-1">“Con Chronos, nuestro equipo de marketing por fin dejó los chats caóticos. Ahora todo fluye.”</p>
            <div class="small text-secondary">— Daniela, Project Manager</div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card shadow-soft h-100">
          <div class="card-body">
            <p class="mb-1">“Me ayudó a separar tareas por contextos: universidad, trabajo y personal. Mucho menos estrés.”</p>
            <div class="small text-secondary">— Luis, Desarrollador</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- PRICING -->
<section id="pricing" class="pricing py-5 bg-body">
  <div class="container">
    <div class="text-center mb-4">
      <h2 class="fw-bold">Planes simples</h2>
      <p class="text-secondary">Empieza gratis. Mejora cuando lo necesites.</p>
    </div>
    <div class="row g-4 align-items-stretch">
      <div class="col-md-6">
        <div class="card h-100 shadow-soft">
          <div class="card-body p-4">
            <h5 class="card-title">Gratis</h5>
            <p class="display-6 fw-bold mb-0">Q0</p>
            <p class="text-secondary">Para uso personal</p>
            <ul class="list-unstyled">
              <li class="mb-2"><span class="check me-2">✓</span> Tareas y proyectos ilimitados</li>
              <li class="mb-2"><span class="check me-2">✓</span> Recordatorios básicos</li>
              <li class="mb-2"><span class="check me-2">✓</span> 1 colaborador por proyecto</li>
            </ul>
            <a href="register.php" class="btn btn-outline-primary w-100">Comenzar</a>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card h-100 shadow-soft border-primary">
          <div class="card-body p-4">
            <div class="badge-soft mb-2">Más popular</div>
            <h5 class="card-title">Pro</h5>
            <p class="display-6 fw-bold mb-0">Q39<span class="fs-6 text-secondary">/mes</span></p>
            <p class="text-secondary">Para equipos y power users</p>
            <ul class="list-unstyled">
              <li class="mb-2"><span class="check me-2">✓</span> Recordatorios avanzados y repetitivos</li>
              <li class="mb-2"><span class="check me-2">✓</span> Colaboración ilimitada</li>
              <li class="mb-2"><span class="check me-2">✓</span> Paneles y reportes</li>
              <li class="mb-2"><span class="check me-2">✓</span> Soporte prioritario</li>
            </ul>
            <a href="register.php" class="btn btn-primary w-100">Probar Pro</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FAQ -->
<section id="faq" class="py-5">
  <div class="container">
    <div class="text-center mb-4">
      <h2 class="fw-bold">Preguntas frecuentes</h2>
      <p class="text-secondary">Respuestas rápidas antes de empezar.</p>
    </div>

    <div class="accordion faq" id="faqAcc">
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#f1">
            ¿Necesito tarjeta de crédito para empezar?
          </button>
        </h2>
        <div id="f1" class="accordion-collapse collapse show" data-bs-parent="#faqAcc">
          <div class="accordion-body">
            No. Crea tu cuenta gratis y prueba Chronos sin compromiso.
          </div>
        </div>
      </div>
      <div class="accordion-item mt-2">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#f2">
            ¿Puedo usar Chronos con mi equipo?
          </button>
        </h2>
        <div id="f2" class="accordion-collapse collapse" data-bs-parent="#faqAcc">
          <div class="accordion-body">
            Sí. Invita a tu equipo a proyectos específicos y asigna tareas con fechas y prioridades.
          </div>
        </div>
      </div>
      <div class="accordion-item mt-2">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#f3">
            ¿Qué pasa si decido cancelar?
          </button>
        </h2>
        <div id="f3" class="accordion-collapse collapse" data-bs-parent="#faqAcc">
          <div class="accordion-body">
            Puedes cancelar en cualquier momento desde tu perfil. Tus datos permanecen seguros.
          </div>
        </div>
      </div>
      <div class="accordion-item mt-2">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#f4">
            ¿Tienen app móvil?
          </button>
        </h2>
        <div id="f4" class="accordion-collapse collapse" data-bs-parent="#faqAcc">
          <div class="accordion-body">
            La versión web está optimizada para móviles. La app nativa está en el roadmap.
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="py-4 bg-body border-top">
  <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
    <div class="small text-secondary">© <span id="year"></span> Chronos. Todos los derechos reservados.</div>
    <div class="small">
      <a href="#" class="text-decoration-none me-3">Privacidad</a>
      <a href="#" class="text-decoration-none me-3">Términos</a>
      <a href="login.php" class="text-decoration-none">Iniciar sesión</a>
    </div>
  </div>
</footer>

<script src="../assets/bootstrap.bundle.js"></script>
<script>
  document.getElementById('year').textContent = new Date().getFullYear();
</script>
</body>
</html>
