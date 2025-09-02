<?php
require_once 'Database.php';
require_once 'alumno.php';
require_once 'curso.php';
require_once 'sesion.php';

$db = new Database();
$conn = $db->getConnection();

$alumno = new alumno($conn);
$alumnos = $alumno->getAllWithSesionesAndCursos(); 
?>

<!DOCTYPE html>
<html lang="es">
    
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Alumnos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        background: linear-gradient(135deg, #6B73FF 0%, #00ff80ff 100%);
        color: #f8f8f8ff;
        font-family: 'Roboto', sans-serif;
    }
    h1 {
        font-weight: 700;
        letter-spacing: 2px;
        text-align: center;
        margin-bottom: 2rem;
        text-shadow: 2px 2px 5px rgba(0,0,0,0.3);
    }
    input[type="text"], input[type="date"], select {
        border: 2px solid #4A90E2;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        transition: border-color 0.3s ease;
        color: #000;
        background-color: #ffffffff;
    }
    input[type="text"]:focus, input[type="date"]:focus, select:focus {
        border-color: #0026FF;
        box-shadow: 0 0 8px #0026FF;
        outline: none;
    }
    button, .btn {
        background: #4A90E2;
        border: none;
        border-radius: 8px;
        color: white;
        padding: 0.5rem 1.5rem;
        font-weight: 600;
        transition: background-color 0.3s ease;
    }
    button:hover, .btn:hover {
        background: #0026FF;
        color: #fff;
    }
    .container {
        max-width: 1000px;
        margin-top: 4rem;
        background-color: rgba(255,255,255,0.9);
        padding: 2rem;
        border-radius: 15px;
        color: #000;
    }
    </style>
</head>
<body>
<div class="container p-4">
    <h1>Lista de Alumnos</h1>
    <a href="agregar.php" class="btn btn-success mb-3">Agregar Alumno</a>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>DNI</th>
                <th>Nombre</th>
                <th>Fecha Matrícula</th>
                <th>Cursos y Sesiones</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($alumnos as $alumno): ?>
            <tr>
                <td><?= htmlspecialchars($alumno['dni']) ?></td>
                <td><?= htmlspecialchars($alumno['nombre']) ?></td>
                <td><?= htmlspecialchars($alumno['fecha_matricula']) ?></td>
                <td>
                    <?php if (!empty($alumno['sesiones'])): ?>
                        <ul class="mb-0">
                        <?php foreach ($alumno['sesiones'] as $sesion): ?>
                            <li>
                                <strong><?= htmlspecialchars($sesion['nombre_sesion']) ?>:</strong>
                                <?php
                                    $cursoNombres = array_map(fn($c) => htmlspecialchars($c['nombre_curso']), $sesion['cursos']);
                                    echo implode(", ", $cursoNombres);
                                ?>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <em>No asignado</em>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="editar.php?id=<?= $alumno['id_alumno'] ?>" class="btn btn-warning btn-sm">Editar</a>
                    <a href="eliminar.php?id=<?= $alumno['id_alumno'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar este alumno?')">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        
        </tbody>
    </table>
</div>
</body>
</html>
