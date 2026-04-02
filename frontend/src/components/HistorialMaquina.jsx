import React, { useState, useEffect } from 'react';
import api from '../utils/api';
import './HistorialMaquina.css';

const HistorialMaquina = ({ idMaquina, nombreMaquina, onClose }) => {
    const [historial, setHistorial] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [pagina, setPagina] = useState(1);
    const [paginacion, setPaginacion] = useState({
        pagina_actual: 1,
        total_paginas: 1,
        total: 0
    });
    const [filtros, setFiltros] = useState({
        fecha_inicio: '',
        fecha_fin: '',
        tipo_usuario: '',
        accion: ''
    });

    useEffect(() => {
        cargarHistorial();
    }, [idMaquina, pagina, filtros]);

    const cargarHistorial = async () => {
        setLoading(true);
        try {
            let url = idMaquina 
                ? `/historial/maquina/${idMaquina}?pagina=${pagina}&por_pagina=20`
                : `/historial/general?pagina=${pagina}&por_pagina=20`;

            // Añadir filtros si existen
            if (filtros.fecha_inicio) url += `&fecha_inicio=${filtros.fecha_inicio}`;
            if (filtros.fecha_fin) url += `&fecha_fin=${filtros.fecha_fin}`;
            if (filtros.tipo_usuario) url += `&tipo_usuario=${filtros.tipo_usuario}`;
            if (filtros.accion) url += `&accion=${filtros.accion}`;

            const { data } = await api.get(url);
            
            if (data.success) {
                setHistorial(data.historial);
                setPaginacion(data.paginacion);
            } else {
                setError(data.message || 'Error al cargar historial');
            }
        } catch (err) {
            console.error('Error:', err);
            setError('Error de conexión al cargar historial');
        } finally {
            setLoading(false);
        }
    };

    const handleFiltroChange = (e) => {
        const { name, value } = e.target;
        setFiltros(prev => ({ ...prev, [name]: value }));
        setPagina(1); // Resetear a primera página al cambiar filtros
    };

    const limpiarFiltros = () => {
        setFiltros({
            fecha_inicio: '',
            fecha_fin: '',
            tipo_usuario: '',
            accion: ''
        });
        setPagina(1);
    };

    const getIconoAccion = (accion) => {
        const iconos = {
            'Registro': '➕',
            'Montaje': '🔧',
            'Comprobación': '',
            'Distribución': '📦',
            'Mantenimiento': '⚙️',
            'Reparación': '🔨',
            'Retirada': '',
            'Ensamblaje': '🛠️',
            'Actualización': '',
            'Cambio de estado': '🔄'
        };
        return iconos[accion] || '📋';
    };

    const getColorAccion = (accion) => {
        const colores = {
            'Registro': '#4CAF50',
            'Montaje': '#2196F3',
            'Comprobación': '#9C27B0',
            'Distribución': '#FF9800',
            'Mantenimiento': '#FFC107',
            'Reparación': '#F44336',
            'Retirada': '#795548',
            'Ensamblaje': '#00BCD4',
            'Actualización': '#3F51B5',
            'Cambio de estado': '#607D8B'
        };
        return colores[accion] || '#9E9E9E';
    };

    const formatearFecha = (fecha) => {
        return new Date(fecha).toLocaleString('es-ES', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    };

    return (
        <div className="historial-modal-overlay">
            <div className="historial-modal">
                <div className="historial-header">
                    <h2>
                        {nombreMaquina 
                            ? `Historial de: ${nombreMaquina}` 
                            : 'Historial General de Máquinas'}
                    </h2>
                    <button className="close-btn" onClick={onClose}>×</button>
                </div>

                {/* Filtros */}
                <div className="historial-filtros">
                    <input
                        type="date"
                        name="fecha_inicio"
                        value={filtros.fecha_inicio}
                        onChange={handleFiltroChange}
                        placeholder="Fecha inicio"
                    />
                    <input
                        type="date"
                        name="fecha_fin"
                        value={filtros.fecha_fin}
                        onChange={handleFiltroChange}
                        placeholder="Fecha fin"
                    />
                    <select
                        name="tipo_usuario"
                        value={filtros.tipo_usuario}
                        onChange={handleFiltroChange}
                    >
                        <option value="">Todos los usuarios</option>
                        <option value="Tecnico">Técnicos</option>
                        <option value="Logistica">Logística</option>
                        <option value="Administrador">Administradores</option>
                    </select>
                    <input
                        type="text"
                        name="accion"
                        value={filtros.accion}
                        onChange={handleFiltroChange}
                        placeholder="Buscar acción..."
                    />
                    <button className="limpiar-filtros" onClick={limpiarFiltros}>
                        Limpiar filtros
                    </button>
                </div>

                {loading && <div className="historial-loading">Cargando historial...</div>}
                
                {error && <div className="historial-error">{error}</div>}

                {!loading && !error && (
                    <>
                        <div className="historial-timeline">
                            {historial.length === 0 ? (
                                <p className="sin-registros">No hay registros de actividad</p>
                            ) : (
                                historial.map((item, index) => (
                                    <div key={item.ID_Historial || index} className="timeline-item">
                                        <div className="timeline-icon" style={{ backgroundColor: getColorAccion(item.accion) }}>
                                            {getIconoAccion(item.accion)}
                                        </div>
                                        <div className="timeline-content">
                                            <div className="timeline-header">
                                                <span className="timeline-accion">{item.accion}</span>
                                                <span className="timeline-fecha">
                                                    {formatearFecha(item.fecha_hora)}
                                                </span>
                                            </div>
                                            
                                            <div className="timeline-usuario">
                                                <strong>👤 Usuario:</strong> {item.usuario_nombre} {item.usuario_apellido} ({item.tipo_usuario})
                                            </div>
                                            
                                            {!nombreMaquina && item.Nombre_Maquina && (
                                                <div className="timeline-maquina">
                                                    <strong>🕹️ Máquina:</strong> {item.Nombre_Maquina}
                                                </div>
                                            )}
                                            
                                            {item.descripcion && (
                                                <div className="timeline-descripcion">
                                                    <strong> Descripción:</strong> {item.descripcion}
                                                </div>
                                            )}
                                            
                                            {(item.estado_anterior || item.estado_nuevo) && (
                                                <div className="timeline-cambio-estado">
                                                    <strong>🔄 Cambio de estado:</strong>
                                                    {item.estado_anterior && (
                                                        <span className="estado-anterior">{item.estado_anterior}</span>
                                                    )}
                                                    {item.estado_anterior && item.estado_nuevo && ' → '}
                                                    {item.estado_nuevo && (
                                                        <span className="estado-nuevo">{item.estado_nuevo}</span>
                                                    )}
                                                </div>
                                            )}
                                            
                                            {item.etapa_anterior && item.etapa_nueva && (
                                                <div className="timeline-cambio-etapa">
                                                    <strong>📊 Cambio de etapa:</strong>
                                                    <span className="etapa-anterior">{item.etapa_anterior}</span>
                                                    {' → '}
                                                    <span className="etapa-nueva">{item.etapa_nueva}</span>
                                                </div>
                                            )}
                                            
                                            {item.detalles_adicionales && (
                                                <div className="timeline-detalles">
                                                    <strong>🔍 Detalles adicionales:</strong>
                                                    <pre>{JSON.stringify(item.detalles_adicionales, null, 2)}</pre>
                                                </div>
                                            )}
                                            
                                            <div className="timeline-ip">
                                                <small> IP: {item.ip_address || 'Desconocida'}</small>
                                            </div>
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>

                        {/* Paginación */}
                        {paginacion.total_paginas > 1 && (
                            <div className="historial-paginacion">
                                <button
                                    onClick={() => setPagina(p => Math.max(1, p - 1))}
                                    disabled={pagina === 1}
                                >
                                    Anterior
                                </button>
                                <span>
                                    Página {pagina} de {paginacion.total_paginas}
                                    {' '}(Total: {paginacion.total} registros)
                                </span>
                                <button
                                    onClick={() => setPagina(p => Math.min(paginacion.total_paginas, p + 1))}
                                    disabled={pagina === paginacion.total_paginas}
                                >
                                    Siguiente
                                </button>
                            </div>
                        )}
                    </>
                )}
            </div>
        </div>
    );
};

export default HistorialMaquina;