/**
*    File        : frontend/js/controllers/studentsController.js
*    Project     : CRUD PHP
*    Author      : Tecnologías Informáticas B - Facultad de Ingeniería - UNMdP
*    License     : http://www.gnu.org/licenses/gpl.txt  GNU GPL 3.0
*    Date        : Mayo 2025
*    Status      : Prototype
*    Iteration   : 2.0 ( prototype )
*/

import { studentsAPI } from '../apiConsumers/studentsAPI.js';

//2.0
//For pagination:
let currentPage = 1;
let totalPages = 1;
const limit = 5;

document.addEventListener('DOMContentLoaded', () => 
{
    loadStudents();
    setupFormHandler();
    setupCancelHandler();
    setupPaginationControls();//2.0
});
  
function setupFormHandler()
{
    const form = document.getElementById('studentForm');
    form.addEventListener('submit', async e => 
    {
        e.preventDefault();
        const student = getFormData();
    
        try 
        {
            if (student.id) 
            {
                await studentsAPI.update(student);
            } 
            else 
            {
                try {
                    await studentsAPI.create(student);
                }
                catch (e){
                    showError("El correo ingresado ya existe. Intente con otro.");
                    return;
                }
            }
            clearForm();
            loadStudents();
        }
        catch (err)
        {
            showError(err.message || 'Ocurrió un error al procesar la solicitud.');
        }
    });
}

function setupCancelHandler()
{
    const cancelBtn = document.getElementById('cancelBtn');
    cancelBtn.addEventListener('click', () => 
    {
        document.getElementById('studentId').value = '';
    });
}

//2.0
function setupPaginationControls() 
{
    document.getElementById('prevPage').addEventListener('click', () => 
    {
        if (currentPage > 1) 
        {
            currentPage--;
            loadStudents();
        }
    });

    document.getElementById('nextPage').addEventListener('click', () => 
    {
        if (currentPage < totalPages) 
        {
            currentPage++;
            loadStudents();
        }
    });

    document.getElementById('resultsPerPage').addEventListener('change', e => 
    {
        currentPage = 1;
        loadStudents();
    });
}
  
function getFormData()
{
    return {
        id: document.getElementById('studentId').value.trim(),
        fullname: document.getElementById('fullname').value.trim(),
        email: document.getElementById('email').value.trim(),
        age: parseInt(document.getElementById('age').value.trim(), 10)
    };
}
  
function clearForm()
{
    document.getElementById('studentForm').reset();
    document.getElementById('studentId').value = '';
}

//2.0
async function loadStudents()
{
    try 
    {
        const resPerPage = parseInt(document.getElementById('resultsPerPage').value, 10) || limit;
        const data = await studentsAPI.fetchPaginated(currentPage, resPerPage);
        console.log(data);
        renderStudentTable(data.students);
        totalPages = Math.ceil(data.total / resPerPage);
        document.getElementById('pageInfo').textContent = `Página ${currentPage} de ${totalPages}`;
    } 
    catch (err) 
    {
        console.error('Error cargando estudiantes:', err.message);
    }
}
  
function renderStudentTable(students)
{
    const tbody = document.getElementById('studentTableBody');
    tbody.replaceChildren();
  
    students.forEach(student => 
    {
        const tr = document.createElement('tr');
    
        tr.appendChild(createCell(student.fullname));
        tr.appendChild(createCell(student.email));
        tr.appendChild(createCell(student.age.toString()));
        tr.appendChild(createActionsCell(student));
    
        tbody.appendChild(tr);
    });
}
  
function createCell(text)
{
    const td = document.createElement('td');
    td.textContent = text;
    return td;
}
  
function createActionsCell(student)
{
    const td = document.createElement('td');
  
    const editBtn = document.createElement('button');
    editBtn.textContent = 'Editar';
    editBtn.className = 'w3-button w3-blue w3-small';
    editBtn.addEventListener('click', () => fillForm(student));
  
    const deleteBtn = document.createElement('button');
    deleteBtn.textContent = 'Borrar';
    deleteBtn.className = 'w3-button w3-red w3-small w3-margin-left';
    deleteBtn.addEventListener('click', () => confirmDelete(student.id));
  
    td.appendChild(editBtn);
    td.appendChild(deleteBtn);
    return td;
}
  
function fillForm(student)
{
    document.getElementById('studentId').value = student.id;
    document.getElementById('fullname').value = student.fullname;
    document.getElementById('email').value = student.email;
    document.getElementById('age').value = student.age;
}
  
//Pilar Balbuena 3.0
async function confirmDelete(id) 
{
    if (!confirm('¿Estás seguro que deseas borrar este estudiante?')) return;
    
    try
    {
        const data = await studentsAPI.remove(id); 
        //FIX try - nuevo manejo de respuesta de API, para manejar data como json ... en lugar de un response de fetch.
        if (data.error) {
            alert(data.error); 
        }
        else if (data.message) {
            alert(data.message);
            loadStudents(); 
        }
        else {
            alert('Ocurrió un error inesperado al eliminar el estudiante.');
        }
    } 
    catch (err)
    {
        // 🛑 LÓGICA DE CAPTURA DEL ERROR 409 DENTRO DEL CATCH:
        // Verificamos si el error (err) es el objeto de respuesta HTTP
        if (err && err.status === 409) {
            
            let errorMessage = "Error: El estudiante está asignado a una o más asignaturas.";
            
            try {
                // Leemos el cuerpo del error para obtener el mensaje del Backend
                const data = await err.json();
                errorMessage = data.message;
            } catch (e) {
                // Si la lectura del JSON falla, usamos el mensaje de fallback
            }
            
            // Mostrar el mensaje garantizado
            alert("⚠️ Error de Borrado:\n\n" + errorMessage);
            return; // Detenemos la ejecución después de mostrar la alerta
        }
        
        // Código original de tus compañeros para errores de red o fallos genéricos
        //console.error('Error de red/petición:', err.message); 
        alert('Ocurrió un error de conexión o servidor. (Fallo general)');
    }
}

  