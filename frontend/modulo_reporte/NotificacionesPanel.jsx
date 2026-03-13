import React, { useState, useEffect } from 'react';
import { useAuth } from '../src/context/AuthContext';
import { useNavigate } from 'react-router-dom';
import '../css/modulo_reporte/gestionReportes.css';
import api from '../src/utils/api';

const NotificacionesPanel = ({ currentUser }) => {
    const [notificaciones, setNotificaciones] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [unreadCount, setUnreadCount] = useState(0);
    const navigate = useNavigate();

    const cargarNotificaciones = async () => {
        try {
            setLoading(true);
            const { data } = await api.get(`/notificaciones/${currentUser.ID_Usuario}`);
            
            if (!data.success) {
                throw new Error(data.message || 'Error al cargar notificaciones');
            }
            
            setNotificaciones(data.notificaciones || []);
            const unread = data.notificaciones.filter(n => !n.leida).length;
            setUnreadCount(unread);
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };
    
    useEffect(() => {
        cargarNotificaciones();
        const intervalo = setInterval(cargarNotificaciones, 30000);
        return () => clearInterval(intervalo);
    }, [currentUser]);

    const marcarComoLeida = async (notificacionId) => {
        try {
            const { response } = await api.post(`/notificaciones/${notificacionId}/marcarla-leida`, {});
            
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Error al marcar como leída');
            }
            
            setUnreadCount(prev => prev - 1);
            setNotificaciones(prev => 
                prev.map(n => 
                    n.ID_Notificaciones === notificacionId ? { ...n, leida: 1 } : n
                )
            );
        } catch (err) {
            setError(err.message);
        }
    };

    const marcarTodasComoLeidas = async () => {
        try {
            const { response } = await api.post('/notificaciones/marcarla-todas-leidas', {});
            
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Error al marcar todas como leídas');
            }
            
            setNotificaciones(prev => 
                prev.map(n => ({ ...n, leida: 1 }))
            );
            setUnreadCount(0);
        } catch (err) {
            setError(err.message);
        }
    };

    const handleVerReporte = (reporteId) => {
        navigate(`/reportes/chat?reporteId=${reporteId}&currentUserId=${currentUser.ID_Usuario}`);
    };

    if (loading) {
        return (
            <div className="loading">
                <div className="spinner"></div>
                <p>Cargando notificaciones...</p>
            </div>
        );
    }

    if (error) {
        return (
            <div className="error">
                <span className="error-icon">!</span>
                <p>{error}</p>
                <button onClick={cargarNotificaciones}>Reintentar</button>
            </div>
        );
    }

    return (
        <div className="notificaciones-container">
            <h2>
                <span className="bell-icon">🔔</span>
                Notificaciones
                {unreadCount > 0 && (
                    <span className="unread-count">{unreadCount}</span>
                )}
            </h2>
            
            <div className="notificaciones-header">
                <span className="notificaciones-count">
                    {notificaciones.length} notificaciones
                </span>
                {unreadCount > 0 && (
                    <button 
                        className="marcar-todas-btn"
                        onClick={marcarTodasComoLeidas}
                    >
                        Marcar todas como leídas
                    </button>
                )}
            </div>
            
            {notificaciones.length > 0 ? (
                <ul className="notificaciones-list">
                    {notificaciones.map(notificacion => (
                        <li 
                            key={notificacion.ID_Notificaciones} 
                            className={`notificacion-item ${notificacion.leida ? '' : 'no-leida'}`}
                        >
                            <div className="notificacion-mensaje">
                                {notificacion.mensaje}
                                {notificacion.reporte_descripcion && (
                                    <div className="reporte-descripcion">
                                        <small>Reporte: {notificacion.reporte_descripcion}</small>
                                    </div>
                                )}
                            </div>
                            <div className="notificacion-footer">
                                <span className="notificacion-fecha">
                                    {new Date(notificacion.fecha_hora).toLocaleString()}
                                </span>
                                {notificacion.emisor_nombre && (
                                    <span className="notificacion-remitente">
                                        De: {notificacion.emisor_nombre} {notificacion.emisor_apellido}
                                    </span>
                                )}
                                <div className="notificacion-acciones">
                                    <button 
                                        className="marcar-leida-btn"
                                        onClick={() => marcarComoLeida(notificacion.ID_Notificaciones)}
                                        disabled={notificacion.leida}
                                    >
                                        Marcar como leída
                                    </button>
                                    {notificacion.ID_Reporte && (
                                        <button
                                            className="ver-reporte"
                                            onClick={() => handleVerReporte(notificacion.ID_Reporte)}
                                        >
                                            Ver chat relacionado
                                        </button>
                                    )}
                                </div>
                            </div>
                        </li>
                    ))}
                </ul>
            ) : (
                <p className="no-notificaciones">No tienes notificaciones</p>
            )}
        </div>
    );
};

export default NotificacionesPanel;