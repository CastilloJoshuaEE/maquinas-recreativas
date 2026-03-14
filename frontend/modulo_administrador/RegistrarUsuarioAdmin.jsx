import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import '../css/modulo_administrador/registrar_usuario.css';
import { AdminHeader } from '../modulo_usuario/AdminHeader';
import api from '../src/utils/api';

export default function RegistrarUsuarioAdmin() {
    const [formData, setFormData] = useState({
        nombre: '',
        apellido: '',
        ci: '',
        email: '',
        usuario_asignado: '',
        contrasena: '',
        tipo: 'Logistica',
        especialidad: '',
        estado: 'Activo'
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
        if (!window.confirm('¿Está seguro de registrar al usuario?')) {
            return;
        }
        try {
            if (!formData.usuario_asignado || formData.usuario_asignado.trim() === '') {
                setError('El campo Usuario es obligatorio');
                return;
            }
            
            if (!formData.estado) {
                setError('Debe seleccionar un estado para el usuario');
                return;
            }

            const payload = {
                nombre: formData.nombre,
                apellido: formData.apellido,
                ci: formData.ci,
                email: formData.email,
                usuario_asignado: formData.usuario_asignado,
                contrasena: formData.contrasena,
                tipo: formData.tipo,
                estado: formData.estado
            };

            if (formData.tipo === 'Tecnico') {
                if (!formData.especialidad || formData.especialidad.trim() === '') {
                    setError('Debe seleccionar una especialidad para el técnico');
                    return;
                }
                payload.especialidad = formData.especialidad;
            }

            const token = localStorage.getItem('token');
            const { response, data } = await api.post('/administrador/usuarios', payload, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            
            if (!response.ok) {
                throw new Error(data.message || 'Error al registrar usuario');
            }
    
            if (data.success) {
                await api.post('/historial-actividades', {
                    descripcion: `El usuario registró un nuevo usuario: ${formData.nombre} ${formData.apellido} (${formData.usuario_asignado})`
                }, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                
                setSuccess(true);
                setTimeout(() => navigate('/admin/gestion-usuarios'), 2000);
            } else {
                setError(data.message || 'Error al registrar usuario');
            }
        } catch (err) {
            setError(err.message || 'Error de conexión con el servidor');
        }
    };

    return (
        <div className="register-admin-container">
            <AdminHeader/>
            <h2>Registrar Nuevo Usuario (Admin)</h2>
            {error && <div className="error-message">{error}</div>}
            {success && <div className="success-message">¡Usuario registrado correctamente! Redirigiendo...</div>}

            <form onSubmit={handleSubmit}>
                <div className="form-group">
                    <label>Nombre:</label>
                    <input
                        type="text"
                        name="nombre"
                        value={formData.nombre}
                        onChange={handleChange}
                        required
                    />
                </div>

                <div className="form-group">
                    <label>Apellido:</label>
                    <input
                        type="text"
                        name="apellido"
                        value={formData.apellido}
                        onChange={handleChange}
                        required
                    />
                </div>

                <div className="form-group">
                    <label>Cédula:</label>
                    <input
                        type="text"
                        name="ci"
                        value={formData.ci}
                        onChange={handleChange}
                        required
                        maxLength="10"
                    />
                </div>

                <div className="form-group">
                    <label>Email:</label>
                    <input
                        type="email"
                        name="email"
                        value={formData.email}
                        onChange={handleChange}
                        required
                    />
                </div>

                <div className="form-group">
                    <label>Usuario:</label>
                    <input
                        type="text"
                        name="usuario_asignado"
                        value={formData.usuario_asignado}
                        onChange={handleChange}
                        required
                    />
                </div>

                <div className="form-group">
                    <label>Contraseña:</label>
                    <input
                        type="password"
                        name="contrasena"
                        value={formData.contrasena}
                        onChange={handleChange}
                        required
                    />
                </div>

                <div className="form-group">
                    <label>Tipo de Usuario:</label>
                    <select
                        name="tipo"
                        value={formData.tipo}
                        onChange={handleChange}
                        required
                    >
                        <option value="Administrador">Administrador del sistema</option>
                        <option value="Contabilidad">Contabilidad</option>
                        <option value="Logistica">Logística</option>
                        <option value="Tecnico">Técnico</option>
                    </select>
                </div>

                {formData.tipo === 'Tecnico' && (
                    <div className="form-group">
                        <label>Especialidad:</label>
                        <select
                            name="especialidad"
                            value={formData.especialidad}
                            onChange={handleChange}
                            required
                        >
                            <option value="">Seleccione...</option>
                            <option value="Ensamblador">Ensamblador</option>
                            <option value="Comprobador">Comprobador</option>
                            <option value="Mantenimiento">Mantenimiento</option>
                        </select>
                    </div>
                )}

                <div className="form-group">
                    <label>Estado:</label>
                    <select
                        name="estado"
                        value={formData.estado}
                        onChange={handleChange}
                        required
                    >
                        <option value="Activo">Activo</option>
                        <option value="Inhabilitado">Inhabilitado</option>
                        <option value="Pendiente de asignacion">Pendiente de asignacion</option>
                    </select>
                </div>

                <div className="form-actions">
                    <button type="submit">Registrar Usuario</button>
                    <button onClick={() => navigate('/dashboard/admin')}>Cancelar</button>
                </div>
            </form>
        </div>
    );
}