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

    // En NotificacionesPanel.jsx - MEJORAR
const marcarComoLeida = async (notificacionId) => {
    try {
        setError(''); // Limpiar errores previos
        
        console.log('Marcando notificación como leída:', notificacionId);
        
        const { data } = await api.post(`/notificaciones/${notificacionId}/marcarla-leida`);

        // Verificar que la respuesta existe
        if (!data) {
            throw new Error('No se recibió respuesta del servidor');
        }

        console.log('Respuesta del servidor:', data);

        // Verificar que data es un objeto
        if (typeof data !== 'object') {
            throw new Error('Respuesta del servidor no válida');
        }

        if (!data.success) {
            throw new Error(data.message || 'Error al marcar como leída');
        }

        // Actualizar el estado local
        setNotificaciones(prev => 
            prev.map(n => 
                n.ID_Notificaciones === notificacionId ? { ...n, leida: 1 } : n
            )
        );
        
        // Actualizar contador de no leídas
        setUnreadCount(prev => Math.max(prev - 1, 0));

        // Opcional: mostrar mensaje de éxito
        console.log('Notificación marcada como leída exitosamente');

    } catch (err) {
        console.error('Error al marcar como leída:', err);
        setError(err.message || 'Error al marcar la notificación');
        
        // Opcional: mostrar un toast o alerta al usuario
        alert('Error: ' + (err.message || 'No se pudo marcar la notificación'));
    }
};
    const marcarTodasComoLeidas = async () => {
        try {
const { data } = await api.post('/notificaciones/marcarla-todas-leidas');
            
if (!data.success) {
    throw new Error(data.message || 'Error al marcar todas como leída');
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
                <span className="bell-icon" style={{ color: "black", marginRight: "10px" }}>🔔
                Notificaciones</span>
                {unreadCount > 0 && (
                    <span className="unread-count"style={{ color: "black" }}>{unreadCount}</span>
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