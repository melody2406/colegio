<?php

class alumno {
    private $conn;
    private $table = 'alumno';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT * FROM {$this->table}";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "
            SELECT 
                a.*, 
                als.id_sesion AS sesion_id, 
                als.id_curso AS curso_id
            FROM alumno a
            LEFT JOIN alumno_sesion als ON a.id_alumno = als.id_alumno
            WHERE a.id_alumno = :id
            LIMIT 1
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(
    $dni, $nombre, $fecha_matricula, 
    $id_sesion_1, $id_curso_1, 
    $id_sesion_2, $id_curso_2, 
    $id_sesion_3, $id_curso_3
) {
    try {
        $this->conn->beginTransaction();

        $query = "INSERT INTO {$this->table} (dni, nombre, fecha_matricula) 
                  VALUES (:dni, :nombre, :fecha)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            'dni' => $dni,
            'nombre' => $nombre,
            'fecha' => $fecha_matricula
        ]);

        $id_alumno = $this->conn->lastInsertId();  
        $sessions = [
            ['id_sesion' => $id_sesion_1, 'id_curso' => $id_curso_1],
            ['id_sesion' => $id_sesion_2, 'id_curso' => $id_curso_2],
            ['id_sesion' => $id_sesion_3, 'id_curso' => $id_curso_3]
        ];

        foreach ($sessions as $session) {
            if ($session['id_sesion'] && $session['id_curso']) {
                $query_sesion = "INSERT INTO alumno_sesion (id_alumno, id_sesion, id_curso, fecha_inscripcion) 
                                 VALUES (:id_alumno, :id_sesion, :id_curso, CURDATE())";
                $stmt_sesion = $this->conn->prepare($query_sesion);
                $stmt_sesion->execute([
                    'id_alumno' => $id_alumno,
                    'id_sesion' => $session['id_sesion'],
                    'id_curso' => $session['id_curso']
                ]);
            }
        }

        $this->conn->commit();
        return true;

    } catch (PDOException $e) {
        $this->conn->rollBack();
        return false;
    }
}


    public function update($id, $dni, $nombre, $fecha_matricula, 
                       $id_sesion_1, $id_curso_1,
                       $id_sesion_2, $id_curso_2,
                       $id_sesion_3, $id_curso_3) {
    try {
        $this->conn->beginTransaction();

        $query = "UPDATE {$this->table} 
                  SET dni = :dni, nombre = :nombre, fecha_matricula = :fecha 
                  WHERE id_alumno = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            'dni' => $dni,
            'nombre' => $nombre,
            'fecha' => $fecha_matricula,
            'id' => $id
        ]);

        $stmtDel = $this->conn->prepare("DELETE FROM alumno_sesion WHERE id_alumno = :id");
        $stmtDel->execute(['id' => $id]);

        $insert = $this->conn->prepare("
            INSERT INTO alumno_sesion (id_alumno, id_sesion, id_curso, fecha_inscripcion)
            VALUES (:id_alumno, :id_sesion, :id_curso, CURDATE())
        ");

        if ($id_curso_1) {
            $insert->execute([
                'id_alumno' => $id,
                'id_sesion' => $id_sesion_1,
                'id_curso' => $id_curso_1
            ]);
        }

        if ($id_curso_2) {
            $insert->execute([
                'id_alumno' => $id,
                'id_sesion' => $id_sesion_2,
                'id_curso' => $id_curso_2
            ]);
        }

        if ($id_curso_3) {
            $insert->execute([
                'id_alumno' => $id,
                'id_sesion' => $id_sesion_3,
                'id_curso' => $id_curso_3
            ]);
        }

        $this->conn->commit();
        return true;

    } catch (PDOException $e) {
        $this->conn->rollBack();
        return false;
    }
}


    public function delete($id) {
        try {
            $this->conn->beginTransaction();

            $stmt_rel = $this->conn->prepare("DELETE FROM alumno_sesion WHERE id_alumno = :id");
            $stmt_rel->execute(['id' => $id]);

            $stmt_alumno = $this->conn->prepare("DELETE FROM {$this->table} WHERE id_alumno = :id");
            $stmt_alumno->execute(['id' => $id]);

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function getAllWithSesionesAndCursos() {
        $query = "
            SELECT 
                a.id_alumno, a.dni, a.nombre, a.fecha_matricula,
                s.id_sesion, s.nombre AS nombre_sesion,
                c.id_curso, c.nombre AS nombre_curso,
                als.fecha_inscripcion
            FROM alumno a
            LEFT JOIN alumno_sesion als ON a.id_alumno = als.id_alumno
            LEFT JOIN sesion s ON als.id_sesion = s.id_sesion
            LEFT JOIN curso c ON als.id_curso = c.id_curso
            ORDER BY a.id_alumno;
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $alumnos = [];
        foreach ($rows as $row) {
            $id = $row['id_alumno'];
            if (!isset($alumnos[$id])) {
                $alumnos[$id] = [
                    'id_alumno' => $id,
                    'dni' => $row['dni'],
                    'nombre' => $row['nombre'],
                    'fecha_matricula' => $row['fecha_matricula'],
                    'sesiones' => []
                ];
            }

            if ($row['id_sesion']) {
                $sesion_id = $row['id_sesion'];
                if (!isset($alumnos[$id]['sesiones'][$sesion_id])) {
                    $alumnos[$id]['sesiones'][$sesion_id] = [
                        'id_sesion' => $sesion_id,
                        'nombre_sesion' => $row['nombre_sesion'],
                        'fecha_inscripcion' => $row['fecha_inscripcion'],
                        'cursos' => []
                    ];
                }

                if ($row['id_curso']) {
                    $curso_id = $row['id_curso'];
                    $alumnos[$id]['sesiones'][$sesion_id]['cursos'][$curso_id] = [
                        'id_curso' => $curso_id,
                        'nombre_curso' => $row['nombre_curso']
                    ];
                }
            }
        }

        foreach ($alumnos as &$alumno) {
            $alumno['sesiones'] = array_values($alumno['sesiones']);
            foreach ($alumno['sesiones'] as &$sesion) {
                $sesion['cursos'] = array_values($sesion['cursos']);
            }
        }

        return array_values($alumnos);
    }
}
?>
