<?php
/**
*    File        : backend/controllers/studentsController.php
*    Project     : CRUD PHP
*    Author      : Tecnologías Informáticas B - Facultad de Ingeniería - UNMdP
*    License     : http://www.gnu.org/licenses/gpl.txt  GNU GPL 3.0
*    Date        : Mayo 2025
*    Status      : Prototype
*    Iteration   : 2.0 ( prototype )
*/

require_once("./repositories/students.php");
// 2. INCLUSIÓN NECESARIA para la validación (¡AGREGA ESTA LÍNEA!)
require_once("./repositories/studentsSubjects.php");

// Para GET (usamos la variable superglobal $_GET):
//https://www.php.net/manual/es/language.variables.superglobals.php
function handleGet($conn) 
{
    $input = json_decode(file_get_contents("php://input"), true);
    if (isset($_GET['id'])) 
    {
        $student = getStudentById($conn, $_GET['id']);
        echo json_encode($student);
    } 
    //2.0
    else if (isset($_GET['page']) && isset($_GET['limit'])) 
    {
        $page = (int)$_GET['page'];
        $limit = (int)$_GET['limit'];
        $offset = ($page - 1) * $limit;

        $students = getPaginatedStudents($conn, $limit, $offset);
        $total = getTotalStudents($conn);

        echo json_encode([
            'students' => $students, // ya es array
            'total' => $total        // ya es entero
        ]);
    }
    else
    {
        $students = getAllStudents($conn); // ya es array
        echo json_encode($students);
    }
}

function handlePost($conn) 
{
    $input = json_decode(file_get_contents("php://input"), true);

    $result = createStudent($conn, $input['fullname'], $input['email'], $input['age']);
    if ($result['inserted'] > 0) 
    {
        echo json_encode(["message" => "Estudiante agregado correctamente"]);
    } 
    else 
    {
        http_response_code(500);
        echo json_encode(["error" => "No se pudo agregar"]);
    }
}

function handlePut($conn) 
{
    $input = json_decode(file_get_contents("php://input"), true);

    $result = updateStudent($conn, $input['id'], $input['fullname'], $input['email'], $input['age']);
    if ($result['updated'] > 0) 
    {
        echo json_encode(["message" => "Actualizado correctamente"]);
    } 
    else 
    {
        http_response_code(500);
        echo json_encode(["error" => "No se pudo actualizar"]);
    }
}

function handleDelete($conn) 
{
    $input = json_decode(file_get_contents("php://input"), true);
    $studentId = $input['id'] ?? null; // Usamos 'id' de la solicitud

    // 1. VALIDACIÓN INICIAL: Comprobar que el ID existe.
    if ($studentId === null) {
        header('Content-Type: application/json');
        http_response_code(400); // 400 Bad Request: La solicitud está incompleta
        echo json_encode([
            'success' => false,
            'message' => 'Error de solicitud: El ID del estudiante es requerido y no fue enviado.'
        ]);
        return;
    }
    
    // 2. VALIDACIÓN DE NEGOCIO: Comprobar si tiene asignaturas (consigna).
    if (isStudentAssignedToSubjects($conn, $studentId)) {
        
        // VALIDACIÓN FALLIDA: Rechazar la eliminación (CONSIGNA CUMPLIDA).
        header('Content-Type: application/json');
        http_response_code(409); // 409 Conflict
        
        echo json_encode([
            'success' => false,
            'message' => 'ERROR: No se puede eliminar. El estudiante tiene asignaturas asignadas. Desasigne primero para continuar.'
        ]);
        return;
    }

    $result = deleteStudent($conn, $input['id']);
    if ($result['deleted'] > 0) 
    {
        echo json_encode(["message" => "Eliminado correctamente"]);
    } 
    else 
    {
        http_response_code(500);
        echo json_encode(["error" => "No se pudo eliminar"]);
    }
}
?>