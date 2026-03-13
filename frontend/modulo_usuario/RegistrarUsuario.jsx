import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import "../css/modulo_usuario/registrar_usuario.css";
import api from '../src/utils/api';

export default function RegistrarUsuario() {
    const [formData, setFormData] = useState({
        nombre: '',
        apellido: '',
        ci: '',
        email: '',
        usuario_asignado: '',
        contrasena: '',
        tipo: 'Logistica'
    });
    const [error, setError] = useState('');
    const [success, setSuccess] = useState(false);
    const navigate = useNavigate();

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!window.confirm('Seguro desea registrarse?')) {
            return;
        }
        try {
            const { response, data } = await api.post('/usuario/register', formData);

            if (!response.ok) {
                throw new Error(data.message || 'Error en la respuesta del servidor');
            }

            if (data.success) {
                setSuccess(true);
                setTimeout(() => navigate('/login'), 2000);
            } else {
                setError(data.message || 'Error al registrar usuario');
            }
        } catch (err) {
            setError(err.message || 'Error de conexión con el servidor');
        }
    };

    return (
        <div className="register-container">
            <h2>Formulario de Registro</h2>
            {error && <div className="error-message">{error}</div>}
            {success && <div className="success-message">¡Registro exitoso! Redirigiendo...</div>}

            <form id="form_registro_usuario" onSubmit={handleSubmit}>
                <input 
                    type="text" 
                    name="nombre" 
                    placeholder="Nombre" 
                    value={formData.nombre}
                    onChange={handleChange}
                    required 
                />
                <input 
                    type="text" 
                    name="apellido" 
                    placeholder="Apellido" 
                    value={formData.apellido}
                    onChange={handleChange}
                    required 
                />
                <input 
                    type="text" 
                    name="ci" 
                    placeholder="Cédula (CI)" 
                    maxLength="10" 
                    value={formData.ci}
                    onChange={handleChange}
                    required 
                />
                <input 
                    type="email" 
                    name="email" 
                    placeholder="Correo electrónico" 
                    value={formData.email}
                    onChange={handleChange}
                    required 
                />
                
                <select 
                    name="tipo" 
                    value={formData.tipo}
                    onChange={handleChange}
                    required
                >
                    <option value="">Seleccione una área donde hay vacantes</option>
                    <option value="Contabilidad">Área de Contabilidad</option>
                    <option value="Logistica">Área de Logística</option>
                    <option value="Administrador">Administrador del sistema</option>
                </select>
                
                <input 
                    type="text" 
                    name="usuario_asignado" 
                    placeholder="Nombre de usuario" 
                    value={formData.usuario_asignado}
                    onChange={handleChange}
                    required
                />
                <input 
                    type="password" 
                    name="contrasena" 
                    placeholder="Contraseña" 
                    maxLength="10" 
                    value={formData.contrasena}
                    onChange={handleChange}
                    required
                />
                
                <button type="submit">Registrar</button>
                <button type="button" onClick={() => navigate(-1)}>Regresar</button>
            </form>
        </div>
    );
}