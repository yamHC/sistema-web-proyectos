<?php
session_start();
require_once '../config/db.php';

// Verificar si está logueado y es admin
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: ../public/login.php');
    exit();
}

// Obtener clientes para el formulario
$stmt = $pdo->query("SELECT id, nombre, empresa FROM personas WHERE rol = 'cliente'");
$clientes = $stmt->fetchAll();

// Manejar el formulario de creación de proyectos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_proyecto'])) {
    $persona_id = $_POST['persona_id'];
    $empresa = $_POST['empresa'];
    $nombre_proyecto = $_POST['nombre_proyecto'];
    $estado = $_POST['estado'];
    $fecha_publicacion = $_POST['fecha_publicacion'];
    $fecha_finalizacion = $_POST['fecha_finalizacion'];
    $avance = $_POST['avance'];

    $stmt = $pdo->prepare("INSERT INTO proyectos (persona_id, empresa, nombre_proyecto, estado, avance, fecha_publicacion, fecha_finalizacion) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$persona_id, $empresa, $nombre_proyecto, $estado, $avance, $fecha_publicacion, $fecha_finalizacion]);

    $_SESSION['mensaje'] = 'Proyecto creado exitosamente.';
    header('Location: crear_proyectos.php');
    exit();
}

// Manejar el formulario de edición de proyectos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_proyecto'])) {
    $id = $_POST['id'];
    $estado = $_POST['estado'];
    $fecha_finalizacion = $_POST['fecha_finalizacion'];
    $avance = $_POST['avance'];

    $stmt = $pdo->prepare("UPDATE proyectos SET estado = ?, fecha_finalizacion = ?, avance = ? WHERE id = ?");
    $stmt->execute([$estado, $fecha_finalizacion, $avance, $id]);

    $_SESSION['mensaje'] = 'Proyecto actualizado exitosamente.';
    header('Location: crear_proyectos.php');
    exit();
}

// Obtener proyectos para la tabla
$stmt = $pdo->query("SELECT p.id, p.empresa, p.nombre_proyecto, p.estado, p.avance, p.fecha_publicacion, p.fecha_finalizacion, c.nombre AS cliente, c.id AS cliente_id 
                     FROM proyectos p 
                     JOIN personas c ON p.persona_id = c.id");
$proyectos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Proyectos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            display: flex;
            min-height: 100vh;
            background: linear-gradient(120deg, #e0e7ff 0%, #f8fafc 100%);
            font-family: 'Poppins', Arial, Helvetica, sans-serif;
        }
        .btn-close {
            background: none;
            border: none;
            font-size: 1.7rem;
            color: #ef4444 !important;
            opacity: 1;
            position: absolute;
            right: 18px;
            top: 18px;
            z-index: 2;
            transition: color 0.2s;
        }
        .btn-close:hover, .btn-close:focus {
            color: #b91c1c !important;
            opacity: 1;
            outline: none;
            box-shadow: none;
        }
        .modal-footer .btn.btn-secondary {
            background: #ef4444;
            color: #fff;
            border: none;
            font-weight: 600;
            min-width: 140px;
            border-radius: 8px;
            transition: background 0.2s;
        }
        .modal-footer .btn.btn-secondary:hover {
            background: #b91c1c;
            color: #fff;
        }
        .sidebar, .sidebar a, .sidebar h4, .sidebar .logo-container {
            font-family: 'Poppins', Arial, Helvetica, sans-serif !important;
        }
        .sidebar {
            width: 380px;
            background:  linear-gradient(180deg,rgb(0, 36, 134) 0%,rgb(2, 131, 217) 100%);
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
            align-items: flex-start;
            justify-content: center;
            background: linear-gradient(120deg, #e0e7ff 0%, #f8fafc 100%);
        }
        .proyectos-card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 8px 40px 0 rgba(30,41,59,0.13);
            width: 100%;
            max-width: 1200px;
            margin: 2.5rem auto;
            padding: 2.2rem 2.2rem 1.5rem 2.2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .proyectos-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .proyectos-busqueda .btn.btn-primary {
    min-width: 180px;
    font-size: 1.08rem;
    font-weight: 600;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}
        .table-responsive {
            width: 100%;
        }
        .table thead th {
            background: #2563eb;
            color: #fff;
            font-weight: 600;
            text-align: center;
        }
        .table td, .table th {
            vertical-align: middle !important;
            text-align: center;
        }
        .acciones-btns {
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        .btn-warning {
            background: #fbbf24;
            color: #222;
            border: none;
            min-width: 90px;
            font-weight: 600;
            /* border-radius: 8px; */
        }
        .btn-warning:hover {
            background: #f59e42;
            color: #fff;
        }
        .btn-danger {
            background: #ef4444;
            color: #fff;
            border: none;
            min-width: 90px;
            font-weight: 600;
            /* border-radius: 8px; */
        }
        .btn-danger:hover {
            background: #dc2626;
            color: #fff;
        }
        .modal-content {
            border-radius: 18px;
        }
        .modal-header {
            background: #2563eb;
            color: #fff;
            border-radius: 18px 18px 0 0;
        }
        .modal-title {
            font-weight: 600;
        }
        .btn-close {
            background: #fff;
        }
        /* Alerta bonita tipo toast */
        .swal2-popup.swal2-toast {
            font-family: 'Poppins', Arial, Helvetica, sans-serif;
            border-radius: 14px !important;
            padding: 1.1rem 2.2rem !important;
            font-size: 1.1rem !important;
        }
        .swal2-title {
            font-size: 1.1rem !important;
        }
        .swal2-close {
            font-size: 1.5rem !important;
            color: #222 !important;
            opacity: 0.7;
        }
        .swal2-close:hover {
            color: #ef4444 !important;
            opacity: 1;
        }
        @media (max-width: 1300px) {
            .proyectos-card {
                max-width: 98vw;
                padding: 1.2rem 0.5rem;
            }
        }
        @media (max-width: 900px) {
            .proyectos-card {
                max-width: 98vw;
                padding: 1rem 0.5rem;
            }
        }
        @media (max-width: 600px) {
            .sidebar {
                display: none;
            }
            .proyectos-card {
                width: 100vw;
                padding: 1rem 0.2rem;
            }
            .proyectos-busqueda {
                max-width: 98vw;
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
        <a href="crear_proyectos.php" class="active"><i class="ri-folder-add-line"></i> Crear Proyecto</a>
        <a href="mensaje.php"><i class="ri-mail-send-line"></i> Enviar Mensaje</a>
        <a href="recibir_mensaje_de_cliente.php"><i class="ri-message-3-line"></i> Mensajes de Clientes</a>
        <a href="info.php"><i class="ri-user-settings-line"></i> Información Personal</a>
        <a href="../public/login.php"><i class="ri-logout-box-r-line"></i> Cerrar Sesión</a>
    </div>

    <!-- Contenido principal -->
    <div class="content">
        <div class="proyectos-card">
            <?php if (isset($_SESSION['mensaje'])): ?>
                <script>
                    window.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: '<?= addslashes($_SESSION['mensaje']) ?>',
                            showConfirmButton: false,
                            timer: 2200,
                            timerProgressBar: true,
                            customClass: {
                                popup: 'swal2-toast'
                            }
                        });
                    });
                </script>
                <?php unset($_SESSION['mensaje']); ?>
            <?php endif; ?>

            <div class="proyectos-title"><i class="ri-folder-add-line"></i> Proyectos</div>
            <div class="proyectos-busqueda d-flex gap-2 mb-3">
                <input type="text" id="searchEmpresa" class="form-control" placeholder="Buscar por empresa">
                <input type="text" id="searchNombre" class="form-control" placeholder="Buscar proyecto">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crearProyectoModal"><i class="ri-add-line"></i> Crear Proyecto</button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle" id="tablaProyectos">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Empresa</th>
                            <th>Nombre del Proyecto</th>
                            <th>Estado</th>
                            <th>Fecha de Publicación</th>
                            <th>Fecha de Finalización</th>
                            <th>Avance</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($proyectos as $proyecto): ?>
                            <tr>
                                <td><?= htmlspecialchars($proyecto['cliente']) ?></td>
                                <td><?= htmlspecialchars($proyecto['empresa']) ?></td>
                                <td><?= htmlspecialchars($proyecto['nombre_proyecto']) ?></td>
                                <td><?= htmlspecialchars($proyecto['estado']) ?></td>
                                <td><?= htmlspecialchars($proyecto['fecha_publicacion']) ?></td>
                                <td><?= htmlspecialchars($proyecto['fecha_finalizacion']) ?></td>
                                <td><?= htmlspecialchars($proyecto['avance']) ?>%</td>
                                <td>
                                    <div class="acciones-btns">
                                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editarProyectoModal" 
                                            data-id="<?= $proyecto['id'] ?>" 
                                            data-estado="<?= htmlspecialchars($proyecto['estado']) ?>" 
                                            data-fecha_finalizacion="<?= htmlspecialchars($proyecto['fecha_finalizacion']) ?>" 
                                            data-avance="<?= htmlspecialchars($proyecto['avance']) ?>">
                                            Editar
                                        </button>
                                        <a href="eliminar_proyecto.php?id=<?= $proyecto['id'] ?>" 
                                           class="btn btn-danger btn-sm btn-eliminar" 
                                           data-id="<?= $proyecto['id'] ?>">
                                           Eliminar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para crear proyecto -->
    <div class="modal fade" id="crearProyectoModal" tabindex="-1" aria-labelledby="crearProyectoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="crearProyectoModalLabel">Crear Proyecto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="crear_proyecto" value="1">
                        <div class="mb-3">
                            <label for="persona_id" class="form-label">Cliente</label>
                            <select name="persona_id" id="persona_id" class="form-control" required>
                                <option value="">Seleccionar Cliente</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?= $cliente['id'] ?>"><?= htmlspecialchars($cliente['nombre']) ?> - <?= htmlspecialchars($cliente['empresa']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="empresa" class="form-label">Empresa</label>
                            <input type="text" name="empresa" id="empresa" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="nombre_proyecto" class="form-label">Nombre del Proyecto</label>
                            <input type="text" name="nombre_proyecto" id="nombre_proyecto" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select name="estado" id="estado" class="form-control" required>
                                <option value="en curso">En curso</option>
                                <option value="finalizado">Finalizado</option>
                                <option value="en pausa">En pausa</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_publicacion" class="form-label">Fecha de Publicación</label>
                            <input type="date" name="fecha_publicacion" id="fecha_publicacion" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_finalizacion" class="form-label">Fecha de Finalización</label>
                            <input type="date" name="fecha_finalizacion" id="fecha_finalizacion" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="avance" class="form-label">Avance (%)</label>
                            <input type="number" name="avance" id="avance" class="form-control" min="0" max="100" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear Proyecto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para editar proyecto -->
    <div class="modal fade" id="editarProyectoModal" tabindex="-1" aria-labelledby="editarProyectoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <!-- Cambia el botón de cerrar por un icono (usando Remix Icon) -->
<!-- filepath: c:\laragon\www\seguimiento_proyecto\admin\crear_proyectos.php -->
<!-- ...código existente... -->
<div class="modal-header">
    <h5 class="modal-title" id="editarProyectoModalLabel">Editar Proyecto</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="position:absolute;right:18px;top:18px;z-index:2;">
        <i class="ri-close-line" style="font-size:1.7rem;color:#000000;"></i>
    </button>
</div>
<!-- ...código existente... -->
                    <div class="modal-body">
                        <input type="hidden" name="editar_proyecto" value="1">
                        <input type="hidden" name="id" id="proyecto-id">
                        <div class="mb-3">
                            <label for="proyecto-estado" class="form-label">Estado</label>
                            <select name="estado" id="proyecto-estado" class="form-control" required>
                                <option value="en curso">En curso</option>
                                <option value="finalizado">Finalizado</option>
                                <option value="en pausa">En pausa</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="proyecto-fecha-finalizacion" class="form-label">Fecha de Finalización</label>
                            <input type="date" name="fecha_finalizacion" id="proyecto-fecha-finalizacion" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="proyecto-avance" class="form-label">Avance (%)</label>
                            <input type="number" name="avance" id="proyecto-avance" class="form-control" min="0" max="100" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar Proyecto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Pasar datos al modal de edición
        const editarProyectoModal = document.getElementById('editarProyectoModal');
        editarProyectoModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const estado = button.getAttribute('data-estado');
            const fechaFinalizacion = button.getAttribute('data-fecha_finalizacion');
            const avance = button.getAttribute('data-avance');

            document.getElementById('proyecto-id').value = id;
            document.getElementById('proyecto-estado').value = estado;
            document.getElementById('proyecto-fecha-finalizacion').value = fechaFinalizacion;
            document.getElementById('proyecto-avance').value = avance;
        });

        // Filtrar tabla por empresa y nombre del proyecto
        const searchEmpresa = document.getElementById('searchEmpresa');
        const searchNombre = document.getElementById('searchNombre');
        const tablaProyectos = document.getElementById('tablaProyectos').getElementsByTagName('tbody')[0];

        function filtrarTabla() {
            const filtroEmpresa = searchEmpresa.value.toLowerCase();
            const filtroNombre = searchNombre.value.toLowerCase();

            Array.from(tablaProyectos.rows).forEach(row => {
                const empresa = row.cells[1].textContent.toLowerCase();
                const nombre = row.cells[2].textContent.toLowerCase();

                if (empresa.includes(filtroEmpresa) && nombre.includes(filtroNombre)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        searchEmpresa.addEventListener('input', filtrarTabla);
        searchNombre.addEventListener('input', filtrarTabla);

        // Alerta bonita para eliminar proyecto
        document.querySelectorAll('.btn-eliminar').forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault(); // Evitar la acción predeterminada del enlace

                const href = this.getAttribute('href');

                Swal.fire({
                    title: '¿Estás seguro de eliminar este proyecto?',
                    text: "¡Esta acción no se puede deshacer!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#f60808',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href; // Redirigir al enlace de eliminación
                    }
                });
            });
        });
    </script>
</body>
</html>