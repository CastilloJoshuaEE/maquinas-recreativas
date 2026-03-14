import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import "../css/modulo_usuario/recuperacion.css";
import api from '../src/utils/api';

export default function RecuperarContrasena() {
    const [error, setError] = useState('');
    const [success, setSuccess] = useState(false);
    const navigate = useNavigate();

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            
            if (data.nueva !== data.repetir) {
                setError('Las contraseñas no coinciden');
                setTimeout(() => window.location.reload(), 2000);
                return;
            }
            
            const { response, data: result } = await api.post('/usuario/recuperar-contrasena', {
                email: data.correo,
                nueva_contrasena: data.nueva
            });

            if (!response.ok) {
                throw new Error(result.message || 'Error en la respuesta del servidor');
            }

            if (result.success) {
                setSuccess(true);
                setTimeout(() => navigate('/login'), 2000);
            } else {
                setError(result.message || 'Error al actualizar la contraseña');
            }
        } catch (err) {
            setError(err.message || 'Error de conexión con el servidor');
        }
    };

    return (
        <div className="recuperacion-container">
            <h2>Recuperar Contraseña</h2>
            {error && <div className="error-message">{error}</div>}
            {success && <div className="success-message">¡Contraseña actualizada correctamente! Redirigiendo...</div>}

            <form onSubmit={handleSubmit}>
                <label>Correo electrónico registrado:</label>
                <input type="email" name="correo" required />

                <label>Nueva contraseña:</label>
                <input type="password" name="nueva" required />

                <label>Repetir contraseña:</label>
                <input type="password" name="repetir" required />

                <button type="submit">Actualizar contraseña</button>
            </form>
            <a href="/">Volver al inicio</a>
        </div>
    );
}