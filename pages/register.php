<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - AutoML App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/automl/src/css/login.css">
    <link rel="icon" href="/automl/src/img/favicon.ico" type="image/x-icon">
</head>
<body class="bg-light">

    <div class="container-fluid vh-100">
    <div class="row h-100 flex-lg-row-reverse">
         

        <!-- ΑΡΙΣΤΕΡΑ: FORM -->
        <div class="col-lg-6 d-flex flex-column align-items-center min-vh-100 py-4 login-bg-mobile">
    
    <div class="logo mb-auto"> 
        <a class="navbar-brand mobile_logo fw-bold fs-4" href="/automl">
            <img class="imgicon" src="/automl/src/img/logo2.png" alt="Logo" width="100" class="me-2">
            <span class="logo-color">Auto<span class="text-primary">ML</span></span>
        </a>
    </div>

    <div class="w-75 mt-auto mb-auto mobile-form">
        <h2 class="text-center mb-4">Create Account</h2>
        
        <div id="alertPlaceholder"></div> 

        <form id="registerForm">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" id="fname" class="form-control" placeholder="e.g. John" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" id="lname" class="form-control" placeholder="e.g. Smith">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" id="email" class="form-control" placeholder="name@example.com" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" id="pass" class="form-control" placeholder="At least 8 characters" required>
                <small class="text-muted">Must contain an uppercase letter, a lowercase letter, a number, and a symbol.</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" id="pass_confirm" class="form-control" placeholder="Re-enter Password" required>
            </div>

            <button type="button" id="regbtn" class="btn btn-primary w-100 py-2">Sign Up</button>
        </form>
        
        <div class="mt-4 text-center">
            <p class="mb-0">Already have an account? <a href="/automl/login" class="text-decoration-none">Sign In</a></p>
        </div>
    </div>

    <div class="mt-auto" style="height: 20px;"></div>
</div>
        <!-- ΔΕΞΙΑ: IMAGE -->
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
    


    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="/automl/src/js/register.js"></script>
</body>
</html>