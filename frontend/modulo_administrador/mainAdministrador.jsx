import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../src/context/AuthContext';
import '../css/modulo_administrador/mainAdministrador.css';
import GestionReportes from '../modulo_reporte/GestionReportes';
import ChatUsuarios from '../modulo_reporte/ChatUsuarios';
import NotificacionesPanel from '../modulo_reporte/NotificacionesPanel';
import api from '../src/utils/api';

export default function Administrador() {
  const [usuarios, setUsuarios] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [showProfileMenu, setShowProfileMenu] = useState(false);
  const [showReportes, setShowReportes] = useState(false);
  const [showChat, setShowChat] = useState(false);
  const [showNotificaciones, setShowNotificaciones] = useState(false);

  const { currentUser, logout } = useAuth();
  const navigate = useNavigate();
  const isPanelOpen = showReportes || showChat || showNotificaciones;

  useEffect(() => {
    const fetchUsuarios = async () => {
      try {
        const { response, data } = await api.get('/administrador/usuarios');
        if (!response.ok) throw new Error('Error al cargar usuarios');
        if (data.success) {
          setUsuarios(data.usuarios);
        } else {
          setError(data.message || 'Error al cargar usuarios');
        }
      } catch (err) {
        setError(err.message || 'Error de conexión con el servidor');
      } finally {
        setLoading(false);
      }
    };

    fetchUsuarios();
  }, []);

  const handleLogout = async () => {
    try {
      const user = JSON.parse(localStorage.getItem('user'));
      if (!user || !user.ID_Usuario) {
        navigate('/');
        return;
      }

      const uuidRegex = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;
      if (!uuidRegex.test(user.ID_Usuario)) {
        throw new Error('ID de usuario no válido');
      }

      const { response } = await api.post('/usuario/logout', { ID_Usuario: user.ID_Usuario });

      if (!response.ok) {
        throw new Error('Error al cerrar sesión');
      }

      localStorage.removeItem('user');
      localStorage.removeItem('token');
      navigate('/');
    } catch (err) {
      console.error('Error al cerrar sesión:', err);
    }
  };

  if (loading) return <div className="loading">Cargando panel de administrador...</div>;
  if (error) return <div className="error">{error}</div>;

  return (
    <div className="dashboard-container">
      <header className="admin-header">
        <h1 className='titulo-header'>Panel de administración</h1>
        {currentUser && (
          <div className="profile-section">
            <button
              className="profile-button"
              onClick={() => setShowProfileMenu(!showProfileMenu)}
            >
              <span>{currentUser.usuario_asignado}</span>
              <span className="profile-arrow">▼</span>
            </button>
            {showProfileMenu && (
              <div className="profile-dropdown">
                <button
                  className="dropdown-item"
                  onClick={() => navigate(`/usuario/perfil?id=${currentUser.uuid}`)}
                >
                  Ver Perfil
                </button>
                <button
                  className="dropdown-item"
                  onClick={() => navigate(`/usuario/actualizar-perfil?id=${currentUser.uuid}`)}
                >
                  Editar Perfil
                </button>
                <button
                  className="dropdown-item logout"
                  onClick={handleLogout}
                >
                  Cerrar Sesión
                </button>
              </div>
            )}
          </div>
        )}
      </header>

      <div className="header-controls elevated-menu">
        <button onClick={() => navigate('/admin/gestion-usuarios')}>
          Gestión de Usuarios
        </button>

        <button
          onClick={() => {
            setShowReportes(!showReportes);
            setShowChat(false);
            setShowNotificaciones(false);
          }}
        >
          {showReportes ? 'Ocultar Reportes' : 'Mostrar Reportes'}
        </button>
        <button
          onClick={() => {
            setShowNotificaciones(!showNotificaciones);
            setShowReportes(false);
            setShowChat(false);
          }}
        >
          {showNotificaciones ? 'Ocultar Notificaciones' : 'Mostrar Notificaciones'}
        </button>
        <button
          onClick={() => {
            setShowChat(!showChat);
            setShowReportes(false);
            setShowNotificaciones(false);
          }}
        >
          {showChat ? 'Ocultar Chat' : 'Mostrar Chat'}
        </button>
      </div>
      <div className="admin-content">
        {!isPanelOpen && (
          <div className="admin-cards-container">
            <div className="admin-card" onClick={() => navigate('/admin/gestion-usuarios')}>
              <span className="emoji-icon">👥</span>
              <h3>Gestión de Usuarios</h3>
            </div>

            <div className="admin-card" onClick={() => {
              setShowReportes(true);
              setShowChat(false);
              setShowNotificaciones(false);
            }}>
              <span className="emoji-icon">📄</span>
              <h3>Gestionar Reportes</h3>
            </div>

            <div className="admin-card" onClick={() => {
              setShowNotificaciones(true);
              setShowReportes(false);
              setShowChat(false);
            }}>
              <span className="emoji-icon">🔔</span>
              <h3>Notificaciones</h3>
            </div>

            <div className="admin-card" onClick={() => {
              setShowChat(true);
              setShowReportes(false);
              setShowNotificaciones(false);
            }}>
              <span className="emoji-icon">💬</span>
              <h3>Chat de Usuarios</h3>
            </div>
          </div>
        )}
        {showNotificaciones && <NotificacionesPanel currentUser={currentUser} />}
        {showReportes && <GestionReportes currentUser={currentUser} />}
        {showChat && <ChatUsuarios currentUser={currentUser} asPanel={true} />}
      </div>
    </div>
  );
}