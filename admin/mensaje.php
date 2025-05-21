<?php

session_start();
require_once '../config/db.php';

// Verificar si está logueado y es administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: ../public/login.php');
    exit();
}

// Obtener la lista de clientes
$stmt = $pdo->query("SELECT id, nombre, correo, empresa FROM personas WHERE rol = 'cliente'");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Manejar el envío del mensaje (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id = $_POST['cliente_id'];
    $asunto = $_POST['asunto'];
    $mensaje = $_POST['mensaje'];
    $admin_id = $_SESSION['usuario']['id']; // ID del administrador que envía el mensaje

    // Obtener el nombre del cliente para la respuesta
    $stmt = $pdo->prepare("SELECT nombre FROM personas WHERE id = ?");
    $stmt->execute([$cliente_id]);
    $cliente = $stmt->fetch();

    try {
        // Guardar el mensaje en la base de datos
        $stmt = $pdo->prepare("INSERT INTO mensajes (remitente_id, destinatario_id, asunto, contenido, enviado_en) 
                               VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$admin_id, $cliente_id, $asunto, $mensaje]);

        echo json_encode(['status' => 'success', 'message' => 'Mensaje enviado correctamente para ' . htmlspecialchars($cliente['nombre']) . '.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar el mensaje: ' . $e->getMessage()]);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mensajería</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    align-items: center;      /* Centra verticalmente */
    justify-content: center;  /* Centra horizontalmente */
    background: linear-gradient(120deg, #e0e7ff 0%, #f8fafc 100%);
}
.mensaje-card {
    background: #fff;
    border-radius: 24px;
    box-shadow: 0 8px 40px 0 rgba(30,41,59,0.13);
    width: 100%;
    max-width: 750px;         /* Más largo */
    margin: 0 auto;
    padding: 2.2rem 2.2rem 1.5rem 2.2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
}
        .mensaje-title {
            font-size: 2rem;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .form-label {
            font-weight: 600;
            color: #222;
        }
        .btn-primary {
            background: #2563eb;
            border: none;
            font-weight: 600;
            border-radius: 10px;
            min-width: 180px;
        }
        .btn-primary:hover {
            background: #1d4ed8;
        }
        .swal2-popup.swal2-toast {
            font-family: 'Poppins', Arial, Helvetica, sans-serif;
            border-radius: 14px !important;
            padding: 1.1rem 2.2rem !important;
            font-size: 1.1rem !important;
        }
        .swal2-title {
            font-size: 1.1rem !important;
        }
        @media (max-width: 900px) {
            .mensaje-card {
                max-width: 98vw;
                padding: 1.2rem 0.5rem;
            }
        }
        @media (max-width: 600px) {
            .sidebar {
                display: none;
            }
            .mensaje-card {
                width: 100vw;
                padding: 1rem 0.2rem;
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
        <a href="mensaje.php" class="active"><i class="ri-mail-send-line"></i> Enviar Mensaje</a>
        <a href="recibir_mensaje_de_cliente.php"><i class="ri-message-3-line"></i> Mensajes de Clientes</a>
        <a href="info.php"><i class="ri-user-settings-line"></i> Información Personal</a>
        <a href="../public/login.php"><i class="ri-logout-box-r-line"></i> Cerrar Sesión</a>
    </div>

    <!-- Contenido principal -->
    <div class="content">
        <div class="mensaje-card">
            <div class="mensaje-title"><i class="ri-mail-send-line"></i> Mensajería</div>
            <p class="mb-4 text-center">Envíe un mensaje a un cliente:</p>
            <!-- Formulario -->
            <form id="mensajeForm" style="width:100%;max-width:420px;">
                <div class="mb-3">
                    <label for="cliente_id" class="form-label">Cliente</label>
                    <select name="cliente_id" id="cliente_id" class="form-control" required>
                        <option value="">Seleccionar Cliente</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= $cliente['id'] ?>" data-nombre="<?= htmlspecialchars($cliente['nombre']) ?>">
                                <?= htmlspecialchars($cliente['nombre']) ?> - <?= htmlspecialchars($cliente['empresa']) ?> (<?= htmlspecialchars($cliente['correo']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="asunto" class="form-label">Asunto</label>
                    <input type="text" name="asunto" id="asunto" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="mensaje" class="form-label">Mensaje</label>
                    <textarea name="mensaje" id="mensaje" class="form-control" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Enviar Mensaje</button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('mensajeForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const form = this;
            const formData = new FormData(form);
            const clienteSelect = document.getElementById('cliente_id');
            const clienteNombre = clienteSelect.options[clienteSelect.selectedIndex].getAttribute('data-nombre');

            fetch('mensaje.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 2200,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'swal2-toast'
                        }
                    });
                    form.reset();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al procesar la solicitud.'
                });
            });
        });
    </script>
</body>
</html>