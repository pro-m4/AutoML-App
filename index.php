<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoML App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/automl/src/css/index.css">
    <link rel="manifest" href="/automl/manifest.json">
    <meta name="theme-color" content="#01ABF5">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="/automl/src/img/favicon.ico" type="image/x-icon">
</head>
<body>

    <<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom nav-height fixed-top">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="#hero">
            <img src="/automl/src/img/logo2.png" alt="Logo" height="40px" class="me-1" style="vertical-align:text-bottom!important;">
            Auto<span class="text-primary">ML</span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            
            <ul class="navbar-nav mx-auto d-none d-lg-flex">
                <li class="nav-item"><a class="nav-link fw-semibold px-3 text-dark" href="#features">Features</a></li>
                <li class="nav-item"><a class="nav-link fw-semibold px-3 text-dark" href="#how-it-works">How it Works</a></li>
                <li class="nav-item"><a class="nav-link fw-semibold px-3 text-dark" href="#about">About</a></li>
                <li class="nav-item"><a class="nav-link fw-semibold px-3 text-dark" href="https://forms.gle/sKCCVMGZoJHj6q1n6">Rate App</a></li>
            </ul>

            <ul class="navbar-nav d-lg-none text-center">
                <li class="nav-item py-2 border-bottom"><a class="nav-link text-dark" href="#features">Features</a></li>
                <li class="nav-item py-2 border-bottom"><a class="nav-link text-dark" href="#how-it-works">How it Works</a></li>
                <li class="nav-item py-2 border-bottom"><a class="nav-link text-dark" href="#about">About</a></li>
                <li class="nav-item py-2"><a class="nav-link text-dark" href="https://forms.gle/sKCCVMGZoJHj6q1n6">Rate App</a></li>
                
                <div class="d-grid gap-2 py-2 border-bottom" style="grid-auto-flow: column; grid-auto-columns: 1fr; order:-1;">
                    <a href="/automl/login" class="btn btn-outline-dark rounded-3">Login</a>
                    <a href="/automl/register" class="btn btn-dark rounded-3">Sign Up</a>
                </div>
            </ul>

            <div class="d-none d-lg-flex align-items-center">
                <a href="/automl/login" class="btn btn-link text-decoration-none text-dark fw-semibold me-2">Login</a>
                <a href="/automl/register" class="btn btn-dark px-4 rounded-3">Sign Up</a>
            </div>

        </div>
    </div>
</nav>

    <main id="hero" class="hero-vh">
    <div class="container">
        <div class="row align-items-center">

            <!-- ΑΡΙΣΤΕΡΑ: ΕΙΚΟΝΑ -->
            <div class="col-lg-6 text-center">
                <img src="/automl/src/img/Frame.jpg" class="img-fluid" alt="Hero Image">
            </div>

            <!-- ΔΕΞΙΑ: ΠΕΡΙΕΧΟΜΕΝΟ -->
            <div class="col-lg-6 text-center text-lg-start">
                
                <div class="mb-4">
                    <span class="badge rounded-pill bg-light text-dark border px-3 py-2 fw-normal">
                        New: Support for AutoML Frameworks
                    </span>
                </div>

                <h1 class="display-1 fw-bolder mb-4 tracking-tight">
                    Train models <br> 
                    <span class="text-primary">in seconds.</span>
                </h1>

                <p class="lead text-muted mb-5 max-width-700 fs-4">
                    A unified platform for FLAML, H2O, and MLJAR. Upload your data and let AutoML find the best algorithm.
                </p>

                <div class="mobile-buttons d-flex justify-content-center justify-content-lg-start gap-3">
                    <a href="/automl/register" class="btn btn-primary btn-lg px-lg-5 py-3 rounded-3 fw-bold">
                        Sign Up for Free
                    </a>
                    <a href="#how-it-works" class="btn btn-outline-secondary btn-lg px-lg-5 py-3 rounded-3 border-0">
                        How it works <i class="bi bi-arrow-down ms-1"></i>
                    </a>
                </div>

            </div>

        </div>
    </div>
</main>
    <section id="features" class="py-5 bg-light">
    <div class="container py-5">
        
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">Powerful Features</h2>
            <p class="lead text-muted mx-auto" style="max-width: 700px;">
                Everything you need to go from raw data to production-ready models in record time.
            </p>
        </div>

        <div class="row g-4">
            
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4">
                    <div class="mb-3 text-primary">
                        <i class="bi bi-robot fs-1"></i>
                    </div>
                    <h4 class="fw-bold">AutoML Engine</h4>
                    <p class="text-muted">
                        Leverage industry-leading frameworks like <strong>FLAML, H2O, and MLJAR</strong>. Our system automatically picks the best algorithm tailored to your dataset's unique characteristics.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4">
                    <div class="mb-3 text-primary">
                        <i class="bi bi-trophy fs-1"></i>
                    </div>
                    <h4 class="fw-bold">Metric-Driven Selection</h4>
                    <p class="text-muted">
                        You define success. Whether it's <strong>Accuracy, F1-Score, or RMSE</strong>, the platform optimizes and delivers the "champion" model based on your chosen metric.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm p-4">
                    <div class="mb-3 text-primary">
                        <i class="bi bi-sliders fs-1""></i>
                    </div>
                    <h4 class="fw-bold">Instant Predictions</h4>
                    <p class="text-muted">
                        Don't just train—deploy. Use your best-performing model immediately to generate predictions on new data directly through our web interface.
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>
<section id="how-it-works" class="py-5 bg-white">
    <div class="container py-5">
        
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">How it works</h2>
            <p class="lead text-muted mx-auto" style="max-width: 700px;">
                From raw data to the perfect model in three simple steps. No coding, no manual tuning—just powerful AutoML.
            </p>
        </div>

        <div class="row g-4 text-center">
            
            <div class="col-md-4">
                <div class="p-4 h-100">
                    <div class="mb-4 d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle shadow-sm" style="width: 80px; height: 80px;">
                        <i class="bi bi-file-earmark-spreadsheet fs-2"></i>
                    </div>
                    <h3 class="h4 fw-bold">1. Upload & Map</h3>
                    <p class="text-muted">
                        Upload your CSV file and define your workflow. Select your <strong>Target</strong> column and choose the specific <strong>Features</strong> you want the model to learn from.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="p-4 h-100 border-start border-end border-light">
                    <div class="mb-4 d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle shadow-sm" style="width: 80px; height: 80px;">
                        <i class="bi bi-sliders2 fs-2"></i>
                    </div>
                    <h3 class="h4 fw-bold">2. Configure AutoML</h3>
                    <p class="text-muted">
                        Set your training time in seconds, pick your metric, and select your preferred frameworks from <strong>H2O, FLAML, or MLJAR</strong>. Our engine handles the rest.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="p-4 h-100">
                    <div class="mb-4 d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle shadow-sm" style="width: 80px; height: 80px;">
                        <i class="bi bi-magic fs-2"></i>
                    </div>
                    <h3 class="h4 fw-bold">3. Train & Predict</h3>
                    <p class="text-muted">
                        Once training completes, the system identifies the champion model. Use it immediately to upload new data and generate real-time predictions directly in the app.
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>

<section id="about" class="py-5 bg-light">
    <div class="container py-5">
        <div class="row align-items-center">
            
            <div class="col-lg-7 text-start">
                <h2 class="display-5 fw-bold mb-4">About the Project</h2>
                <p class="lead text-dark mb-4">
                    This web application was developed by <strong>Prodromos Vezyridis</strong> as part of a <strong>Diploma Thesis</strong> at the International Hellenic University.
                </p>
                <p class="text-muted mb-4">
                    The research focuses on the democratization of Machine Learning through a unified AutoML interface. By integrating <strong>FLAML, H2O, and MLJAR</strong>, the platform evaluates automated model selection and performance benchmarking, making complex Data Science workflows accessible to everyone.
                </p>
                
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-primary me-2"></i>
                            <span>Benchmarking Analysis</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-primary me-2"></i>
                            <span>Unified Web Interface</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-primary me-2"></i>
                            <span>Performance Optimization</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-primary me-2"></i>
                            <span>Explainable AI (XAI)</span>
                        </div>
                    </div>
                </div>

                <p class="small text-muted">
                    <i class="bi bi-info-circle me-1"></i> Special thanks to <strong>Petros Topouzelis</strong> for his contribution to the logo and UI/UX design.
                </p>
            </div>

            <div class="col-lg-5 mt-5 mt-lg-0">
                <div class="card border-0 shadow-sm p-4 bg-white">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Thesis Details</h5>
                    <ul class="list-unstyled">
                          <li class="mb-3">
    <small class="text-uppercase text-muted d-block">Institution</small>
    <strong>International Hellenic University (IHU)</strong>
    <a href="https://www.iee.ihu.gr/" target="_blank" class="text-decoration-none d-block small text-muted">
        Dept. of Information and Electronic Engineering
    </a>
</li>
                        <li class="mb-3">
                            <small class="text-uppercase text-muted d-block">Supervisor</small>
                            <strong>Prof. Stefanos Ougiaroglou</strong>
                        </li>
                        <li class="mb-3">
                            <small class="text-uppercase text-muted d-block">Developer</small>
                            <strong>Prodromos Vezyridis</strong>
                        </li>
                        <li class="mb-0">
                            <small class="text-uppercase text-muted d-block">Keywords</small>
                            <div class="mt-2">
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">AutoML</span>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">Python</span>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">PHP</span>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">JavaScript</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</section>

    



    <footer class="py-5 border-top text-center text-muted">
        <div class="container">
            <p class="small">&copy; 2026 Diploma Thesis Project. Created for academic research purposes.</p>
        </div>
    </footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/@studio-freight/lenis@1.0.42/dist/lenis.min.js"></script>


 
<script src="https://cdn.jsdelivr.net/npm/@studio-freight/lenis"></script>

<script>
  (function () {
    
    if (window.__lenis__) return;

    
    const el = document.querySelector('#brxe-bogwsp');
    const ds = el ? el.dataset : {};

    // Μετατροπές/προεπιλογές
    const duration         = parseFloat(ds.duration) || 1.8;
    const smoothTouch      = ds.smoothtouch === '1';
    const wheelMultiplier  = parseFloat(ds.wheelmultiplier) || 1;
    const touchMultiplier  = parseFloat(ds.touchmultiplier) || 1;
    const infinite         = ds.infinite === '1';

    // ΜΗΝ αφήνεις CSS smooth-behavior να συγκρούεται με Lenis
    try { document.documentElement.style.scrollBehavior = 'auto'; } catch(e){}

    // Φτιάξε το Lenis
    const lenis = new Lenis({
      duration,
      easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
      smoothWheel: true,
      smoothTouch,
      wheelMultiplier,
      touchMultiplier,
      infinite
    });

    // RAF loop
    function raf(time) {
      lenis.raf(time);
      requestAnimationFrame(raf);
    }
    requestAnimationFrame(raf);

    // Κάν' το global για να μην διπλο-δημιουργείται
    window.__lenis__ = lenis;

    // Προαιρετικό: smooth scroll στα #anchors μέσω Lenis (αντί για default)
    document.addEventListener('click', function (e) {
      const a = e.target.closest('a[href^="#"]');
      if (!a) return;
      const id = a.getAttribute('href');
      const target = id && document.querySelector(id);
      if (target) {
        e.preventDefault();
        window.__lenis__.scrollTo(target, { offset: 0 });
      }
    }, { passive: false });
  })();
</script>

</body>
</html>