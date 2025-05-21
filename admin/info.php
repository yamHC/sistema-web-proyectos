<?php
session_start();
require_once '../config/db.php';

// Verificar si está logueado
if (!isset($_SESSION['usuario'])) {
    header('Location: ../public/login.php');
    exit();
}

$usuario_id = $_SESSION['usuario']['id'];
$mensaje = "";

// Obtener datos actuales del usuario
$stmt = $pdo->prepare("SELECT nombre, apellido, correo, rol, creado_en, foto FROM personas WHERE id = ?");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    $_SESSION['mensaje'] = 'Error: Usuario no encontrado.';
    header('Location: ../public/login.php');
    exit();
}

// Inicializar variables con los datos actuales
$nombre = $usuario['nombre'];
$apellido = $usuario['apellido'];
$correo = $usuario['correo'];
$foto = $usuario['foto'];

// Procesar actualización de datos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_info'])) {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $correo = $_POST['correo'];

    // Procesar imagen si se subió
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto = 'foto_' . $usuario_id . '_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], '../assets/img/' . $foto);

        $stmt = $pdo->prepare("UPDATE personas SET nombre=?, apellido=?, correo=?, foto=? WHERE id=?");
        $stmt->execute([$nombre, $apellido, $correo, $foto, $usuario_id]);
        $_SESSION['usuario']['foto'] = $foto;
    } else {
        $stmt = $pdo->prepare("UPDATE personas SET nombre=?, apellido=?, correo=? WHERE id=?");
        $stmt->execute([$nombre, $apellido, $correo, $usuario_id]);
    }

    $_SESSION['usuario']['nombre'] = $nombre;
    $_SESSION['usuario']['apellido'] = $apellido;
    $_SESSION['usuario']['correo'] = $correo;

    $mensaje = "¡Se editó correctamente!";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Información Personal</title>
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
        .sidebar, .sidebar a, .sidebar h4, .sidebar .logo-container {
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
            /* max-width: 120px; */
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
            min-height: 100vh;
            padding: 0;
            display: flex;
            align-items: stretch;
            justify-content: center;
            background: linear-gradient(120deg, #e0e7ff 0%, #f8fafc 100%);
        }
        .info-card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 8px 40px 0 rgba(30,41,59,0.13);
            width: 100%;
            max-width: 630px;
            min-height: 340px;
            height: auto;
            margin: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            position: relative;
            padding: 1.7rem 1.2rem 1.2rem 1.2rem;
            transition: box-shadow 0.2s;
        }
        .profile-img {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 50%;
            border: 1px solid #2563eb;
            margin-bottom: 0.7rem;
            background: #f1f5f9;
            box-shadow: 0 2px 8px 0 rgba(30,41,59,0.10);
        }
        .info-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 1.1rem;
            margin-top: 0.2rem;
            text-align: center;
        }
        .info-fields {
            width: 100%;
            margin-top: 0.7rem;
            margin-bottom: 1.2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem 0;
        }
        .info-field {
            margin-bottom: 0;
        }
        .info-label {
            font-size: 1rem;
            color: #2563eb;
            font-weight: 600;
            margin-bottom: 0.2rem;
            display: block;
            text-align: left;
        }
        .info-value {
            width: 100%;
            max-width: 100%;
            min-width: 0;
            font-size: 1rem;
            color: #222;
            background: #f8fafc;
            border-radius: 10px;
            padding: 0.6rem 0.9rem;
            border: 1.2px solid #e0e7ff;
            box-shadow: 0 1px 4px 0 rgba(30,41,59,0.04);
            text-align: left;
            font-family: 'Poppins', Arial, Helvetica, sans-serif;
        }
        .btn-editar {
            background: #2563eb;
            color: #fff;
            font-weight: 600;
            border-radius: 10px;
            font-size: 1.05rem;
            padding: 0.7rem 2rem;
            margin-top: 0.7rem;
            transition: background 0.2s;
        }
        .btn-editar:hover {
            background: #0f2027;
            color: #fff;
        }
        /* PANEL LATERAL DE EDICIÓN MÁS ANCHO Y COMPACTO */
        .edit-panel {
            position: fixed;
            top: 0;
            right: -100vw;
            width: 410px;
            max-width: 100vw;
            height: 100vh;
            background: #fff;
            box-shadow: -4px 0 24px 0 rgba(30,41,59,0.13);
            z-index: 9999;
            transition: right 0.4s cubic-bezier(.4,0,.2,1);
            padding: 1.7rem 1.5rem 1.5rem 1.5rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .edit-panel.active {
            right: 0;
        }
        .edit-panel h5 {
            font-size: 1.18rem;
            color: #2563eb;
            font-weight: 700;
            margin-bottom: 1.1rem;
            text-align: center;
        }
        .edit-panel form {
            width: 100%;
            max-width: 320px;
            margin: 0 auto;
        }
        .edit-panel .mb-3 {
            margin-bottom: 1.2rem !important;
        }
        .edit-panel .form-label {
            font-size: 1rem;
            color: #2563eb;
            font-weight: 600;
            margin-bottom: 0.3rem;
            letter-spacing: 0.5px;
        }
        .edit-panel .form-control {
            width: 100%;
            max-width: 100%;
            min-width: 0;
            font-size: 1rem;
            color: #222;
            background: #f8fafc;
            border-radius: 10px;
            padding: 0.6rem 0.9rem;
            border: 1.2px solid #e0e7ff;
            box-shadow: 0 1px 4px 0 rgba(30,41,59,0.04);
            text-align: left;
            font-family: 'Poppins', Arial, Helvetica, sans-serif;
            margin-bottom: 0.2rem;
        }
        .edit-panel .form-control:focus {
            border: 1.2px solid #2563eb;
            background: #fff;
            outline: none;
            box-shadow: 0 2px 8px 0 rgba(37,99,235,0.10);
        }
        .edit-panel input[type="file"].form-control {
            padding: 0.5rem 0.9rem;
            background: #f1f5f9;
        }
        .edit-panel .btn {
            font-size: 1rem;
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
        }

        .edit-panel .d-flex .btn {
    min-width: 150px;
    height: 52px;
    font-size: 1.08rem;
    font-weight: 600;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}
        .btn-cancelar {
            background: #ef4444 !important;
            color: #fff !important;
            border: none;
        }
        .btn-cancelar:hover {
            background: #dc2626 !important;
            color: #fff !important;
        }
        /* Alerta bonita */
        .custom-alert {
            display: none;
            position: fixed;
            top: 30px;
            right: 30px;
            z-index: 99999;
            background: #22c55e;
            color: #fff;
            padding: 1.1rem 2.2rem;
            border-radius: 14px;
            font-size: 1.15rem;
            font-weight: 600;
            box-shadow: 0 4px 24px 0 rgba(30,41,59,0.13);
            animation: fadeIn 0.7s;
        }
        .custom-alert.show {
            display: block;
        }
        @keyframes fadeIn {
            from { opacity: 0; top: 0;}
            to { opacity: 1; top: 30px;}
        }
        @media (max-width: 1100px) {
            .info-card {
                max-width: 98vw;
                padding: 1.2rem 0.5rem;
            }
        }
        @media (max-width: 900px) {
            .info-card {
                max-width: 98vw;
                padding: 1rem 0.5rem;
            }
            .edit-panel {
                width: 100vw;
                padding: 1rem 0.2rem;
            }
            .info-fields {
                flex-direction: column;
                gap: 1rem 0;
            }
            .info-field {
                flex: 1 1 100%;
            }
        }
        @media (max-width: 600px) {
            .sidebar {
                display: none;
            }
            .info-card {
                width: 100vw;
                min-height: 60vh;
                padding: 1rem 0.2rem;
            }
            .edit-panel {
                width: 100vw;
                padding: 1rem 0.2rem;
            }
            .edit-panel form {
                max-width: 98vw;
            }
            .info-value,
            .edit-panel .form-control {
                min-width: 0;
            }
            .custom-alert {
                right: 10px;
                left: 10px;
                top: 10px;
                font-size: 1rem;
                padding: 0.7rem 1rem;
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
            <i class="ri-admin-line" style="font-size:2rem;vertical-align:middle;margin-right:8px;"></i>
            Panel de Administración
        </h4>
        <a href="panel.php"><i class="ri-home-5-line"></i> Inicio</a>
        <a href="clientes.php"><i class="ri-group-line"></i> Lista de Clientes</a>
        <a href="crear_proyectos.php"><i class="ri-folder-add-line"></i> Crear Proyecto</a>
        <a href="mensaje.php"><i class="ri-mail-send-line"></i> Enviar Mensaje</a>
        <a href="recibir_mensaje_de_cliente.php"><i class="ri-message-3-line"></i> Mensajes de Clientes</a>
        <a href="info.php" class="active"><i class="ri-user-settings-line"></i> Información Personal</a>
        <a href="../public/login.php"><i class="ri-logout-box-r-line"></i> Cerrar Sesión</a>
    </div>

    <!-- Alerta bonita -->
    <div id="alertaEditado" class="custom-alert">
        <i class="ri-checkbox-circle-line" style="font-size:1.5rem;vertical-align:middle;margin-right:8px;"></i>
        ¡Se editó correctamente!
    </div>

    <!-- Contenido principal -->
    <div class="content">
        <div class="info-card text-center">
            <?php if ($usuario['foto'] && file_exists("../assets/img/" . $usuario['foto'])): ?>
                <img src="../assets/img/<?= htmlspecialchars($usuario['foto']) ?>" class="profile-img" alt="Foto de perfil">
            <?php else: ?>
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($usuario['nombre'].' '.$usuario['apellido']) ?>&background=2fd8e6&color=fff&size=256" class="profile-img" alt="Foto de perfil">
            <?php endif; ?>
            <div class="info-title"><i class="ri-user-settings-line"></i> Información Personal</div>
            <div class="info-fields">
                <div class="info-field">
                    <span class="info-label">Nombre</span>
                    <div class="info-value"><?= htmlspecialchars($usuario['nombre']) ?></div>
                </div>
                <div class="info-field">
                    <span class="info-label">Apellido</span>
                    <div class="info-value"><?= htmlspecialchars($usuario['apellido']) ?></div>
                </div>
                <div class="info-field">
                    <span class="info-label">Correo</span>
                    <div class="info-value"><?= htmlspecialchars($usuario['correo']) ?></div>
                </div>
                <div class="info-field">
                    <span class="info-label">Rol</span>
                    <div class="info-value"><?= htmlspecialchars($usuario['rol']) ?></div>
                </div>
                <div class="info-field">
                    <span class="info-label">Fecha de Registro</span>
                    <div class="info-value"><?= htmlspecialchars($usuario['creado_en']) ?></div>
                </div>
            </div>
            <button class="btn btn-editar" id="abrirEditar">
                <i class="ri-edit-2-line"></i> Editar
            </button>
        </div>
        <!-- Panel lateral de edición -->
        <div class="edit-panel" id="editPanel">
            <form method="POST" enctype="multipart/form-data" id="editForm">
                <h5><i class="ri-edit-2-line"></i> Editar Información</h5>
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Apellido</label>
                    <input type="text" name="apellido" class="form-control" value="<?= htmlspecialchars($usuario['apellido']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Correo</label>
                    <input type="email" name="correo" class="form-control" value="<?= htmlspecialchars($usuario['correo']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Foto de perfil</label>
                    <input type="file" name="foto" class="form-control" accept="image/*">
                </div>
                <input type="hidden" name="editar_info" value="1">
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-cancelar" id="cerrarPanel">Cancelar</button>
                    <button type="submit" class="btn btn-editar">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Abrir panel lateral
        document.getElementById('abrirEditar').onclick = function() {
            document.getElementById('editPanel').classList.add('active');
        };
        // Cerrar panel lateral
        document.getElementById('cerrarPanel').onclick = function() {
            document.getElementById('editPanel').classList.remove('active');
        };
        // Confirmar antes de editar
        document.getElementById('editForm').onsubmit = function(e) {
            e.preventDefault();
            if (confirm('¿Quieres editar tu información?')) {
                this.submit();
            }
        };

        // Mostrar alerta bonita si se editó correctamente
        <?php if ($mensaje): ?>
        window.addEventListener('DOMContentLoaded', function() {
            var alerta = document.getElementById('alertaEditado');
            alerta.classList.add('show');
            setTimeout(function() {
                alerta.classList.remove('show');
            }, 2500);
        });
        <?php endif; ?>
    </script>
</body>
</html>