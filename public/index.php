<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Chronos — Organiza tu día sin estrés</title>

  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="assets/css/style.css" rel="stylesheet">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/icon.png">

  <meta name="description" content="Chronos: gestor de tareas y proyectos. Organiza tu tiempo con recordatorios inteligentes y paneles de progreso.">
  <meta property="og:title" content="Chronos — Organiza tu día sin estrés">
  <meta property="og:description" content="Crea listas, asigna fechas y recibe recordatorios. Menos caos, más resultados.">
  <meta property="og:type" content="website">
</head>
<body class="bg-body-tertiary">

<nav class="navbar navbar-expand-lg bg-body sticky-top py-3" data-aos="fade-down">
  <div class="container">
   <a class="navbar-brand fw-bold d-flex align-items-center" href="#">
    <img src="assets/img/Cro.png" alt="Chronos logo" width="auto" height="40" class="me-2">
   </a>
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
        <a class="btn btn-primary-outline" href="login.php">Iniciar sesión</a>
        <a class="btn btn-info text-white btn-lg" href="register.php">Crear cuenta</a>
      </div>
    </div>
  </div>
</nav>

<section class="hero py-5">
  <div class="container py-5">
    <div class="row align-items-center gy-4">
      <div class="col-lg-6" data-aos="fade-right">
        <div class="baadge-pill mb-3">Tu tiempo, bajo control</div>
        <h1 class="display-5 fw-bold mt-3">
          Organiza tus tareas y proyectos con <span class="text-primary-accent">Chronos</span>
        </h1>
        <p class="lead text-secondary mt-3">
          Crea listas, asigna fechas límite, recibe recordatorios y enfócate en lo importante.
          Menos caos, más resultados.
        </p>
        <div class="d-flex flex-wrap gap-2 mt-3">
          <a href="register.php" class="btn btn-info text-white btn-lg">Comenzar gratis</a>
          <a href="#features" class="btn btn-primary-outline btn-lg">Ver características</a>
        </div>
        <div class="d-flex align-items-center gap-3 mt-4 text-secondary small">
          <div class="d-flex align-items-center"><i class="bi bi-check-circle-fill text-success me-2"></i> Sin tarjeta de crédito</div>
          <div class="d-flex align-items-center"><i class="bi bi-check-circle-fill text-success me-2"></i> Cancela cuando quieras</div>
        </div>
      </div>
      <div class="col-lg-6 text-center" data-aos="fade-left">
        <div class="app-mockup">
          <div class="mockup-header">
            <div class="mockup-dot red"></div>
            <div class="mockup-dot yellow"></div>
            <div class="mockup-dot green"></div>
          </div>
          <div class="bg-white rounded-3 p-4 shadow-sm mockup-content">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div class="fw-semibold">Mis tareas de hoy</div>
              <span class="badge rounded-pill bg-primary text-white">3 pendientes</span>
            </div>
            <hr class="my-3">
            <div class="d-flex align-items-center gap-3 mb-3">
              <i class="bi bi-calendar-check-fill fs-4 text-primary"></i>
              <div>
                <div class="fw-semibold">Preparar informe de ventas</div>
                <div class="text-secondary small">Vence: hoy 5:00 PM • Prioridad: Alta</div>
              </div>
            </div>
            <div class="d-flex align-items-center gap-3 mb-3">
              <i class="bi bi-clipboard-check-fill fs-4 text-primary"></i>
              <div>
                <div class="fw-semibold">Revisar tareas del equipo</div>
                <div class="text-secondary small">Proyecto: Marketing Q4</div>
              </div>
            </div>
            <div class="d-flex align-items-center gap-3">
              <i class="bi bi-bell-fill fs-4 text-primary"></i>
              <div>
                <div class="fw-semibold">Enviar propuesta a cliente</div>
                <div class="text-secondary small">Recordatorio: 3:30 PM</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-4 bg-body-secondary">
  <div class="container">
    <div class="cta-band p-4 p-md-5 d-flex flex-column flex-lg-row align-items-center justify-content-between gap-3 rounded-4" data-aos="zoom-in">
      <div class="row align-items-center w-100 g-4">
         <div class="col-lg-6 text-center">
          <img src="https://images.unsplash.com/photo-1486312338219-ce68d2c6f44d?q=80&w=1172&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Focus" class="img-fluid rounded shadow">
        </div>
        <div class="col-lg-6">
          <h3 id="gray" class="fw-bold mb-1 text-dark">Enfócate en lo que importa</h3>
          <p class="mb-0 text-secondary">Paneles claros, recordatorios puntuales y colaboración sencilla.</p>
          <div class="d-flex gap-2 mt-3">
            <a class="btn btn-info text-white btn-lg" href="register.php">Crear cuenta</a>
            <a class="btn btn-primary-outline btn-lg text-dark" href="#how">Ver cómo funciona</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="features" class="py-5">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
      <h2 class="fw-bold">Todo lo que necesitas para cumplir</h2>
      <p class="text-secondary">Herramientas simples, resultados potentes.</p>
    </div>
    <div class="row g-4">
      <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
        <div class="card h-100 p-3 shadow-deep hover-lift">
          <div class="card-body">
            <div class="feature-icon mb-3"><i class="bi bi-list-task"></i></div>
            <h5 class="card-title">Listas y proyectos</h5>
            <p class="card-text text-secondary">Organiza por áreas, proyectos o contextos. Arrastra, suelta y prioriza con facilidad.</p>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
        <div class="card h-100 p-3 shadow-deep hover-lift">
          <div class="card-body">
            <div class="feature-icon mb-3"><i class="bi bi-bell-fill"></i></div>
            <h5 class="card-title">Recordatorios inteligentes</h5>
            <p class="card-text text-secondary">Recibe avisos en el momento preciso para no olvidar entregas ni reuniones.</p>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="300">
        <div class="card h-100 p-3 shadow-deep hover-lift">
          <div class="card-body">
            <div class="feature-icon mb-3"><i class="bi bi-clock-fill"></i></div>
            <h5 class="card-title">Fechas y prioridades</h5>
            <p class="card-text text-secondary">Define vencimientos, categorías y prioridades para enfocar tu energía donde importa.</p>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="400">
        <div class="card h-100 p-3 shadow-deep hover-lift">
          <div class="card-body">
            <div class="feature-icon mb-3"><i class="bi bi-tags-fill"></i></div>
            <h5 class="card-title">Etiquetas y filtros</h5>
            <p class="card-text text-secondary">Categoriza tareas con etiquetas personalizadas y usa filtros para ver lo que necesitas al instante. Control total sobre tu lista.</p>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="500">
        <div class="card h-100 p-3 shadow-deep hover-lift">
          <div class="card-body">
            <div class="feature-icon mb-3"><i class="bi  bi-calendar-week-fill"></i></div>
            <h5 class="card-title">Vista de calendario</h5>
            <p class="card-text text-secondary">Visualiza tus tareas y eventos en un calendario. Arrastra y suelta para reorganizar tu semana y planifica tus días de forma intuitiva.</p>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="600">
        <div class="card h-100 p-3 shadow-deep hover-lift">
          <div class="card-body">
            <div class="feature-icon mb-3"><i class="bi bi-lock-fill"></i></div>
            <h5 class="card-title">Seguridad</h5>
            <p class="card-text text-secondary">Protección de cuentas con contraseñas cifradas y buenas prácticas desde el primer día.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="how" class="py-5 bg-body-secondary">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
      <h2 class="fw-bold">¿Cómo funciona?</h2>
      <p class="text-secondary">De cero a productivo en 3 pasos.</p>
    </div>
    <div class="row g-4 justify-content-center">
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
        <div class="p-4 bg-white rounded-3 shadow-deep h-100 text-center position-relative">
          <div class="feature-icon mb-3"><i  class="bi bi-pencil-fill fs-4 text-primary"></i></div>
          <h5>Anota todo</h5>
          <p class="text-secondary mb-0">Captura tareas, ideas y pendientes. No te preocupes por el orden al principio.</p>
        </div>
      </div>
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
        <div class="p-4 bg-white rounded-3 shadow-deep h-100 text-center position-relative">
          <div class="feature-icon mb-3"><i class="bi bi-bullseye  fs-4 text-primary"></i></div>
          <h5>Prioriza</h5>
          <p class="text-secondary mb-0">Agrupa por proyectos, añade vencimientos y prioridades. Claridad instantánea.</p>
        </div>
      </div>
      <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
        <div class="p-4 bg-white rounded-3 shadow-deep h-100 text-center position-relative">
          <div class="feature-icon mb-3"><i class="bi bi-rocket-fill  fs-4 text-primary"></i></div>
          <h5>Ejecuta</h5>
          <p class="text-secondary mb-0">Enfócate en lo de hoy, recibe recordatorios y marca avances. Siente el progreso.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
      <h2 class="fw-bold">Historias de usuarios</h2>
      <p class="text-secondary">Personas que ya organizan su día de manera más efectiva.</p>
    </div>
    <div class="row g-4">
      <div class="col-md-6" data-aos="fade-right">
        <div class="card shadow-deep h-100 hover-lift testimonial-card">
          <div class="card-body">
            <p class="testimonial-quote">“Con Chronos, mis proyectos ya no son un caos. Ahora sé exactamente qué hacer, y cuándo.”</p>
            <div class="d-flex align-items-center mt-3">
              <div class="testimonial-avatar me-3"></div>
              <div>
                <div class="fw-semibold">Alejandro, Diseñador UX</div>
                <div class="small text-secondary">Estudio Creativo</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-6" data-aos="fade-left">
        <div class="card shadow-deep h-100 hover-lift testimonial-card">
          <div class="card-body">
            <p class="testimonial-quote">“Me ayudó a separar tareas por contextos: universidad, trabajo y personal. Mucho menos estrés.”</p>
            <div class="d-flex align-items-center mt-3">
              <div class="testimonial-avatar me-3"></div>
              <div>
                <div class="fw-semibold">Sofía, Estudiante</div>
                <div class="small text-secondary">Universitaria</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="pricing" class="pricing py-5 bg-body">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
      <h2 class="fw-bold">Planes simples</h2>
      <p class="text-secondary">Empieza gratis. Mejora cuando lo necesites.</p>
    </div>
    <div class="row g-4 align-items-stretch">
      <div class="col-md-6" data-aos="fade-right">
        <div class="card h-100 p-3 shadow-deep hover-lift">
          <div class="card-body p-4">
            <h5 class="card-title">Gratis</h5>
            <p class="display-6 fw-bold mb-0">Q0</p>
            <p class="text-secondary">Para uso personal</p>
            <ul class="list-unstyled pricing-list">
              <li class="mb-2"><i class="bi bi-check-lg me-2 text-primary-accent"></i> Tareas y proyectos ilimitados</li>
              <li class="mb-2"><i class="bi bi-check-lg me-2 text-primary-accent"></i> Recordatorios básicos</li>
              <li class="mb-2"><i class="bi bi-check-lg me-2 text-primary-accent"></i> 1 colaborador por proyecto</li>
            </ul>
            <a href="register.php" class="btn btn-primary-outline w-100">Comenzar</a>
          </div>
        </div>
      </div>
      <div class="col-md-6" data-aos="fade-left">
        <div class="card h-100 p-3 shadow-deep border-primary-accent hover-lift position-relative">
          <div class="badge-popular">Más popular</div>
          <div class="card-body p-4">
            <h5 class="card-title">Pro</h5>
            <p class="display-6 fw-bold mb-0">Q39<span class="fs-6 text-secondary">/mes</span></p>
            <p class="text-secondary">Para equipos y power users</p>
            <ul class="list-unstyled pricing-list">
              <li class="mb-2"><i class="bi bi-check-lg me-2 text-primary-accent"></i> Recordatorios avanzados y repetitivos</li>
              <li class="mb-2"><i class="bi bi-check-lg me-2 text-primary-accent"></i> Colaboración ilimitada</li>
              <li class="mb-2"><i class="bi bi-check-lg me-2 text-primary-accent"></i> Paneles y reportes</li>
              <li class="mb-2"><i class="bi bi-check-lg me-2 text-primary-accent"></i> Soporte prioritario</li>
            </ul>
            <a href="register.php" class="btn btn-info text-white btn-lg w-100">Probar Pro</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="faq" class="py-5 bg-body-secondary">
  <div class="container">
    <div class="text-center mb-5" data-aos="fade-up">
      <h2 class="fw-bold">Preguntas frecuentes</h2>
      <p class="text-secondary">Respuestas rápidas antes de empezar.</p>
    </div>
    <div class="accordion accordion-flush" id="faqAcc" data-aos="fade-up" data-aos-delay="200">
      <div class="accordion-item shadow-deep rounded-3 mb-3">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#f1">
            ¿Necesito tarjeta de crédito para empezar?
          </button>
        </h2>
        <div id="f1" class="accordion-collapse collapse" data-bs-parent="#faqAcc">
          <div class="accordion-body">
            No. Crea tu cuenta gratis y prueba Chronos sin compromiso.
          </div>
        </div>
      </div>
      <div class="accordion-item shadow-deep rounded-3 mb-3">
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
      <div class="accordion-item shadow-deep rounded-3 mb-3">
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
      <div class="accordion-item shadow-deep rounded-3 mb-3">
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

<footer class="py-4 border-top">
  <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
    <div class="small text-secondary">© <span id="year"></span> Chronos. Todos los derechos reservados.</div>
    <div class="small mt-3 mt-md-0">
      <a href="#" class="text-decoration-none me-3 text-secondary hover-primary">Privacidad</a>
      <a href="#" class="text-decoration-none me-3 text-secondary hover-primary">Términos</a>
      <a href="login.php" class="text-decoration-none text-secondary hover-primary">Iniciar sesión</a>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init({
    duration: 800,
    once: true,
  });
  document.getElementById('year').textContent = new Date().getFullYear();
</script>
</body>
</html>