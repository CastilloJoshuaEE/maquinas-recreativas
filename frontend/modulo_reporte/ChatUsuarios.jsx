import React, { useState, useEffect } from 'react';
import { useAuth } from '../src/context/AuthContext';
import { useNavigate, useParams, useLocation } from 'react-router-dom';
import '../css/modulo_reporte/chatUsuarios.css';
import api from '../src/utils/api';

const ChatUsuarios = ({ currentUser, asPanel = false, onClose }) => {
    const { reporteId, emisorId, destinatarioId } = useParams();
    const location = useLocation();
    const queryParams = new URLSearchParams(location.search);
    const currentUserId = queryParams.get('currentUserId');
    const [destinatario, setDestinatario] = useState(null);
    const [comentarios, setComentarios] = useState([]);
    const [nuevoComentario, setNuevoComentario] = useState('');
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [usuariosChat, setUsuariosChat] = useState([]);
    const [selectedReporte, setSelectedReporte] = useState(null);
    const [reportes, setReportes] = useState([]);
    const navigate = useNavigate();

    const userId = currentUser?.ID_Usuario || currentUserId;

    useEffect(() => {
        if (!userId) {
            navigate('/login');
        }
    }, [userId, navigate]);

    useEffect(() => {
        const cargarDatosIniciales = async () => {
            try {
                setLoading(true);
                
                const { data: usuariosData } = await api.get(`/reportes/usuarios-chat?userId=${userId}`);
                
                if (!usuariosData.success) {
                    throw new Error(usuariosData.message || 'Error al cargar usuarios');
                }
                
                setUsuariosChat(usuariosData.usuarios);

                if (reporteId) {
                    const { data: reporteData } = await api.get(`/reportes/${reporteId}`);
                    
                    if (reporteData.success) {
                        const reporte = reporteData.reporte;
                        setSelectedReporte(reporte);
                        
                        const destId = reporte.ID_Usuario_Emisor === userId 
                            ? reporte.ID_Usuario_Destinatario 
                            : reporte.ID_Usuario_Emisor;
                        
                        const usuarioDest = usuariosData.usuarios.find(u => u.ID_Usuario == destId);
                        if (usuarioDest) {
                            setDestinatario(usuarioDest);
                            await cargarComentariosReporte(reporteId);
                        }
                    }
                }
            } catch (err) {
                setError(err.message);
            } finally {
                setLoading(false);
            }
        };

        if (userId) {
            cargarDatosIniciales();
        }
    }, [userId, reporteId]);

    const cargarComentariosReporte = async (idReporte) => {
        try {
            const { data } = await api.get(`/comentarios/reporte/${idReporte}`);
            
            if (data.success) {
                setComentarios(data.data || []);
            } else {
                setComentarios([]);
            }
        } catch (err) {
            console.error('Error al cargar comentarios:', err);
            setComentarios([]);
        }
    };

    const cargarReportesUsuario = async (userId) => {
        try {
            const { data } = await api.get(`/reportes/usuario/${userId}`);
            
            if (data.success) {
                setReportes(data.reportes);
            }
        } catch (err) {
            console.error('Error al cargar reportes:', err);
        }
    };

    const handleEnviarComentario = async (e) => {
        e.preventDefault();
        
        const token = localStorage.getItem('token');
        await api.post('/historial-actividades', {
            descripcion: `El usuario tuvo una conversación`
        }, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        
        if (!nuevoComentario.trim()) return;

        try {
            let idReporte = selectedReporte?.ID_Reporte;
            
            if (!idReporte && destinatario) {
                const { data } = await api.post('/reportes/crear', {
                    ID_Usuario_Emisor: userId,
                    ID_Usuario_Destinatario: destinatario.ID_Usuario,
                    descripcion: `Chat con ${destinatario.nombre} ${destinatario.apellido}`
                });
                
                if (data.success) {
                    idReporte = data.reporteId;
                    setSelectedReporte({ ID_Reporte: idReporte });
                    await cargarReportesUsuario(userId);
                }
            }

            const { data: comentarioData } = await api.post('/comentarios', {
                ID_Reporte: idReporte,
                ID_Usuario_Emisor: userId,
                comentario: nuevoComentario
            });
            
            if (comentarioData.success) {
                await cargarComentariosReporte(idReporte);
                setNuevoComentario('');
            }
        } catch (err) {
            setError(err.message);
        }
    };

    const handleSeleccionarUsuario = async (usuario) => {
        try {
            setDestinatario(usuario);
            setLoading(true);
            
            const { data } = await api.get(`/reportes/chat/${userId}/${usuario.ID_Usuario}`);
            
            if (data.success && data.reportes.length > 0) {
                setReportes(data.reportes);
                setSelectedReporte(data.reportes[0]);
                await cargarComentariosReporte(data.reportes[0].ID_Reporte);
            } else {
                setComentarios([]);
                setSelectedReporte(null);
            }
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    const handleSeleccionarReporte = async (reporte) => {
        setSelectedReporte(reporte);
        await cargarComentariosReporte(reporte.ID_Reporte);
    };

    if (loading) return <div className="loading">Cargando chat...</div>;
    if (error) return <div className="error">{error}</div>;

    return (
        <div className={`chat-modal-container ${asPanel ? 'panel-mode' : 'window-mode'}`}>
            {asPanel && (
                <div className="chat-modal-header">
                    <h3>Chat de Usuarios</h3>
                    <button className="chat-close-button" onClick={onClose}>
                        &times;
                    </button>
                </div>
            )}
            
            <div className={`chat-container ${asPanel ? 'modal-scrollable' : ''}`}>
                <div className="chat-sidebar">
                    <h3>Conversaciones</h3>
                    <ul className="usuarios-list">
                        {usuariosChat.map((usuario) => {
                            const esSeleccionado = destinatario?.ID_Usuario === usuario.ID_Usuario;
                            return (
                                <li
                                    key={usuario.ID_Usuario}
                                    onClick={() => handleSeleccionarUsuario(usuario)}
                                    className={`usuario-chat-item ${esSeleccionado ? 'activo' : ''}`}
                                    style={{ cursor: 'pointer', color: 'black' }}
                                >
                                    <div className="usuario-info">
                                        <strong>{usuario.nombre} {usuario.apellido}</strong> {usuario.tipo}, [{usuario.email}]
                                    </div>
                                </li>
                            );
                        })}
                    </ul>
                </div>

                <div className="chat-main">
                    {destinatario ? (
                        <>
                            <div className="chat-header">
                                <h3>Chat con {destinatario.nombre} {destinatario.apellido}</h3>
                                <small>{destinatario.email} - {destinatario.tipo}</small>
                            </div>

                            <div className="reportes-list">
                                <select 
                                    value={selectedReporte?.ID_Reporte || ''}
                                    onChange={(e) => {
                                        const reporte = reportes.find(r => r.ID_Reporte == e.target.value);
                                        if (reporte) handleSeleccionarReporte(reporte);
                                    }}
                                >
                                    <option value="">Seleccionar reporte</option>
                                    {reportes.map(reporte => (
                                        <option key={reporte.ID_Reporte} value={reporte.ID_Reporte}>
                                            Reporte #{reporte.ID_Reporte} - {reporte.estado}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div className="chat-mensajes">
                                {comentarios.length > 0 ? (
                                    comentarios.map((comentario) => (
                                        <div key={comentario.ID_Comentario} className={`comentario-item ${comentario.ID_Usuario_Emisor === userId ? 'emisor' : 'receptor'}`}>
                                            <p><strong>{comentario.nombre} {comentario.apellido}</strong>: {comentario.comentario}</p>
                                            <small>{new Date(comentario.fecha_hora).toLocaleString()}</small>
                                        </div>
                                    ))
                                ) : (
                                    <p>No hay comentarios aún.</p>
                                )}
                            </div>

                            <form className="chat-form" onSubmit={handleEnviarComentario}>
                                <textarea
                                    value={nuevoComentario}
                                    onChange={(e) => setNuevoComentario(e.target.value)}
                                    placeholder="Escribe un comentario..."
                                    required
                                />
                                <button type="submit">Enviar</button>
                            </form>
                        </>
                    ) : (
                        <div className="no-chat-selected">
                            <p>Selecciona un usuario para chatear</p>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

export default ChatUsuarios;