import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import '../css/modulo_administrador/mainAdministrador.css';
import { AdminHeader } from '../modulo_usuario/AdminHeader';
import api from '../src/utils/api';

export default function GestionUsuarios() {
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    const navigate = useNavigate();

    useEffect(() => {
        const registrarActividad = async () => {
            try {
                await api.post('/historial-actividades', {
                    descripcion: "El usuario estuvo en la gestión de usuarios"
                });
            } catch (err) {
                console.error('Error al registrar actividad:', err);
            }
        };

        registrarActividad();
    }, []);

    return (
        <div className="gestion-usuarios-container">
            <AdminHeader/>
            <div className="perfil-contenedor">
                <h2>Gestión de Usuarios</h2>
                
                <button onClick={() => navigate('/dashboard/admin')} className="btn-regresar">Regresar</button>
                <h2 className="gestion-title">Gestión de usuarios</h2>

                <div className="card-buttons-container">
                    <div className="card-button" onClick={() => navigate('/admin/gestion-usuarios/registrar-usuario')}>
                        <span className="icono-card" role="img" aria-label="Registrar">👤➕</span>
                        <h3>Registrar Usuario</h3>
                        <p>Crear un nuevo usuario en el sistema.</p>
                    </div>

                    <div className="card-button" onClick={() => navigate('/admin/gestion-usuarios/consultar-usuarios')}>
                        <span className="icono-card" role="img" aria-label="Consultar">🗂️</span>
                        <h3>Consultar Usuarios</h3>
                        <p>Consultar, actualizar y eliminar usuarios existentes.</p>
                    </div>
                </div>
            </div>
        </div>
    );
}