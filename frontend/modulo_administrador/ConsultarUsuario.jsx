import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import Modal from 'react-modal';
import '../css/modulo_administrador/consultar_usuarios.css';
import { AdminHeader } from '../modulo_usuario/AdminHeader';
import api from '../src/utils/api';

Modal.setAppElement('#root');

export default function ConsultarUsuarios() {
  const [usuarios, setUsuarios] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [filtros, setFiltros] = useState({
    ci: '',
    estado: '',
    tipo: '',
    rango_fecha: ''
  });
  const [modalIsOpen, setModalIsOpen] = useState(false);
  const [historialUsuario, setHistorialUsuario] = useState([]);
  const [usuarioSeleccionado, setUsuarioSeleccionado] = useState(null);
  const navigate = useNavigate();

  useEffect(() => {
    cargarUsuarios();
  }, []);

  const cargarUsuarios = async () => {
    setLoading(true);
    setError('');
  
    try {
      const params = new URLSearchParams();
      if (filtros.ci) params.append('ci', filtros.ci);
      if (filtros.estado) params.append('estado', filtros.estado);
      if (filtros.tipo) params.append('tipo', filtros.tipo);
      if (filtros.rango_fecha) params.append('rango', filtros.rango_fecha);
  
      const token = localStorage.getItem('token');
      const { response, data } = await api.get(`/administrador/usuarios?${params.toString()}`, {
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        }
      });
  
      if (!response.ok || !data.success) {
        throw new Error(data.message || `Error ${response.status}`);
      }
  
      setUsuarios(data.usuarios);
    } catch (err) {
      console.error(err);
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };
  
  const cargarHistorialUsuario = async (idUsuario) => {
    setError('');
    try {
      const token = localStorage.getItem('token');
      const { response, data } = await api.get(`/historial-actividades?usuarioId=${idUsuario}`, {
        headers: { 
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}` 
        }
      });
  
      if (!response.ok || !data.success) {
        throw new Error(data.message || `Error ${response.status}`);
      }
      setHistorialUsuario(data.historial);
    } catch (err) {
      console.error('Error al cargar el historial de actividades:', err);
      setError(err.message);
    }
  };
  
  const abrirModalHistorial = (usuario) => {
    setUsuarioSeleccionado(usuario);
    cargarHistorialUsuario(usuario.ID_Usuario);
    setModalIsOpen(true);
  };

  const cerrarModal = () => {
    setModalIsOpen(false);
    setHistorialUsuario([]);
    setUsuarioSeleccionado(null);
  };

  const handleFiltroChange = (e) => {
    const { name, value } = e.target;
    setFiltros(prev => ({ ...prev, [name]: value }));
  };

  const handleActualizarUsuario = (uuid) => navigate(`/admin/gestion-usuarios/editar-usuario/${uuid}`);
  
  const handleEditarEstadoUsuario = (uuid) => navigate(`/admin/gestion-usuarios/editar-estado-usuario/${uuid}`);

  const handleEliminarUsuario = async (uuid) => {
    if (!uuid) {
      console.error('UUID no proporcionado');
      return;
    }
    
    if (!window.confirm('¿Está seguro de eliminar este usuario?')) return;
    
    try {
      const token = localStorage.getItem('token');
      const { response, data } = await api.delete(`/administrador/usuarios/${uuid}`, {
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        }
      });

      if (!response.ok) {
        throw new Error(data.message || `Error ${response.status}`);
      }
    
      if (data.success) {
        setUsuarios(us => us.filter(u => u.ID_Usuario !== uuid));
        alert('Usuario eliminado correctamente');
      }
    } catch (err) {
      console.error('Error al eliminar usuario:', err);
      alert(err.message);
    }
  };

  if (loading) return <div className="loading">Cargando usuarios...</div>;
  if (error) return <div className="error">{error}</div>;

  return (
    <div className="consultar-usuarios-container">
      <AdminHeader />
      <h2>Consultar usuarios</h2>
      <button onClick={() => navigate('/admin/gestion-usuarios')}>Regresar</button>

      <div className="contenedor-filtros">
        <input type="text" name="ci" placeholder="Cédula" value={filtros.ci} onChange={handleFiltroChange} />
        <select name="estado" value={filtros.estado} onChange={handleFiltroChange}>
          <option value="">-- Estado --</option>
          <option value="Activo">Activo</option>
          <option value="Inhabilitado">Inhabilitado</option>
          <option value="Pendiente de asignacion">Pendiente de asignación</option>
        </select>
        <select name="tipo" value={filtros.tipo} onChange={handleFiltroChange}>
          <option value="">-- Tipo de Usuario --</option>
          <option value="Contabilidad">Área de Contabilidad</option>
          <option value="Logistica">Área de Logística</option>
          <option value="Tecnico">Técnico</option>
        </select>
        
        <select name="rango_fecha" value={filtros.rango_fecha} onChange={handleFiltroChange}>
          <option value="">-- Rango Fecha Última Sesión --</option>
          <option value="hoy">Hoy</option>
          <option value="ayer">Ayer</option>
          <option value="15dias">Últimos 15 días</option>
          <option value="30dias">Últimos 30 días</option>
        </select>
        
        <button onClick={cargarUsuarios}>Buscar</button>
      </div>

      <div className="contenedor-tabla">
        <table>
          <thead>
            <tr>
              <th>ID</th><th>Cédula</th><th>Nombre</th><th>Apellido</th>
              <th>Email</th><th>Usuario Asignado</th><th>Estado</th><th>Tipo</th>
              <th>Historial</th><th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            {usuarios.length > 0 ? usuarios.map(u => (
              <tr key={u.ID_Usuario}>
                <td>{u.ID_Usuario}</td>
                <td>{u.ci}</td>
                <td>{u.nombre}</td>
                <td>{u.apellido}</td>
                <td>{u.email}</td>
                <td>{u.usuario_asignado}</td>
                <td>{u.estado}</td>
                <td>{u.tipo}</td>
                <td><button onClick={() => abrirModalHistorial(u)}>Ver Historial</button></td>
                <td>
                  <button onClick={() => handleActualizarUsuario(u.ID_Usuario)}>Actualizar</button>
                  <button onClick={() => handleEditarEstadoUsuario(u.ID_Usuario)}>Editar Estado</button>
                  <button onClick={() => handleEliminarUsuario(u.ID_Usuario)}>Eliminar</button>
                </td>
              </tr>
            )) : (
              <tr><td colSpan="11">No se encontraron usuarios</td></tr>
            )}
          </tbody>
        </table>
      </div>

      <Modal
        isOpen={modalIsOpen}
        onRequestClose={cerrarModal}
        contentLabel="Historial de Actividades"
        className="modal-historial"
        overlayClassName="modal-overlay"
      >
        <h2>Historial de Actividades</h2>
        <h3>{usuarioSeleccionado?.nombre} {usuarioSeleccionado?.apellido}</h3>
        <div className="historial-container">
          <table className="tabla-historial">
            <thead>
              <tr><th>Fecha</th><th>Actividad</th></tr>
            </thead>
            <tbody>
              {historialUsuario.length > 0 ?
                historialUsuario.map((actividad, i) => (
                  <tr key={i}>
                    <td>{new Date(actividad.fecha_registro).toLocaleString()}</td>
                    <td>{actividad.descripcion}</td>
                  </tr>
                )) : (
                  <tr><td colSpan="2">No hay actividades registradas</td></tr>
                )
              }
            </tbody>
          </table>
        </div>
        <button onClick={cerrarModal}>Cerrar</button>
      </Modal>
    </div>
  );
}