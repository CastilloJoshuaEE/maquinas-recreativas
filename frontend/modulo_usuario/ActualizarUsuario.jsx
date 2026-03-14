import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import "../css/modulo_usuario/recuperacion.css";
import api from '../src/utils/api';

export default function ActualizarUsuario() {
    const [error, setError] = useState('');
    const [success, setSuccess] = useState(false);
    const navigate = useNavigate();

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!window.confirm('¿Está seguro de guardar los cambios?')) {
            return;
        }
        try {
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            
            const { response, data: result } = await api.post('/usuario/actualizar-usuario', {
                email: data.correo,
                usuario_asignado: data.usuario_asignado
            });

            if (!response.ok) {
                throw new Error(result.message || 'Error en la respuesta del servidor');
            }
            
            if (result.success) {
                const token = localStorage.getItem('token');
                await api.post('/historial-actividades', {
                    descripcion: `El usuario actualizó un nuevo usuario`
                }, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                
                setSuccess(true);
                setTimeout(() => navigate('/login'), 2000);
            } else {
                setError(result.message || 'Error al actualizar el usuario');
            }
        } catch (err) {
            setError(err.message || 'Error de conexión con el servidor');
        }
    };

    return (
        <div className="recuperacion-container">
            <h2>Actualizar Usuario Asignado</h2>
            {error && <div className="error-message">{error}</div>}
            {success && <div className="success-message">¡Usuario actualizado correctamente! Redirigiendo...</div>}

            <form onSubmit={handleSubmit}>
                <label>Correo electrónico registrado:</label>
                <input type="email" name="correo" required />

                <label>Nuevo usuario asignado:</label>
                <input type="text" name="usuario_asignado" required />

                <button type="submit">Actualizar usuario</button>
            </form>
            <a href="/">Volver al inicio</a>
        </div>
    );
}