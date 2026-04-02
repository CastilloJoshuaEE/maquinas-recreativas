// frontend/src/Autenticacion/Registrar.jsx
import React, { useState } from 'react';
import api from '../utils/api';
import { useNavigate, Link } from 'react-router-dom';
//Este componente permite registrar un nuevo usuario a través de un formulario. 
// Recoge los datos del formulario, los valida y los envía al servidor para crear un nuevo registro.
export default function RegistrarUsuario() {
    const [formData, setFormData] = useState({
        nombre: '',
        apellido: '',
        ci: '',
        email: '',
        usuario_asignado: '',
        contrasena: '',
        tipo: 'Logistica',
        especialidad: ''
    });
    const [error, setError] = useState('');
    const [success, setSuccess] = useState(false);
    const [cvFile, setCvFile] = useState(null);

    // Permite redirigir al usuario al inicio tras un registro exitoso.
    const navigate = useNavigate();
// Actualiza los valores del formulario a medida que el usuario escribe.
    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));
    };
    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!window.confirm('Seguro desea registrarse?')) {
            return;
        }
        try {
            const payload = {
                nombre: formData.nombre,
                apellido: formData.apellido,
                ci: formData.ci,
                email: formData.email,
                usuario_asignado: formData.usuario_asignado,
                contrasena: formData.contrasena,
                tipo: formData.tipo
            };

            if (formData.tipo === 'Tecnico') {
                payload.especialidad = formData.especialidad;
            }

            const { response, data } = await api.post('/usuario/register', payload);

            if (!response.ok) {
                throw new Error(data.message || 'Error en la respuesta del servidor');
            }

            if (data.success) {
                setSuccess(true);
                alert("No olvide su usuario y contraseña que acaba de registrar. Pronto nos pondremos en contacto con usted para informarle sobre su trabajo. Se le agradece su interés en querer pertenecer a esta empresa de máquinas recreativas.");
                setTimeout(() => navigate('/'), 2000);
            } else {
                setError(data.message || 'Error al registrar usuario');
            }
        } catch (err) {
            setError(err.message || 'Error de conexión con el servidor');
        }
    };
    const handleFileChange = (e) => {
        const file = e.target.files[0];
        if (file && file.type === "application/pdf") {
            setCvFile(file);
            alert(`Currículum "${file.name}" seleccionado correctamente.`);
        } else {
            alert("Por favor, seleccione un archivo PDF válido.");
            e.target.value = null;
            setCvFile(null);
        }
    };

    return (
        
        <div className="register-container">
            <h2>Registro de Usuario</h2>
            
            {error && <div className="error-message">{error}</div>}
            {success && (
                <div className="success-message">
                    <p style={{ color: '#56e335', marginBottom: '5px' }}>
                        ¡Registro exitoso! Redirigiendo...
                    </p>
                </div>
            )}

            <form onSubmit={handleSubmit}>
                <div className="form-group">
                    <label>Nombre: </label>
                    <input
                        type="text"
                        name="nombre"
                        value={formData.nombre}
                        onChange={handleChange}
                        required
                    />
                </div>

                <div className="form-group">
                    <label>Apellido: </label>
                    <input
                        type="text"
                        name="apellido"
                        value={formData.apellido}
                        onChange={handleChange}
                        required
                    />
                </div>

                <div className="form-group">
                    <label>Cédula: </label>
                    <input
                        type="text"
                        name="ci"
                        value={formData.ci}
                        onChange={handleChange}
                        required
                        maxLength="10"
                    />
                </div>

                <div className="form-group">
                    <label>Email: </label>
                    <input
                        type="email"
                        name="email"
                        value={formData.email}
                        onChange={handleChange}
                        required
                    />
                </div>

            
                <div className="form-group">
                    <label>Contraseña: </label>
                    <input
                        type="password"
                        name="contrasena"
                        value={formData.contrasena}
                        onChange={handleChange}
                        required
                    />
                </div>

                <div className="form-group">
                <label>Areas vacantes: </label>
                <select
                    name="tipo"
                    value={formData.tipo}
                    onChange={handleChange}
                    required
                >
                    <option value="Logistica">Logística</option>
                    <option value="Tecnico">Técnico</option>
                    <option value="Contabilidad">Área de Contabilidad</option>
                    <option value="Administrador">Administrador del sistema</option>
                </select>
                </div>

                {formData.tipo === 'Tecnico' && (
                    <div className="form-group">
                        <label>Especialidad: </label>
                        <select
                            name="especialidad"
                            value={formData.especialidad}
                            onChange={handleChange}
                            required
                        >
                            <option value="" disabled>Seleccione...</option>
                            <option value="Ensamblador">Ensamblador</option>
                            <option value="Comprobador">Comprobador</option>
                            <option value="Mantenimiento">Mantenimiento</option>
                        </select>
                    </div>
                )}
                <div className="form-group">
                    <label>Subir Currículum (PDF): </label>
                    <input
                        type="file"
                        accept=".pdf"
                        onChange={handleFileChange}
                    />
                </div>
                <button type="submit" className="submit-btn">
                    Registrar
                </button> 
                <div className="auth-links">
                    <Link to="/">Ir a inicio de sesión</Link>
                </div>
            </form>
            <br />
           <section className="requisitos-postulante">
            <h2>¿Qué buscamos en nuestros postulantes?</h2>
            <div className="requisitos-tipos">
                <div className="requisito-card">
                <h3>🛠 Técnico</h3>
                <ul>
                    <li>Conocimiento en mantenimiento de hardware o electrónica básica.</li>
                    <li>Capacidad para diagnosticar fallas en máquinas recreativas.</li>
                    <li>Actitud proactiva y resolutiva ante fallos técnicos.</li>
                </ul>
                </div>
                <div className="requisito-card">
                <h3> Logística</h3>
                <ul>
                    <li>Buena comunicación con técnicos y contabilidad.</li>
                    <li>Capacidad para trabajar bajo presión y tiempos ajustados.</li>
                </ul>
                </div>
                <div className="requisito-card">
                <h3> Contabilidad</h3>
                <ul>
                    <li>Conocimiento en manejo de recaudaciones por máquina.</li>
                    <li>Capacidad para elaborar reportes claros y transparentes.</li>
                    <li>Atención al detalle y confidencialidad.</li>
                </ul>
                </div>
                <div className="requisito-card">
                <h3>🧠 Administrador del sistema</h3>
                <ul>
                    <li>Conocimientos técnicos y administrativos del sistema web.</li>
                    <li>Capacidad de supervisión de usuarios y asignación de funciones.</li>
                    <li>Gestión de reportes y resolución de incidencias internas.</li>
                    <li>Comunicación efectiva con todos los perfiles.</li>
                </ul>
                </div>
            </div>
            </section>

        </div>
    );
}