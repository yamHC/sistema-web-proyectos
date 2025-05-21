<?php
session_start();
require_once '../config/db.php';

// Verificar si está logueado y es cliente
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'cliente') {
    header('Location: ../public/login.php');
    exit();
}

// Obtener datos del cliente desde la sesión
$cliente_nombre = $_SESSION['usuario']['nombre'];
$cliente_foto = (isset($_SESSION['usuario']['foto']) && $_SESSION['usuario']['foto'])
    ? '../assets/img/' . $_SESSION['usuario']['foto']
    : '../assets/img/user-default.png';

// Obtener la lista de administradores
$stmt = $pdo->query("SELECT id, nombre, correo FROM personas WHERE rol = 'admin'");
$administradores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Manejar el envío de mensajes al administrador
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = $_POST['admin_id'];
    $asunto = $_POST['asunto'];
    $contenido = $_POST['contenido'];
    $cliente_id = $_SESSION['usuario']['id'];

    try {
        $stmt = $pdo->prepare("INSERT INTO mensajes (remitente_id, destinatario_id, asunto, contenido, enviado_en) 
                               VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$cliente_id, $admin_id, $asunto, $contenido]);

        echo json_encode(['status' => 'success', 'message' => 'Mensaje enviado correctamente al administrador.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error al enviar el mensaje: ' . $e->getMessage()]);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Enviar Mensaje</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            background: linear-gradient(120deg, #e0e7ff 0%, #f8fafc 100%);
            font-family: 'Poppins', Arial, Helvetica, sans-serif;
        }
        .sidebar, .sidebar a, .sidebar h4, .sidebar .logo-container, .content, .welcome-card, .dashboard-card, .dashboard-title, .dashboard-value, .dashboard-link {
            font-family: 'Poppins', Arial, Helvetica, sans-serif !important;
        }
        .sidebar {
            width: 380px;
            background: linear-gradient(180deg,rgb(0, 36, 134) 0%,rgb(2, 131, 217) 100%);
            color: #fff;
            padding: 40px 28px 28px 28px;
            min-height: 100vh;
            box-shadow: 2px 0 16px 0 rgba(30,41,59,0.08);
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .sidebar .logo-container {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 1.8rem;
        }
        .sidebar .logo-container img {
            max-height: 140px;
            border-radius: 12px;
            background: transparent;
            box-shadow: none;
            padding: 0;
        }
        .sidebar h4 {
            font-weight: 700;
            margin-bottom: 2.5rem;
            letter-spacing: 1px;
            text-align: center;
            font-size: 1.5rem;
            width: 100%;
        }
        .sidebar a {
            color: #e0e7ff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            border-radius: 10px;
            margin-bottom: 14px;
            font-size: 1.13rem;
            transition: background 0.18s;
            width: 100%;
        }
        .sidebar a:hover, .sidebar a.active {
            background: #2563eb;
            color: #fff;
        }
        .sidebar a:last-child {
            margin-top: 2.5rem;
            background: #f87171;
            color: #fff;
            font-weight: 600;
        }
        .sidebar a:last-child:hover {
            background: #dc2626;
        }
        .content {
            flex: 1;
            padding: 40px 40px 30px 40px;
            display: flex;
            flex-direction: column;
        }
        .welcome-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 24px 0 rgba(30,41,59,0.07);
            padding: 2.2rem 2rem 2rem 2rem;
            margin-bottom: 2.2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .welcome-card h2 {
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 0.5rem;
        }
        .welcome-card p {
            color: #64748b;
            font-size: 1.1rem;
            margin-bottom: 0;
        }
        .form-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px 0 rgba(30,41,59,0.07);
            padding: 2rem 2rem 1.5rem 2rem;
            max-width: 600px;
            margin: 0 auto;
        }
        .form-label {
            font-weight: 500;
            color: #2563eb;
        }
        .btn-enviar {
            background: #2563eb;
            color: #fff;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            transition: background 0.18s;
        }
        .btn-enviar:hover {
            background: #1d4ed8;
            color: #fff;
        }
        @media (max-width: 900px) {
            .content {
                padding: 20px 5px;
            }
            .sidebar {
                width: 100vw;
            }
        }
        @media (max-width: 600px) {
            .sidebar {
                display: none;
            }
            .content {
                padding: 10px 2px;
            }
            .welcome-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar izquierda -->
    <div class="sidebar">
        <div class="logo-container">
            <img src="../assets/img/logoBlanco.png" alt="Logo">
        </div>
        <h4>
            <i class="ri-user-line" style="font-size:1.5rem;vertical-align:middle;margin-right:8px;"></i>
            Panel del Cliente
        </h4>
        <a href="panel.php"><i class="ri-home-5-line"></i> Inicio</a>
        <a href="proyecto.php"><i class="ri-folder-2-line"></i> Mis Proyectos</a>
        <a href="mensaje.php" class="active"><i class="ri-mail-send-line"></i> Mensajes</a>
        <a href="recibir_mensaje_de_admin.php"><i class="ri-mail-unread-line"></i> Mensajes Recibidos</a>
        <a href="info.php"><i class="ri-user-settings-line"></i> Información</a>
        <a href="../public/login.php"><i class="ri-logout-box-r-line"></i> Cerrar Sesión</a>
    </div>
    <!-- Contenido principal -->

<div class="content d-flex align-items-center justify-content-center" style="min-height:100vh; padding:0;">
    <div class="form-card" style="background:#fff; border-radius:24px; box-shadow:0 8px 40px 0 rgba(30,41,59,0.13); width:100%; max-width:600px; margin:0 auto; padding:2.2rem 2.2rem 1.5rem 2.2rem;">
        <div class="text-center mb-4">
            <i class="ri-mail-send-line" style="font-size:2.2rem;color:#2563eb;"></i>
            <span style="font-size:2rem;font-weight:700;color:#2563eb;margin-left:8px;">Mensajería</span>
            <p class="mt-2 mb-0" style="color:#64748b;">Envía un mensaje a un administrador:</p>
        </div>
        <form id="mensajeForm">
            <div class="mb-3">
                <label for="admin_id" class="form-label" style="font-weight:600;">Seleccionar Administrador</label>
                <select name="admin_id" id="admin_id" class="form-control" required>
                    <option value="">Seleccionar Administrador</option>
                    <?php foreach ($administradores as $admin): ?>
                        <option value="<?= $admin['id'] ?>">
                            <?= htmlspecialchars($admin['nombre']) ?> (<?= htmlspecialchars($admin['correo']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="asunto" class="form-label" style="font-weight:600;">Asunto</label>
                <input type="text" name="asunto" id="asunto" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="contenido" class="form-label" style="font-weight:600;">Mensaje</label>
                <textarea name="contenido" id="contenido" class="form-control" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn" style="background:#2563eb;color:#fff;font-weight:600;font-size:1.1rem;border-radius:10px;min-width:180px;">
                <i class="ri-send-plane-2-line"></i> Enviar Mensaje
            </button>
        </form>
        <div id="respuesta" class="mt-3"></div>
    </div>
</div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('mensajeForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('mensaje.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const respuestaDiv = document.getElementById('respuesta');
                if (data.status === 'success') {
                    respuestaDiv.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                    document.getElementById('mensajeForm').reset();
                } else {
                    respuestaDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('respuesta').innerHTML = `<div class="alert alert-danger">Error al procesar la solicitud.</div>`;
            });
        });
    </script>
</body>
</html>