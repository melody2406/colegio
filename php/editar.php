<?php
require_once 'Database.php';
require_once 'alumno.php';
require_once 'curso.php';
require_once 'sesion.php';

$db = new Database();
$conn = $db->getConnection();

$alumno = new alumno($conn);
$sesionObj = new sesion($conn);

// Obtener sesiones y cursos por sesión para el select dinámico
$sesiones = $sesionObj->getAll(); 
$cursosPorSesion = [];
foreach ($sesiones as $s) {
    $stmt = $conn->prepare("
        SELECT c.id_curso, c.nombre 
        FROM curso c
        JOIN curso_sesion cs ON c.id_curso = cs.id_curso
        WHERE cs.id_sesion = :id_sesion
    ");
    $stmt->execute(['id_sesion' => $s['id_sesion']]);
    $cursosPorSesion[$s['id_sesion']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = intval($_GET['id']);
$alumnoData = $alumno->getById($id);

if (!$alumnoData) {
    die('Alumno no encontrado');
}

$alumnoConSesiones = $alumno->getAllWithSesionesAndCursos();
$currentSessions = [];
foreach ($alumnoConSesiones as $item) {
    if ($item['id_alumno'] == $id) {
        foreach ($item['sesiones'] as $ses) {
            $currentSessions[$ses['id_sesion']] = $ses['cursos'][0]['id_curso'] ?? null;
        }
        break;
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = htmlspecialchars($_POST['dni'] ?? '');
    $nombre = htmlspecialchars($_POST['nombre'] ?? '');
    $fecha = htmlspecialchars($_POST['fecha'] ?? '');

    $ids_sesiones = array_column($sesiones, 'id_sesion');

    $cursosSeleccionados = [];
    foreach ($ids_sesiones as $ses_id) {
        $curso_key = 'id_curso_' . $ses_id;
        $cursosSeleccionados[$ses_id] = isset($_POST[$curso_key]) ? intval($_POST[$curso_key]) : null;
    }

    $result = $alumno->update(
        $id, $dni, $nombre, $fecha,
        $ids_sesiones[0], $cursosSeleccionados[$ids_sesiones[0]] ?? null,
        $ids_sesiones[1], $cursosSeleccionados[$ids_sesiones[1]] ?? null,
        $ids_sesiones[2], $cursosSeleccionados[$ids_sesiones[2]] ?? null
    );

    if ($result) {
        header('Location: index.php');
        exit;
    } else {
        $error = "Error al actualizar el alumno";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Editar Alumno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(135deg, #44eba5ff 0%, #2575fc 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
            font-family: Arial, sans-serif;
        }
        
        h2 {
            text-align: center;
            margin-bottom: 25px;
            font-weight: 700;
            letter-spacing: 1.5px;
        }
        .form-control, .form-select {
            margin-bottom: 15px;
            background-color: rgba(255,255,255,0.85);
            border: none;
            border-radius: 5px;
            padding: 10px 12px;
            font-size: 16px;
            color: #333;
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: #00d1b2;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn-submit:hover {
            background-color: #00b89c;
        }
        label {
            font-weight: 600;
            margin-top: 10px;
            display: block;
        }
        .error-msg {
            background-color: #ff4d4f;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: 600;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Editar Alumno</h2>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="dni" value="<?= htmlspecialchars($alumnoData['dni']) ?>" required class="form-control" placeholder="DNI" />
        <input type="text" name="nombre" value="<?= htmlspecialchars($alumnoData['nombre']) ?>" required class="form-control" placeholder="Nombre" />
        <input type="date" name="fecha" value="<?= htmlspecialchars($alumnoData['fecha_matricula']) ?>" required class="form-control" placeholder="Fecha de matrícula" />

        <?php foreach ($sesiones as $sesion): ?>
            <label class="form-label"><?= htmlspecialchars($sesion['nombre']) ?></label>
            <input type="hidden" name="id_sesion_<?= $sesion['id_sesion'] ?>" value="<?= $sesion['id_sesion'] ?>" />
            <select name="id_curso_<?= $sesion['id_sesion'] ?>" class="form-select mb-3" required>
                <option value="">Selecciona un curso</option>
                <?php foreach ($cursosPorSesion[$sesion['id_sesion']] as $curso): ?>
                    <option value="<?= $curso['id_curso'] ?>"
                        <?= (isset($currentSessions[$sesion['id_sesion']]) && $currentSessions[$sesion['id_sesion']] == $curso['id_curso']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($curso['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-primary mt-3">Actualizar</button>
        <a href="index.php" class="btn btn-secondary mt-3 ms-2">Cancelar</a>
    </form>
</div>
</body>
</html>
