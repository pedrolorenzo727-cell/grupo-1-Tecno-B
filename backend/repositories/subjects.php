<?php
/**
*    File        : backend/models/subjects.php
*    Project     : CRUD PHP
*    Author      : Tecnologías Informáticas B - Facultad de Ingeniería - UNMdP
*    License     : http://www.gnu.org/licenses/gpl.txt  GNU GPL 3.0
*    Date        : Mayo 2025
*    Status      : Prototype
*    Iteration   : 3.0 ( prototype )
*/

function getAllSubjects($conn) 
{
    $sql = "SELECT * FROM subjects";

    return $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

//2.0
function getPaginatedSubjects($conn, $limit, $offset) 
{
    $stmt = $conn->prepare("SELECT * FROM subjects LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

//2.0
function getTotalSubjects($conn) 
{
    $sql = "SELECT COUNT(*) AS total FROM subjects";
    $result = $conn->query($sql);
    return $result->fetch_assoc()['total'];
}

function getSubjectById($conn, $id) 
{
    $sql = "SELECT * FROM subjects WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc(); 
}

function createSubject($conn, $name) 
{
    $sql = "INSERT INTO subjects (name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $name);
    $stmt->execute();

    return 
    [
        'inserted' => $stmt->affected_rows,        
        'id' => $conn->insert_id
    ];
}

function updateSubject($conn, $id, $name) 
{
    $sql = "UPDATE subjects SET name = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $name, $id);
    $stmt->execute();

    return ['updated' => $stmt->affected_rows];
}
//3.0
function deleteSubject($conn, $id) 
{
    $sqlCheck = "SELECT COUNT(*) AS total FROM students_subjects WHERE subject_id = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("i", $id);
    $stmtCheck->execute();
    $result = $stmtCheck->get_result();
    $row = $result->fetch_assoc();

    if ($row['total'] > 0) {
        // Tiene estudiantes asignados, no se elimina
        return [
            'deleted' => 0,
            'error' => 'No se puede eliminar la materia porque tiene estudiantes asignados.'
        ];
    }
    else
    {
      $sql = "DELETE FROM subjects WHERE id = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("i", $id);
      $stmt->execute();

      return ['deleted' => $stmt->affected_rows];
    }
}
?>
