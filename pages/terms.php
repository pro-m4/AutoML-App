<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Use - AutoML App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/automl/src/css/index.css">
    <link rel="manifest" href="/automl/manifest.json">
    <meta name="theme-color" content="#01ABF5">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="/automl/src/img/favicon.ico" type="image/x-icon">
    <style>
        body { font-family: 'Instrument Sans', 'Inter', sans-serif; }
        .legal-content h1, .legal-content h2, .legal-content h3 { font-family: 'Inter', sans-serif; font-weight: 700; }
        .legal-content h1 { font-size: 2.5rem; margin-bottom: 1.5rem; }
        .legal-content h2 { font-size: 1.5rem; margin-top: 2rem; margin-bottom: 1rem; color: #212529; }
        .legal-content p, .legal-content li { color: #6c757d; line-height: 1.6; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom nav-height fixed-top">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="/automl/#hero">
            <img src="/automl/src/img/logo2.png" alt="Logo" height="40px" class="me-1" style="vertical-align:text-bottom!important;">
            Auto<span class="text-primary">ML</span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto d-none d-lg-flex">
                <li class="nav-item"><a class="nav-link fw-semibold px-3 text-dark" href="/automl/#features">Features</a></li>
                <li class="nav-item"><a class="nav-link fw-semibold px-3 text-dark" href="/automl/#how-it-works">How it Works</a></li>
                <li class="nav-item"><a class="nav-link fw-semibold px-3 text-dark" href="/automl/#about">About</a></li>
                <li class="nav-item"><a class="nav-link fw-semibold px-3 text-dark" href="https://forms.gle/sKCCVMGZoJHj6q1n6">Rate App</a></li>
            </ul>

            <ul class="navbar-nav d-lg-none text-center">
                <li class="nav-item py-2 border-bottom"><a class="nav-link text-dark" href="/automl/#features">Features</a></li>
                <li class="nav-item py-2 border-bottom"><a class="nav-link text-dark" href="/automl/#how-it-works">How it Works</a></li>
                <li class="nav-item py-2 border-bottom"><a class="nav-link text-dark" href="/automl/#about">About</a></li>
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

<main class="py-5 mt-5 legal-content">
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-9 bg-white p-5 rounded-3 shadow-sm">
                <h1 class="tracking-tight">Terms of Use for Auto<span class="text-primary">ML</span> App</h1>
                <p class="text-muted mb-4"><strong>Last Updated:</strong> May 2026</p>
                <div class="mb-4">
                    <span class="badge rounded-pill bg-light text-dark border px-3 py-2 fw-normal">
                        Terms & Conditions
                    </span>
                </div>
                <hr class="my-4 text-muted opacity-25">

                <p>Welcome to the AutoML Platform ("the App"). By accessing or using this web application hosted at <code>https://kclusterhub.iee.ihu.gr/automl/</code>, you agree to be bound by these Terms of Use.</p>

                <h2>1. Academic Purpose & Scope</h2>
                <p>This platform is a non-commercial web application created exclusively for academic research purposes and evaluation as part of a Diploma Thesis at the International Hellenic University (IHU). Any commercial deployment or exploitation is strictly prohibited.</p>

                <h2>2. User Account and Authentication</h2>
                <ul>
                    <li>To utilize the machine learning pipeline, users must log in via Google Authentication.</li>
                    <li>You are responsible for maintaining the confidentiality of your active session.</li>
                    <li>The application evaluates automated model selection and benchmarking using institutional infrastructure. Any malicious use or attempts to overload the server resources will lead to immediate account termination.</li>
                </ul>

                <h2>3. Uploaded Content and Machine Learning Data</h2>
                <ul>
                    <li>Users can upload raw data in CSV format to train models using integrated AutoML frameworks (<strong>FLAML, H2O, and MLJAR</strong>).</li>
                    <li>You retain all intellectual property rights to the datasets you upload.</li>
                    <li>The platform provides automated predictions and metric-driven selections (Accuracy, F1-Score, RMSE) for evaluation purposes. The underlying software is provided "as-is", and we do not guarantee absolute model correctness or production-level stability for external non-academic deployment.</li>
                </ul>

                <h2>4. Limitation of Liability</h2>
                <p>The developer (Prodromos Vezyridis) and the thesis supervisor (Prof. Stefanos Ougiaroglou) shall not be held liable for any data loss, computational errors, server downtime, or inaccurate model metrics generated through the automated processing engine.</p>

                <h2>5. Contact Information</h2>
                <p>For questions regarding the Terms of Use, the data workflows, or the underlying academic research, please contact the Department of Information and Electronic Engineering at IHU.</p>
            </div>
        </div>
    </div>
</main>

<footer class="py-5 border-top text-center text-muted bg-white">
    <div class="container">
        <p class="small mb-2">&copy; 2026 Diploma Thesis Project. Created for academic research purposes.</p>
        <div class="small">
            <a href="/automl/privacy" class="text-decoration-none text-muted me-3">Privacy Policy</a>
            <a href="/automl/terms" class="text-decoration-none text-muted">Terms of Use</a>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/@studio-freight/lenis@1.0.42/dist/lenis.min.js"></script>
<script>
  (function () {
    if (window.__lenis__) return;
    try { document.documentElement.style.scrollBehavior = 'auto'; } catch(e){}
    const lenis = new Lenis({ duration: 1.2, easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)), smoothWheel: true });
    function raf(time) { lenis.raf(time); requestAnimationFrame(raf); }
    requestAnimationFrame(raf);
    window.__lenis__ = lenis;
  })();
</script>
</body>
</html>