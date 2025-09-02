<?php
require_once 'Database.php';
require_once 'alumno.php';
require_once 'curso.php';
require_once 'sesion.php';

$db = new Database();
$conn = $db->getConnection();

$alumno = new alumno($conn);
$sesionObj = new sesion($conn);

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

    $result = $alumno->create(
        $dni, $nombre, $fecha,
        $ids_sesiones[0], $cursosSeleccionados[$ids_sesiones[0]] ?? null,
        $ids_sesiones[1], $cursosSeleccionados[$ids_sesiones[1]] ?? null,
        $ids_sesiones[2], $cursosSeleccionados[$ids_sesiones[2]] ?? null
    );

    if ($result) {
        header('Location: index.php');
        exit;
    } else {
        $error = "Error al agregar el alumno";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Agregar Alumno</title>
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
        .form-container {
            background-color: rgba(255, 255, 255, 0.15);
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            width: 400px;
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
<div class="form-container">
    <h2>Agregar Alumno</h2>
    <?php if (!empty($error)): ?>
        <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" autocomplete="off">
        <input type="text" name="dni" placeholder="DNI" required class="form-control" value="<?= $_POST['dni'] ?? '' ?>" />
        <input type="text" name="nombre" placeholder="Nombre" required class="form-control" value="<?= $_POST['nombre'] ?? '' ?>" />
        <input type="date" name="fecha" placeholder="Fecha de matrícula" required class="form-control" value="<?= $_POST['fecha'] ?? '' ?>" />

        <?php foreach ($sesiones as $sesion): ?>
            <label><?= htmlspecialchars($sesion['nombre']) ?></label>
            <input type="hidden" name="id_sesion_<?= $sesion['id_sesion'] ?>" value="<?= $sesion['id_sesion'] ?>" />
            <select name="id_curso_<?= $sesion['id_sesion'] ?>" class="form-select" required>
                <option value="">Selecciona un curso</option>
                <?php foreach ($cursosPorSesion[$sesion['id_sesion']] as $curso): ?>
                    <option value="<?= $curso['id_curso'] ?>"
                        <?= (isset($_POST['id_curso_' . $sesion['id_sesion']]) && $_POST['id_curso_' . $sesion['id_sesion']] == $curso['id_curso']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($curso['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endforeach; ?>

        <button type="submit" class="btn-submit mt-3">Guardar Alumno</button>
    </form>
</div>
</body>
</html>
