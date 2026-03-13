import React, { useState, useEffect, useRef } from 'react';
import { useAuth } from '../src/context/AuthContext';
import { useNavigate, useLocation } from 'react-router-dom';
import '../css/modulo_reporte/gestionReportes.css';
import Modal from 'react-modal';
import api from '../src/utils/api';

if (typeof window !== 'undefined') {
  Modal.setAppElement('#root');
}

const GestionReportes = ({ adminMode = false, onClose }) => {
    const { currentUser } = useAuth();
    const [reportes, setReportes] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [nuevoReporte, setNuevoReporte] = useState({ 
        destinatario: '', 
        descripcion: '',
        tipoDestinatario: ''
    });
    const [usuarios, setUsuarios] = useState([]);
    const [filtroEstado, setFiltroEstado] = useState('todos');
    const [tiposUsuario] = useState(['Tecnico', 'Contabilidad', 'Logistica', 'Administrador', 'Usuario']);
    const [statusMessage, setStatusMessage] = useState('');
    const [isDisabledUser, setIsDisabledUser] = useState(false);
    const [modalIsOpen, setModalIsOpen] = useState(false);
    const modalRef = useRef(null);

    const navigate = useNavigate();
    const location = useLocation();

    useEffect(() => {
        if (onClose) {
            setModalIsOpen(true);
        }
        
        if (location.state?.message) {
            setStatusMessage(location.state.message);
        }
        if (location.state?.isDisabledUser) {
            setIsDisabledUser(true);
            cargarAdministradores();
        }
    }, [location.state]);

    useEffect(() => {
        if (!adminMode && !isDisabledUser) {
            cargarReportes();
        } else if (isDisabledUser) {
            cargarAdministradores();
        }
    }, [adminMode, currentUser, isDisabledUser]);

    const cargarReportes = async () => {
        try {
            const { data } = await api.get(`/reportes/usuario/${currentUser.ID_Usuario}`);
            setReportes(data.reportes);
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    const cargarAdministradores = async () => {
        try {
            const { data } = await api.get(`/usuarios/por-tipo?tipo=Administrador&emisorId=${currentUser.ID_Usuario}`);
            if (data.success) {
                setUsuarios(data.usuarios);
                if (isDisabledUser && data.usuarios.length > 0) {
                    setNuevoReporte(prev => ({
                        ...prev,
                        destinatario: data.usuarios[0].ID_Usuario,
                        tipoDestinatario: 'Administrador'
                    }));
                }
            }
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    const cerrarModal = () => {
        if (modalIsOpen) {
            setModalIsOpen(false);
            if (onClose) {
                onClose();
            }
        }
    };

    useEffect(() => {
        if (modalIsOpen && modalRef.current) {
            modalRef.current.focus();
        }
    }, [modalIsOpen]);

    const cargarUsuariosPorTipo = async (tipo) => {
        try {
            const { data } = await api.get(`/usuarios/por-tipo?tipo=${encodeURIComponent(tipo)}&emisorId=${currentUser.ID_Usuario}`);
            setUsuarios(data.usuarios);
        } catch (err) {
            console.error('Error al cargar usuarios:', err);
            setUsuarios([]);
        }
    };

    const handleSubmitReporte = async (e) => {
        e.preventDefault();
        if (!window.confirm('¿Está seguro de enviar reporte?')) {
            return;
        }
        try {
            if (!nuevoReporte.destinatario || !nuevoReporte.descripcion) {
                throw new Error('Debes seleccionar un destinatario y escribir una descripción');
            }

            const descripcionFinal = isDisabledUser 
                ? `[SOLICITUD DE REACTIVACIÓN] ${nuevoReporte.descripcion}`
                : adminMode 
                    ? `[USUARIO RESTRINGIDO] ${nuevoReporte.descripcion}`
                    : nuevoReporte.descripcion;

            const { response, data } = await api.post('/reportes/crear', {
                ID_Usuario_Emisor: currentUser.ID_Usuario,
                ID_Usuario_Destinatario: nuevoReporte.destinatario,
                descripcion: descripcionFinal
            });

            if (!response.ok) throw new Error(data.message || 'Error al crear reporte');

            if (!adminMode && !isDisabledUser) {
                await cargarReportes();
            }

            alert('Reporte enviado correctamente.');
            
            const token = localStorage.getItem('token');
            await api.post('/historial-actividades', {
                descripcion: `El usuario envió un reporte`
            }, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            
            setNuevoReporte({ destinatario: '', descripcion: '', tipoDestinatario: '' });
            setUsuarios([]);
            
            if (isDisabledUser) {
                navigate('/reportes/');
            } else if (onClose) {
                onClose();
            }
        } catch (err) {
            setError(err.message);
        }
    };

    const handleChange = (e) => {
        const { name, value } = e.target;
        setNuevoReporte(prev => ({ ...prev, [name]: value }));
        if (name === 'tipoDestinatario') cargarUsuariosPorTipo(value);
    };

    const handleVerChat = (reporte) => {
        navigate(`/reportes/chat?reporteId=${reporte.ID_Reporte}&currentUserId=${currentUser.ID_Usuario}`);
    };

    const handleActualizarEstado = async (reporteId, nuevoEstado) => {
        try {
            const { data } = await api.put(`/reportes/${reporteId}/estado`, { estado: nuevoEstado });

            if (data.success) {
                const updatedReportes = reportes.map(reporte =>
                    reporte.ID_Reporte === reporteId ? { ...reporte, estado: nuevoEstado } : reporte
                );
                setReportes(updatedReportes);
                
                const token = localStorage.getItem('token');
                await api.post('/historial-actividades', {
                    descripcion: `El usuario actualizó el estado de un reporte`
                }, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
            } else {
                throw new Error(data.message || 'Error al actualizar estado');
            }
        } catch (err) {
            setError(err.message);
        }
    };

    const reportesFiltrados = filtroEstado === 'todos' 
        ? reportes 
        : reportes.filter(r => r.estado === filtroEstado);

    if (!currentUser || !currentUser.ID_Usuario) {
        return <div className="error">Usuario no autenticado</div>;
    }

    if (loading) return <div className="loading">Cargando...</div>;
    if (error) return <div className="error">{error}</div>;

    const contenido = (
        <div className={`gestion-reportes-container ${adminMode ? 'admin-mode' : ''} ${isDisabledUser ? 'disabled-user-mode' : ''}`}
             ref={modalRef}
             tabIndex="-1"
        >
            {statusMessage && (
                <div className="status-message">
                    <p>{statusMessage}</p>
                </div>
            )}

            {isDisabledUser && (
                <div className="disabled-user-notice">
                    <h2>Cuenta Inhabilitada</h2>
                    <p>{statusMessage || "Su cuenta está inhabilitada. Por favor contacte al administrador."}</p>
                </div>
            )}

            {adminMode ? (
                <>
                    <h2>Contactar con Administrador</h2>
                    <p>Estás enviando un reporte como usuario con acceso restringido</p>
                    <form onSubmit={handleSubmitReporte}>
                        <div className="form-group">
                            <label>Administrador:</label>
                            <select 
                                name="destinatario" 
                                value={nuevoReporte.destinatario}
                                onChange={handleChange}
                                required
                            >
                                <option value="">Seleccionar administrador</option>
                                {usuarios.map(user => (
                                    <option key={user.ID_Usuario} value={user.ID_Usuario}>
                                        {user.nombre} {user.apellido} ({user.email})
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div className="form-group">
                            <label>Descripción:</label>
                            <textarea
                                name="descripcion"
                                value={nuevoReporte.descripcion}
                                onChange={handleChange}
                                placeholder="Describe tu situación..."
                                required
                            />
                        </div>
                        <div className="button-group">
                            <a href="/">Volver al inicio</a>
                            <button type="submit" className="btn-enviar">Enviar Reporte</button>
                            {onClose && (
                                <button type="button" onClick={cerrarModal} className="btn-cancel">Cancelar</button>
                            )}
                        </div>
                    </form>
                </>
            ) : (
                <>
                    {!isDisabledUser && (
                        <>
                            <h2>Gestión de Reportes</h2>   
                            <div className="filtros">
                                <label>Filtrar por estado:</label>
                                <select value={filtroEstado} onChange={(e) => setFiltroEstado(e.target.value)}>
                                    <option value="todos">Todos</option>
                                    <option value="Pendiente">Pendientes</option>
                                    <option value="En proceso">En proceso</option>
                                    <option value="Resuelto">Resueltos</option>
                                </select>
                            </div>
                        </>
                    )}

                    <div className="nuevo-reporte">
                        <h3>{isDisabledUser ? 'Solicitud de Reactivación' : 'Crear Nuevo Reporte'}</h3>
                        <form onSubmit={handleSubmitReporte}>
                            {!isDisabledUser && (
                                <>
                                    <div className="form-group">
                                        <label>Área del destinatario:</label>
                                        <select 
                                            name="tipoDestinatario" 
                                            value={nuevoReporte.tipoDestinatario}
                                            onChange={handleChange}
                                            required
                                            disabled={isDisabledUser}
                                        >
                                            <option value="">Seleccionar área</option>
                                            {tiposUsuario.map(tipo => (
                                                <option key={tipo} value={tipo}>{tipo}</option>
                                            ))}
                                        </select>
                                    </div>
                                    <div className="form-group">
                                        <label>Destinatario:</label>
                                        <select 
                                            name="destinatario" 
                                            value={nuevoReporte.destinatario}
                                            onChange={handleChange}
                                            required
                                            disabled={isDisabledUser || !nuevoReporte.tipoDestinatario}
                                        >
                                            <option value="">Seleccionar destinatario</option>
                                            {usuarios.map(user => (
                                                <option key={user.ID_Usuario} value={user.ID_Usuario}>
                                                    {user.nombre} {user.apellido} ({user.email})
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                </>
                            )}
                            <div className="form-group">
                                <label>{isDisabledUser ? 'Explicación para reactivación' : 'Descripción'}:</label>
                                <textarea
                                    name="descripcion"
                                    value={nuevoReporte.descripcion}
                                    onChange={handleChange}
                                    placeholder={isDisabledUser 
                                        ? "Por favor explique por qué desea reactivar su cuenta..." 
                                        : "Describe tu situación..."}
                                    required
                                />
                            </div>
                            <button type="submit" className="btn-enviar">
                                {isDisabledUser ? 'Enviar Solicitud' : 'Enviar Reporte'}
                            </button>
                            <a href="/">Volver al inicio</a>

                        </form>
                    </div>

                    {!isDisabledUser && reportesFiltrados.length > 0 && (
                        <div className="lista-reportes">
                            <h3>Mis Reportes</h3>
                            <table className="tabla-reportes">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Fecha</th>
                                        <th>Descripción</th>
                                        <th>Estado</th>
                                        <th>Emisor</th>
                                        <th>Destinatario</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {reportesFiltrados.map(reporte => (
                                        <tr key={reporte.ID_Reporte}>
                                            <td>{reporte.ID_Reporte}</td>
                                            <td>{new Date(reporte.fecha_hora).toLocaleString()}</td>
                                            <td>{reporte.descripcion}</td>
                                            <td>
                                                <select 
                                                    value={reporte.estado}
                                                    onChange={(e) => handleActualizarEstado(reporte.ID_Reporte, e.target.value)}
                                                >
                                                    <option value="Pendiente">Pendiente</option>
                                                    <option value="En proceso">En proceso</option>
                                                    <option value="Resuelto">Resuelto</option>
                                                </select>
                                            </td>
                                            <td>
                                                {reporte.emisor_nombre && reporte.emisor_apellido
                                                    ? `${reporte.emisor_nombre} ${reporte.emisor_apellido}`
                                                    : 'Sin nombre'}
                                            </td>
                                            <td>
                                                {reporte.destinatario_nombre && reporte.destinatario_apellido
                                                    ? `${reporte.destinatario_nombre} ${reporte.destinatario_apellido}`
                                                    : 'Sin nombre'}
                                            </td>
                                            <td>
                                                <button onClick={() => handleVerChat(reporte)}>Ver Chat</button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </>
            )}
        </div>
    );

    if (onClose) {
        return (
            <Modal
                isOpen={modalIsOpen}
                onRequestClose={cerrarModal}
                onAfterOpen={() => {
                    if (modalRef.current) {
                        modalRef.current.focus();
                    }
                }}
                contentLabel="Gestión de Reportes"
                className="modal-reportes"
                overlayClassName="modal-overlay"
                closeTimeoutMS={200}
                ariaHideApp={true}
                shouldFocusAfterRender={true}
                shouldReturnFocusAfterClose={true}
                aria-modal="true" 
            >
                <button 
                    onClick={cerrarModal} 
                    style={{
                        position: 'absolute',
                        top: '10px',
                        right: '10px',
                        background: 'transparent',
                        border: 'none',
                        fontSize: '1.5rem',
                        cursor: 'pointer',
                        zIndex: 1000
                    }}
                    aria-label="Cerrar modal"
                >
                    ×
                </button>
                {contenido}
            </Modal>
        );
    }

    return contenido;
};

export default GestionReportes;