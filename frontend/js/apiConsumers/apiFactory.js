/**
*    File        : frontend/js/api/apiFactory.js
*    Project     : CRUD PHP
*    Author      : Tecnologías Informáticas B - Facultad de Ingeniería - UNMdP
*    License     : http://www.gnu.org/licenses/gpl.txt  GNU GPL 3.0
*    Date        : Mayo 2025
*    Status      : Prototype
*    Iteration   : 2.0 ( prototype )
*/

export function createAPI(moduleName, config = {}) 
{
    const API_URL = config.urlOverride ?? `../../backend/server.php?module=${moduleName}`;

    async function sendJSON(method, data) 
    {
        const res = await fetch(API_URL,
        {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });


        // --- INICIO DE MODIFICACIÓN DE MANEJO DE ERRORES ---
        if (!res.ok) {
            // 1. Intentamos leer el JSON del cuerpo (donde está nuestro mensaje de error)
            const errorData = await res.json().catch(() => ({ message: `Error desconocido en ${method}` }));
            
            // 2. Comprobación específica para el error de validación (409)
            if (res.status === 409) {
                // Si es 409, devolvemos el objeto de error. NO lanzamos la excepción.
                // Esto permite que el frontController maneje el modal W3.CSS.
                return { 
                    success: false, 
                    status: 409,
                    message: errorData.message || "Error de validación de negocio (409)."
                };
            }
            
            // 3. Para cualquier otro error (400, 500, etc.), lanzamos la excepción
            // incluyendo el mensaje que el servidor nos devolvió si existe.
            const errorMessage = errorData.message || errorData.error || `Error ${res.status} en ${method}`;
            throw new Error(errorMessage); 
        }
        // --- FIN DE MODIFICACIÓN DE MANEJO DE ERRORES ---

    return {
        async fetchAll()
        {
            const res = await fetch(API_URL);
            if (!res.ok) throw new Error("No se pudieron obtener los datos");
            return await res.json();
        },
        //2.0
        async fetchPaginated(page = 1, limit = 10)
        {
            const url = `${API_URL}&page=${page}&limit=${limit}`;
            const res = await fetch(url);
            if (!res.ok)
                throw new Error("Error al obtener datos paginados");
            return await res.json();
        },
        async create(data)
        {
            return await sendJSON('POST', data);
        },
        async update(data)
        {
            return await sendJSON('PUT', data);
        },
        async remove(id)
        {
            return await sendJSON('DELETE', { id });
        }
    };
}
export function showAlert(message) {
    const alertBox = document.createElement("div");
    alertBox.className = 'alert';
    alertBox.textContent = message;
    document.body.appendChild(alertBox);

    setTimeout(() => {
        alertBox.remove();
    }, 4000);
}
