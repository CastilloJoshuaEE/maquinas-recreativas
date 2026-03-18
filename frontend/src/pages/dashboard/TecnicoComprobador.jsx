// frontend/src/pages/dashboard/TecnicoComprobador.jsx
import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import ProfileSection from "../../components/ProfileSection";
import NotificacionesList from "../../components/NotificacionesList";
import MaquinaList from "../../components/MaquinaList";
import { AdminHeader } from "../../../modulo_usuario/AdminHeader";
import "../../../css/dashboards.css";
import api from "../../utils/api";
import HistorialMaquina from "../../components/HistorialMaquina";
export default function TecnicoComprobador() {
  const [user, setUser] = useState(null);
  const [notificaciones, setNotificaciones] = useState([]);
  const [maquinasComprobando, setMaquinasComprobando] = useState([]);
  const [selectedMaquina, setSelectedMaquina] = useState(null);
  const [mostrarMensaje, setMostrarMensaje] = useState(false);
  const [mensaje, setMensaje] = useState("");
  const [accion, setAccion] = useState("");
  const [mostrarNotificaciones, setMostrarNotificaciones] = useState(false);
  const [checklist, setChecklist] = useState({
    placaFuncional: false,
    carcasaBuenEstado: false,
    experienciaJuegoAcorde: false,
  });
  const [mostrarHistorial, setMostrarHistorial] = useState(false);
  const [mostrarChecklist, setMostrarChecklist] = useState(false);
  const navigate = useNavigate();

  useEffect(() => {
    const storedUser = localStorage.getItem("user");
    if (!storedUser) {
      navigate("/");
      return;
    }

    const userData = JSON.parse(storedUser);
    if (!storedUser || JSON.parse(storedUser).Especialidad !== "Comprobador") {
      navigate("/");
      return;
    }

    setUser(userData);
    loadData();
  }, [navigate]);

 const loadData = async () => {
  try {
    const userId = JSON.parse(localStorage.getItem("user")).ID_Usuario;
    
    const { data: notifData } = await api.get(`/notificaciones_maquina/${userId}`);
    if (notifData.success) setNotificaciones(notifData.notificaciones);

    const { data: compData } = await api.get(`/maquina/comprobador/${userId}`);
    if (compData.success) setMaquinasComprobando(compData.maquinas);
  } catch (err) {
    console.error("Error loading data:", err);
  }
};
  const handleAccion = async () => {
    if (!selectedMaquina || !accion) return;

    try {
      let endpoint = "";
      if (accion === "distribucion") {
        endpoint = "/maquina/mandar-distribucion";
      } else {
        endpoint = "/maquina/mandar-reensamblar";
      }
 const { data } = await api.post(endpoint, {
      idMaquina: selectedMaquina.ID_Maquina,
      idRemitente: user.ID_Usuario,
      mensaje: mensaje,
    });

    if (data.success) {
      loadData();
      setSelectedMaquina(null);
      setMostrarMensaje(false);
      setMensaje("");
      setAccion("");
      setMostrarChecklist(false);
      setChecklist({
        placaFuncional: false,
        carcasaBuenEstado: false,
        experienciaJuegoAcorde: false,
      });
    }
  } catch (err) {
    console.error("Error al realizar accion:", err);
  }
};

  const handleSelectMaquina = (maquina) => {
    setSelectedMaquina(maquina);
    setMostrarChecklist(true);
    setChecklist({
      placaFuncional: false,
      carcasaBuenEstado: false,
      experienciaJuegoAcorde: false,
    });
  };

  const handleChecklistChange = (e) => {
    const { name, checked } = e.target;
    setChecklist((prev) => ({
      ...prev,
      [name]: checked,
    }));
  };

  const allChecksPassed = () => {
    return (
      checklist.placaFuncional &&
      checklist.carcasaBuenEstado &&
      checklist.experienciaJuegoAcorde
    );
  };

  return (
    <div className="dashboard-container">
      <AdminHeader />

      <div className="dashboard-sections">
        <hr />

        <ProfileSection
          user={user}
          additionalFields={[
            { label: "Especialidad", value: user?.Especialidad },
          ]}
        />
        <NotificacionesList
          notificaciones={notificaciones}
          mostrarNotificaciones={mostrarNotificaciones}
          setMostrarNotificaciones={setMostrarNotificaciones}
          user={user}
        />

        <hr />

        <section className="machines-section">
          <h2>Máquinas Recreativas</h2>

          {/* Comprobando */}
          <MaquinaList
            title="Comprobando"
            maquinas={maquinasComprobando}
            selectedMaquina={selectedMaquina}
            onSelectMaquina={handleSelectMaquina}
            initiallyExpanded={false}
            emptyMessage="No hay máquinas para comprobar..."
            actionButtons={[
              {
  label: "Ver historial 📋",
  onClick: () => setMostrarHistorial(true),
  disabled: false
}
            ]} // Los botones se manejarán después del checklist
          />

          {mostrarChecklist && selectedMaquina && (
            <div className="checklist-modal">
              <h3>Checklist de comprobación</h3>
              <div className="checklist-item">
                <input
                  type="checkbox"
                  id="placaFuncional"
                  name="placaFuncional"
                  checked={checklist.placaFuncional}
                  onChange={handleChecklistChange}
                />
                <label htmlFor="placaFuncional">¿Placa funcional?</label>
              </div>
              <div className="checklist-item">
                <input
                  type="checkbox"
                  id="carcasaBuenEstado"
                  name="carcasaBuenEstado"
                  checked={checklist.carcasaBuenEstado}
                  onChange={handleChecklistChange}
                />
                <label htmlFor="carcasaBuenEstado">
                  ¿Carcasa en buen estado?
                </label>
              </div>
              <div className="checklist-item">
                <input
                  type="checkbox"
                  id="experienciaJuegoAcorde"
                  name="experienciaJuegoAcorde"
                  checked={checklist.experienciaJuegoAcorde}
                  onChange={handleChecklistChange}
                />
                <label htmlFor="experienciaJuegoAcorde">
                  ¿Experiencia de juego acorde?
                </label>
              </div>

              <div className="action-buttons">
                <button
                  className="primary-btn"
                  onClick={() => {
                    setAccion("distribucion");
                    setMostrarMensaje(true);
                  }}
                  disabled={!allChecksPassed()}
                >
                  Mandar a distribución ✅
                </button>
                <button
                  className="danger-btn"
                  onClick={() => {
                    setAccion("reensamblar");
                    setMostrarMensaje(true);
                  }}
                >
                  Mandar a reensamblar ❌
                </button>
              </div>
            </div>
          )}

          {mostrarMensaje && (
            <div className="message-modal">
              <h3>
                Mensaje{" "}
                {accion === "distribucion"
                  ? "para logística"
                  : "para el tecnico ensamblador"}
              </h3>
              <textarea
                value={mensaje}
                onChange={(e) => setMensaje(e.target.value)}
                placeholder="Escribe un mensaje..."
              />
              <div className="modal-actions">
                <button
                  onClick={() => {
                    setMostrarMensaje(false);
                    setMensaje("");
                    setAccion("");
                  }}
                >
                  Cancelar
                </button>
                <button className="primary-btn" onClick={handleAccion}>
                  Enviar
                </button>
              </div>
            </div>
          )}
    {mostrarHistorial && (
  <HistorialMaquina
    idMaquina={selectedMaquina?.ID_Maquina || null}
    nombreMaquina={selectedMaquina?.Nombre_Maquina || "General"}
    onClose={() => setMostrarHistorial(false)}
  />
)}
        </section>
      </div>
    </div>
  );
}
