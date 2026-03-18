import React, { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import HistorialMaquina from '../components/HistorialMaquina';
import { AdminHeader } from '../../modulo_usuario/AdminHeader';
import './HistorialGeneral.css';

const HistorialGeneral = () => {
    const { currentUser } = useAuth();
    const [mostrarHistorial, setMostrarHistorial] = useState(false);

    // Solo administradores y logística pueden ver esta página
    if (!['Administrador', 'Logistica'].includes(currentUser?.tipo)) {
        return (
            <div className="acceso-denegado">
                <h2>Acceso Denegado</h2>
                <p>No tienes permisos para ver esta página.</p>
            </div>
        );
    }

    return (
        <div className="historial-general-container">
            <AdminHeader />
            
            <div className="historial-general-content">
                <div className="historial-header-section">
                    <h1>📋 Historial General de Actividades</h1>
                    <button 
                        className="ver-historial-btn"
                        onClick={() => setMostrarHistorial(true)}
                    >
                        Ver Historial Completo
                    </button>
                </div>

                <div className="historial-info">
                    <p>
                        Desde aquí puedes ver todas las actividades realizadas en las máquinas recreativas,
                        incluyendo montajes, comprobaciones, mantenimientos y cambios de estado.
                    </p>
                </div>

                {mostrarHistorial && (
                    <HistorialMaquina 
                        onClose={() => setMostrarHistorial(false)}
                    />
                )}
            </div>
        </div>
    );
};

export default HistorialGeneral;