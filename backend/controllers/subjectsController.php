<?php
/**
*    File        : backend/controllers/subjectsController.php
*    Project     : CRUD PHP
*    Author      : Tecnologías Informáticas B - Facultad de Ingeniería - UNMdP
*    License     : http://www.gnu.org/licenses/gpl.txt  GNU GPL 3.0
*    Date        : Mayo 2025
*    Status      : Prototype
*    Iteration   : 3.0 ( prototype )
*/

require_once("./repositories/subjects.php");

function handleGet($conn) 
{
    $input = json_decode(file_get_contents("php://input"), true);

    if (isset($input['id'])) 
    {
        $subjects = getSubjectById($conn, $input['id']);
        echo json_encode($subject);
    } 
    //2.0
    else if (isset($_GET['page']) && isset($_GET['limit'])) 
    {
        $page = (int)$_GET['page'];
        $limit = (int)$_GET['limit'];
        $offset = ($page - 1) * $limit;

        $subjects = getPaginatedSubjects($conn, $limit, $offset);
        $total = getTotalSubjects($conn);

        echo json_encode([
            'subjects' => $subjects, // ya es array
            'total' => $total        // ya es entero
        ]);
    }
    else
    {
        $subjects = getAllSubjects($conn); // ya es array
        echo json_encode($subjects);
    }
}

function handlePut($conn) 
{
    $input = json_decode(file_get_contents("php://input"), true);

    $result = updateSubject($conn, $input['id'], $input['name']);
    if ($result['updated'] > 0) 
    {
        echo json_encode(["message" => "Materia actualizada correctamente"]);
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
    
    $result = deleteSubject($conn, $input['id']);
    if ($result['deleted'] > 0) 
    {
        echo json_encode(["message" => "Materia eliminada correctamente"]);
    } 
    else 
    {
        http_response_code(500);
        echo json_encode(["error" => "No se pudo eliminar"]);
    }
}



//TRABAJO PRACTICO GRUPAL 3.0 Ivan Yungblut
// Busca una materia por su nombre (para validación de duplicados) de forma case insensitive
function handlePost($conn) {
    $input = json_decode(file_get_contents("php://input"), true);
    if (empty($input['name'])) {
        echo json_encode(["error" => "El nombre de la materia es requerido"]);
        http_response_code(400); // Bad Request
        return;
    }

    // --- VALIDACION BACKEND ---

    $existingSubject = getSubjectByName($conn, $input['name']);
    if ($existingSubject) {
        echo json_encode(["error" => "La materia ya existe (back)"]);
        http_response_code(409); // HTTP 409 (Conflict) es el código perfecto para "ya existe"
        return;
    }

    // --- VALIDACION BACKEND ---
    
    $result = createSubject($conn, $input['name']);
    if ($result['inserted'] > 0) {
        echo json_encode(["message" => "Materia creada correctamente"]);
    } 
    else {
        http_response_code(500);
        echo json_encode(["error" => "No se pudo crear por un error interno"]);
    }
}
?>