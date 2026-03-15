import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import '../css/modulo_administrador/consultar_usuarios.css';
import { AdminHeader } from '../modulo_usuario/AdminHeader';
import api from '../src/utils/api';

export default function EditarUsuario({ modo = 'actualizar' }) {
    const { uuid } = useParams();
    const [usuario, setUsuario] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [success, setSuccess] = useState(false);
    const navigate = useNavigate();

    useEffect(() => {
        const fetchUsuario = async () => {
            try {
                const token = localStorage.getItem('token');
                const { response, data } = await api.get(`/administrador/usuarios/${uuid}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });
                
                if (!response.ok) throw new Error('Error al cargar usuario');
                
                if (data.success) {
                    const userData = data.usuario;
                    if (userData.tipo === 'Tecnico' && !userData.ID_Tecnico) {
                        userData.ID_Tecnico = uuid;
                    }
                    setUsuario(userData);
                }
            } catch (err) {
                setError(err.message || 'Error de conexión con el servidor');
            } finally {
                setLoading(false);
            }
        };
        
        fetchUsuario(uuid);
    }, [uuid]);

// En EditarUsuario.jsx - handleSubmit()
const handleSubmit = async (e) => {
    e.preventDefault();
    if (!window.confirm('¿Está seguro de guardar los cambios?')) {
        return;
    }
    
    try {
        let formData;
        const token = localStorage.getItem('token');
        
        if (modo === 'actualizar') {
            formData = {
                ID_Usuario: uuid,
                nombre: e.target.nombre.value,
                apellido: e.target.apellido.value,
                email: e.target.email.value,
                usuario_asignado: e.target.usuario_asignado.value,
                ci: e.target.ci.value,
                tipo: e.target.tipo.value,
                estado: usuario.estado
            };

            if (formData.tipo === 'Tecnico') {
                formData.ID_Tecnico = uuid;
                formData.especialidad = e.target.especialidad?.value;
            }

            if (e.target.contrasena) {
                const nuevaContrasena = e.target.contrasena.value.trim();
                if (nuevaContrasena !== '') {
                    formData.contrasena = nuevaContrasena;
                }
            }
            
            // ✅ Usar PUT para actualización completa
            const { response, data } = await api.put(`/administrador/usuarios/${uuid}`, formData, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            
            if (!response.ok) {
                throw new Error(data.message || 'Error en la solicitud');
            }

            if (data.success) {
                setSuccess(true);
                setTimeout(() => navigate('/admin/gestion-usuarios/consultar-usuarios'), 2000);
            } else {
                setError(data.message || 'Error al actualizar usuario');
            }
        } else {
            // ✅ Modo estado - usar PATCH
            formData = {
                ID_Usuario: uuid,
                estado: e.target.estado.value
            };
            
            const { response, data } = await api.patch(`/administrador/usuarios/${uuid}`, formData, {
                headers: { 'Authorization': `Bearer ${token}` }
            });

            if (!response.ok) {
                throw new Error(data.message || 'Error en la solicitud');
            }

            if (data.success) {
                setSuccess(true);
                setTimeout(() => navigate('/admin/gestion-usuarios/consultar-usuarios'), 2000);
            } else {
                setError(data.message || 'Error al cambiar estado del usuario');
            }
        }
    } catch (err) {
        setError(err.message || 'Error de conexión con el servidor');
        console.error('Error al guardar cambios:', err);
    }
};

    if (loading) return <div className="loading">Cargando usuario...</div>;
    if (error) return <div className="error">{error}</div>;
    if (!usuario) return <div>Usuario no encontrado</div>;

    return (
        <div className="edit-user-container">
            <AdminHeader />
            <h2>{modo === 'actualizar' ? 'Editar Usuario' : 'Editar Estado de Usuario'}: {usuario.nombre} {usuario.apellido}</h2>
            
            {error && <div className="error-message">{error}</div>}
            {success && <div className="success-message">¡Operación realizada correctamente! Redirigiendo...</div>}

            <form onSubmit={handleSubmit}>
                
                {modo === 'actualizar' ? (
                    <>
                        <div className="form-group">
                            <label>Nombre:</label>
                            <input 
                                type="text" 
                                name="nombre" 
                                defaultValue={usuario.nombre} 
                                required
                            />
                        </div>

                        <div className="form-group">
                            <label>Apellido:</label>
                            <input 
                                type="text" 
                                name="apellido" 
                                defaultValue={usuario.apellido} 
                                required
                            />
                        </div>

                        <div className="form-group">
                            <label>Email:</label>
                            <input 
                                type="email" 
                                name="email" 
                                defaultValue={usuario.email} 
                                required
                            />
                        </div>
                        
                        <div className="form-group">
                            <label>Cédula:</label>
                            <input 
                                type="text" 
                                name="ci" 
                                defaultValue={usuario.ci} 
                                required
                            />
                        </div>
                        
                        <div className="form-group">
                            <label>Tipo de Usuario:</label>
                            <select 
                                name="tipo" 
                                defaultValue={usuario.tipo}
                                required
                            >
                                <option value="Administrador">Administrador del sistema</option>
                                <option value="Contabilidad">Contabilidad</option>
                                <option value="Logistica">Logística</option>
                                <option value="Tecnico">Técnico</option>
                            </select>
                        </div>
                        
                        <div className="form-group">
                            <label>Usuario Asignado:</label>
                            <input 
                                type="text" 
                                name="usuario_asignado" 
                                defaultValue={usuario.usuario_asignado} 
                                required
                            />
                        </div>
                        <div className="form-group">
                            <label>Contraseña (dejar vacía si no deseas cambiarla):</label>
                            <input 
                                type="password" 
                                name="contrasena" 
                                placeholder="Nueva contraseña (opcional)" 
                            />
                        </div>

                        <div className="form-group">
                            <label>Estado:</label>
                            <input 
                                type="text" 
                                value={usuario.estado} 
                                readOnly
                            />
                        </div>

                        {usuario.tipo === 'Tecnico' && (
                            <div className="form-group">
                                <label>Especialidad:</label>
                                <select 
                                    name="especialidad" 
                                    defaultValue={usuario.Especialidad || ''}
                                    required
                                >
                                    <option value="Ensamblador">Ensamblador</option>
                                    <option value="Comprobador">Comprobador</option>
                                    <option value="Mantenimiento">Mantenimiento</option>
                                </select>
                            </div>
                        )}
                    </> 

                ) : ( 
                    <>
                        <div className="form-group">                    
                            <label>Estado Actual:</label>
                            <input 
                                type="text" 
                                value={usuario.estado} 
                                readOnly
                            />
                        </div>

                        <div className="form-group">
                            <label>Nuevo Estado:</label>
                            <select name="estado" required>
                                <option value="Activo">Activo</option>
                                <option value="Inhabilitado">Inhabilitado</option>
                                <option value="Pendiente de asignacion">Pendiente de asignación</option>
                            </select>
                        </div>
                    </>
                )}
                
                <div className="form-actions">
                    <button type="submit">Guardar Cambios</button>
                    <button type="button" onClick={() => navigate('/admin/gestion-usuarios/consultar-usuarios')}>
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    );
}