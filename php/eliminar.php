<?php
require_once 'Database.php';
require_once 'alumno.php';
require_once 'curso.php';
require_once 'sesion.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$alumno = new alumno($conn);
$id = $_GET['id'];

if ($alumno->delete($id)) {
    header('Location: index.php');
} else {
    die('Error al eliminar alumno');
}
?>