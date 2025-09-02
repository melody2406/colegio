<?php
class sesion {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
    $sql = "SELECT * FROM sesion";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function getAllWithCursos() {
        $sql = "SELECT 
                    s.id_sesion, 
                    s.nombre AS sesion_nombre, 
                    GROUP_CONCAT(c.nombre ORDER BY c.nombre SEPARATOR ', ') AS cursos
                FROM sesion s
                LEFT JOIN curso_sesion cs ON cs.id_sesion = s.id_sesion
                LEFT JOIN curso c ON c.id_curso = cs.id_curso
                GROUP BY s.id_sesion
                ORDER BY s.id_sesion";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
