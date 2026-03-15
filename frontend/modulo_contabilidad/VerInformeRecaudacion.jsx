// frontend/modulo_contabilidad/VerInformeRecaudacion.jsx
import { useState, useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import "../css/modulo_contabilidad/recaudacion.css";
import { AdminHeader } from "../modulo_usuario/AdminHeader";
import api from "../src/utils/api";

export default function VerInformeRecaudacion() {
  const { idRecaudacion } = useParams();
  const [informe, setInforme] = useState(null);
  const [recaudacion, setRecaudacion] = useState(null);
  const [componentes, setComponentes] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const navigate = useNavigate();

  useEffect(() => {
    fetchInformeData();
  }, [idRecaudacion]);

  const fetchInformeData = async () => {
    try {
      setLoading(true);
      setError(null);

      // Obtener el informe por recaudación
      const { data: informeData } = await api.get(`/contabilidad/informe/${idRecaudacion}`);

      if (!informeData.success || !informeData.informe) {
        throw new Error("Informe no encontrado");
      }

      setInforme(informeData.informe);
      setComponentes(informeData.componentes || []);

      // Obtener la recaudación asociada
      const { data: recData } = await api.get(`/contabilidad/recaudaciones?ID_Recaudacion=${idRecaudacion}`);
      if (recData.success && recData.recaudaciones.length > 0) {
        setRecaudacion(recData.recaudaciones[0]);
      }

    } catch (error) {
      console.error("Error fetching informe:", error);
      setError(error.message);
    } finally {
      setLoading(false);
    }
  };

  const handlePrintInforme = () => {
    setTimeout(() => window.print(), 300);
  };

  const totalComponentes = componentes.reduce(
    (sum, comp) => sum + parseFloat(comp.precio || 0),
    0
  );

  if (loading) {
    return (
      <div className="recaudacion-container">
        <AdminHeader />
        <h2>Ver Informe de Recaudación</h2>
        <p>Cargando datos...</p>
      </div>
    );
  }

  if (error || !informe) {
    return (
      <div className="recaudacion-container">
        <AdminHeader />
        <h2>Ver Informe de Recaudación</h2>
        <div className="alert alert-danger">{error || "Informe no encontrado"}</div>
        <button onClick={() => navigate(-1)}>Volver</button>
      </div>
    );
  }

  return (
    <div className="recaudacion-container">
      <AdminHeader />
      <h2>Informe de Recaudación</h2>

      <div className="informe-content">
        <div className="informe-section">
          <h3>Datos del Comercio</h3>
          <p><strong>Nombre:</strong> {informe.Nombre_Comercio}</p>
          <p><strong>Dirección:</strong> {informe.Direccion_Comercio}</p>
          <p><strong>Teléfono:</strong> {informe.Telefono_Comercio}</p>
        </div>

        <div className="informe-section">
          <h3>Datos de la Recaudación</h3>
          <p><strong>Máquina:</strong> {informe.Nombre_Maquina}</p>
          <p><strong>Monto Total:</strong> ${recaudacion?.Monto_Total || informe.Monto_Total}</p>
          <p><strong>Monto Empresa:</strong> ${recaudacion?.Monto_Empresa || "N/A"}</p>
          {recaudacion?.Tipo_Comercio === "Mayorista" && (
            <p><strong>Monto Comercio:</strong> ${recaudacion?.Monto_Comercio} ({recaudacion?.Porcentaje_Comercio}%)</p>
          )}
          <p><strong>Fecha:</strong> {recaudacion?.fecha ? new Date(recaudacion.fecha).toLocaleString() : "N/A"}</p>
          <p><strong>Detalles:</strong> {recaudacion?.detalle || "Ninguno"}</p>
        </div>

        <div className="informe-section">
          <h3>Técnicos Involucrados</h3>
          <div className="tecnico-info">
            <h4>Ensamblador</h4>
            <p><strong>Pago:</strong> ${informe.Pago_Ensamblador}</p>
          </div>

          <div className="tecnico-info">
            <h4>Comprobador</h4>
            <p><strong>Pago:</strong> ${informe.Pago_Comprobador}</p>
          </div>

          {informe.Pago_Mantenimiento > 0 && (
            <div className="tecnico-info">
              <h4>Técnico de Mantenimiento</h4>
              <p><strong>Pago:</strong> ${informe.Pago_Mantenimiento}</p>
            </div>
          )}
        </div>

        <div className="informe-section">
          <h3>Componentes Utilizados</h3>
          {componentes.length > 0 ? (
            <table>
              <thead>
                <tr>
                  <th>Componente</th>
                  <th>Tipo</th>
                  <th>Precio</th>
                </tr>
              </thead>
              <tbody>
                {componentes.map((comp, index) => (
                  <tr key={index}>
                    <td>{comp.nombre}</td>
                    <td>{comp.tipo}</td>
                    <td>${parseFloat(comp.precio).toFixed(2)}</td>
                  </tr>
                ))}
              </tbody>
              <tfoot>
                <tr>
                  <td colSpan="2"><strong>Total componentes:</strong></td>
                  <td><strong>${totalComponentes.toFixed(2)}</strong></td>
                </tr>
              </tfoot>
            </table>
          ) : (
            <p>No se utilizaron componentes en esta máquina</p>
          )}
        </div>

        <div className="informe-footer">
          <p><strong>Empresa:</strong> {informe.empresa_nombre || "recrea Sys S.A."}</p>
          <p><strong>Descripción:</strong> {informe.empresa_descripcion || "Una empresa encargada en el ciclo de vida de las maquinas recreativas"}</p>
          <p><strong>Fecha de emisión:</strong> {new Date().toLocaleString()}</p>
        </div>

        <div className="informe-actions">
          <button onClick={handlePrintInforme} className="btn btn-primary">Imprimir Informe</button>
          <button onClick={() => navigate(-1)} className="btn btn-secondary">Volver</button>
        </div>
      </div>
    </div>
  );
}