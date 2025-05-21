<?php
require_once '../config/db.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre     = $_POST['nombre'];
    $apellido   = $_POST['apellido'];
    $correo     = $_POST['email'];
    $password   = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $empresa    = $_POST['empresa'];

    // Verificar si el email ya existe
    $stmt = $pdo->prepare("SELECT id FROM personas WHERE correo = ?");
    $stmt->execute([$correo]);

    if ($stmt->rowCount() > 0) {
        $mensaje = 'El correo ya está registrado.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO personas (nombre, apellido, correo, contraseña, rol, empresa) VALUES (?, ?, ?, ?, 'cliente', ?)");
        $stmt->execute([$nombre, $apellido, $correo, $password, $empresa]);
        $mensaje = 'Registro exitoso. Ya puedes iniciar sesión.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
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
            background: url('../assets/img/registrarse.jpg') no-repeat center center fixed;
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
        .register-container {
            position: relative;
            z-index: 3;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-card {
            background: rgba(255,255,255,0.97);
            border-radius: 24px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.2);
            padding: 2.5rem 3.5rem 2rem 3.5rem;
            max-width: 540px;
            width: 100%;
        }
        .register-card h2 {
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
        .btn-register {
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
        .btn-register:hover {
            background: #357ab8;
            color: #fff;
        }
        .alert {
            border-radius: 10px;
            font-family: 'Poppins', Arial, Helvetica, sans-serif;
        }
        .login-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: #4a90e2;
            text-decoration: none;
            font-weight: 500;
            font-size: 1.05rem;
            font-family: 'Poppins', Arial, Helvetica, sans-serif;
        }
        .login-link:hover {
            text-decoration: underline;
            color: #357ab8;
        }
        label {
            font-weight: 500;
            font-size: 1rem;
            margin-bottom: 0.3rem;
        }
        @media (max-width: 600px) {
            .register-card {
                padding: 2rem 1rem;
                max-width: 98vw;
            }
        }
    </style>
</head>
<body>
    <div class="bg-img"></div>
    <div class="bg-overlay"></div>
    <div class="register-container">
        <div class="register-card">
            <h2>Registro de Cliente</h2>
            <form method="POST" class="mt-4" autocomplete="off">
                <div class="mb-3">
                    <label>Nombre:</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-user-line"></i></span>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label>Apellido:</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-user-3-line"></i></span>
                        <input type="text" name="apellido" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label>Email:</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-mail-line"></i></span>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label>Contraseña:</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-lock-2-line"></i></span>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label>Empresa:</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ri-building-line"></i></span>
                        <input type="text" name="empresa" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-register w-100">Registrarse</button>
            </form>
            <a href="login.php" class="login-link">¿Ya tienes cuenta?</a>
            <?php if ($mensaje): ?>
                <div class="alert alert-info mt-3 text-center"><?= $mensaje ?></div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>