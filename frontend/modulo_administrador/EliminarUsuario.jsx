import { useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import api from '../src/utils/api';
export default function EliminarUsuario() {
  const { uuid } = useParams();
  const navigate = useNavigate();

  useEffect(() => {
    const eliminarUsuario = async () => {
      try {
        const token = localStorage.getItem('token');
        const { response, data } = await api.delete(`/administrador/usuarios/${uuid}`, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });

        if (!response.ok) {
          throw new Error(data.message || 'Error al eliminar usuario');
        }

        if (data.success) {
          await api.post('/historial-actividades', {
            descripcion: `El usuario eliminó los datos de un usuario`
          }, {
            headers: { 'Authorization': `Bearer ${token}` }
          });

          alert('Usuario eliminado correctamente');
          navigate('/admin/gestion-usuarios/consultar-usuarios');
        }
      } catch (err) {
        console.error('Error al eliminar usuario:', err);
        alert(err.message);
        navigate('/admin/gestion-usuarios/consultar-usuarios');
      }
    };

    if (window.confirm('¿Está seguro de eliminar este usuario?')) {
      eliminarUsuario();
    } else {
      navigate('/admin/gestion-usuarios/consultar-usuarios');
    }
  }, [uuid, navigate]);

  return null;
}