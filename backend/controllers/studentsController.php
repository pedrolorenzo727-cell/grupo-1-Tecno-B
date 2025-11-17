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

// Para GET (usamos la variable superglobal $_GET):
//https://www.php.net/manual/es/language.variables.superglobals.php
function handleGet($conn) 
{
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

    $result = validateStudent($conn, $input['email']);
    if ($result > 0){
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
    else {
        http_response_code(400);
        echo json_encode(["error" => "El mail ya esta ingresado"]);
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

//Pilar Balbuena 3.0
function handleDelete($conn) 
{
    $input = json_decode(file_get_contents("php://input"), true);
    $id = $input['id']; // Usamos 'id' directamente del input para claridad

    $result = deleteStudent($conn, $id);

    // ==========================================================
    // NUEVA LÓGICA: Manejo del Error de Restricción (409 Conflict)
    // Esto previene que se acceda a $result['deleted'] si hay un error de validación.
    if (isset($result['error'])) {
        http_response_code(409); // Código HTTP para conflicto o regla de negocio violada
        echo json_encode(['message' => $result['message']]);
        return; // ¡DETENER la ejecución aquí!
    }
    // ==========================================================
    
    // CÓDIGO ORIGINAL DE TUS COMPAÑEROS (Sólo se ejecuta si NO hay error de restricción)
    if (isset($result['deleted']) && $result['deleted'] > 0) // Añadimos 'isset' para robustez
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