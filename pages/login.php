<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: /automl/dashboard");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AutoML App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/automl/src/css/login.css">
    <link rel="icon" href="/automl/src/img/favicon.ico" type="image/x-icon">
</head>
<body class="bg-light">

<div class="container-fluid vh-100">
    <div class="row h-100">
         
        <div class="col-lg-6 d-flex flex-column align-items-center min-vh-100 py-5 login-bg-mobile">
    
    <div class="logo mb-auto"> 
        <a class="navbar-brand mobile_logo fw-bold fs-4" href="/automl">
            <img class="imgicon" src="/automl/src/img/logo2.png" alt="Logo" width="100" class="me-2">
            <span class="logo-color">Auto<span class="text-primary">ML</span></span>
        </a>
    </div>

    <div class="w-75 mobile-form mt-auto mb-auto">
        <h2 class="text-center mb-4">Login</h2>

        <div id="loginAlert"></div>

        <form id="loginForm">
            <div class="mb-3">
                <label for="loginEmail" class="form-label">Email</label>
                <input type="email" id="loginEmail" class="form-control" placeholder="name@example.com" required>
            </div>

            <div class="mb-3">
                <label for="loginPass" class="form-label">Password</label>
                <input type="password" id="loginPass" class="form-control" placeholder="Enter password" required>
            </div>

            <button type="button" id="loginBtn" class="btn btn-primary w-100 py-2 fw-semibold">
                Login
            </button>
        </form>

        <div class="d-flex align-items-center">
            <hr class="flex-grow-1 text-muted opacity-25">
            <span class="mx-3 text-muted small fw-semibold">OR</span>
            <hr class="flex-grow-1 text-muted opacity-25">
        </div>

    <a href="https://accounts.google.com/o/oauth2/v2/auth?client_id=522622427401-el773rgtmqot42obrfslmi9ruu9896uu.apps.googleusercontent.com&redirect_uri=https://kclusterhub.iee.ihu.gr/automl/server/php/google-callback.php&response_type=code&scope=profile%20email" class="btn btn-outline-dark w-100 py-2 d-flex align-items-center justify-content-center fw-semibold rounded-3">
    <i class="bi bi-google me-2"></i> Continue with Google
</a>
        <div class="mt-4 text-center">
            <p class="mb-0">
                Don’t have an account?
                <a href="/automl/register" class="text-decoration-none">Sign Up</a>
            </p>
            <p class="mb-0">
                Forgot password?
                <a href="/automl/forgot-password" class="text-decoration-none">Reset</a>
            </p>
        </div>
       
    </div>

    <div class="mt-auto" style="height: 40px;"></div>
</div>

        <div class="col-lg-6 d-none d-lg-block p-0">
            <div class="login-bg h-100">
                <div class="login-banner"><h1 class="display-1 fw-bolder mb-4 tracking-tight">
                    All your AutoML tools in one place. <br> 
                </h1>

                <p class="lead text-muted mb-5 max-width-700 fs-4">
                  Fast, simple, and powerful model selection.
                </p>
                </div>
            </div>
        </div>

    </div>
</div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="/automl/src/js/login.js"></script>
</body>
</html>