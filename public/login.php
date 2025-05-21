<?php
require_once '../config/db.php';
session_start();

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo   = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM personas WHERE correo = ?");
    $stmt->execute([$correo]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['contraseña'])) {
        $_SESSION['usuario'] = [
            'id'   => $user['id'],
            'rol'  => $user['rol'],
            'nombre' => $user['nombre']
        ];

        if ($user['rol'] === 'admin') {
            header('Location: ../admin/panel.php');
        } else {
            header('Location: ../cliente/panel.php');
        }
        exit;
    } else {
        $mensaje = 'Correo o contraseña incorrectos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Iconos de Frepiz (remixicon) -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <!-- Fuente Poppins de Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            padding: 0;
            position: relative;
            font-family: 'Poppins', Arial, Helvetica, sans-serif;
        }
        .bg-img {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            width: 100vw;
            height: 100vh;
            background: url('../assets/img/LoginImg.jpg') no-repeat center center fixed;
            background-size: cover;
            z-index: 1;
        }
        .bg-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            width: 100vw;
            height: 100vh;
            background: rgb(2 22 80 / 71%);
            z-index: 2;
        }
        .login-container {
            position: relative;
            z-index: 3;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: rgba(255,255,255,0.97);
            border-radius: 24px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.2);
            padding: 2.5rem 3.5rem 2rem 3.5rem;
            max-width: 520px;
            width: 100%;
        }
        .login-card h2 {
            font-weight: 700;
            margin-bottom: 2rem;
            text-align: center;
            color: #2a3b6e;
            letter-spacing: 1px;
        }
        .input-group-text {
            background: #f0f0f0;
            border-radius: 10px 0 0 10px;
            border: none;
            font-size: 1.5rem;
            color: #4a90e2;
            padding-right: 0.8rem;
            padding-left: 0.8rem;
        }
        .form-control {
            border-radius: 0 10px 10px 0;
            font-size: 1.1rem;
            border-left: none;
            font-family: 'Poppins', Arial, Helvetica, sans-serif;
        }
        .input-group {
            box-shadow: 0 2px 8px rgba(74,144,226,0.07);
        }
        .btn-login {
            background: #4a90e2;
            color: #fff;
            font-weight: 600;
            border-radius: 10px;
            font-size: 1.15rem;
            padding: 0.85rem 0;
            margin-top: 1.2rem;
            transition: background 0.2s;
            letter-spacing: 1px;
            font-family: 'Poppins', Arial, Helvetica, sans-serif;
        }
        .btn-login:hover {
            background: #357ab8;
            color: #fff;
        }
        .alert {
            border-radius: 10px;
            font-family: 'Poppins', Arial, Helvetica, sans-serif;
        }
        .register-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: #4a90e2;
            text-decoration: none;
            font-weight: 500;
            font-size: 1.05rem;
            font-family: 'Poppins', Arial, Helvetica, sans-serif;
        }
        .register-link:hover {
            text-decoration: underline;
            color: #357ab8;
        }
        @media (max-width: 600px) {
            .login-card {
                padding: 2rem 1rem;
                max-width: 98vw;
            }
        }
    </style>
</head>
<body>
    <div class="bg-img"></div>
    <div class="bg-overlay"></div>
    <div class="login-container">
        <div class="login-card">
            <h2>Iniciar sesión</h2>
            <form method="POST" autocomplete="off">
                <div class="mb-4">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-mail-line"></i></span>
                        <input type="email" id="email" name="email" class="form-control" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-lock-2-line"></i></span>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-login w-100">Acceder</button>
            </form>
            <a href="registro.php" class="register-link">¿Necesitas crear tu cuenta?</a>
            <?php if ($mensaje): ?>
                <div class="alert alert-danger mt-3 text-center"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>