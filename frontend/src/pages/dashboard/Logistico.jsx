// frontend/src/pages/dashboard/Logistico.jsx
import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import ProfileSection from "../../components/ProfileSection";
import ComercioForm from "../forms/ComercioForm";
import MaquinaForm from "../forms/MaquinaForm";
import api from "../../utils/api";
import NotificacionesList from "../../components/NotificacionesList";
import MaquinaList from "../../components/MaquinaList";
import { AdminHeader } from "../../../modulo_usuario/AdminHeader";
import "../../../css/dashboards.css";
{
  /** Este es el componente principal que gestiona el estado de la página y las interacciones con los datos, como notificaciones, máquinas y formularios. 
  Además, se encarga de cargar los datos de las máquinas y las notificaciones desde la API y manejar la sesión del usuario. */
}
export default function Logistico() {
  const [user, setUser] = useState(null);
  const [notificaciones, setNotificaciones] = useState([]);
  const [mostrarNotificaciones, setMostrarNotificaciones] = useState(false);
  const [maquinasDistribucion, setMaquinasDistribucion] = useState([]);
  const [maquinasOperativas, setMaquinasOperativas] = useState([]);
  const [maquinasRetiradas, setMaquinasRetiradas] = useState([]);
  const [mostrarMaquinasRetiradas, setMostrarMaquinasRetiradas] =
    useState(false);
  const [selectedMaquina, setSelectedMaquina] = useState(null);
  const [mostrarComercioForm, setMostrarComercioForm] = useState(false);
  const [mostrarMaquinaForm, setMostrarMaquinaForm] = useState(false);
  const [mensajeMantenimiento, setMensajeMantenimiento] = useState("");
  const [errorMantenimiento, setErrorMantenimiento] = useState("");
  const [mostrarMensajeMantenimiento, setMostrarMensajeMantenimiento] =
    useState(false);
  const navigate = useNavigate();

  useEffect(() => {
    const storedUser = localStorage.getItem("user");
    if (!storedUser) {
      navigate("/");
      return;
    }

    const userData = JSON.parse(storedUser);
    if (userData.tipo !== "Logistica") {
      navigate("/");
      return;
    }

    setUser(userData);
    loadData();
  }, [navigate]);
  //Realiza solicitudes fetch a varias rutas de la API para cargar los datos de las notificaciones, máquinas en diferentes estados (Distribucion, Operativa, Retirada), y luego actualiza el estado correspondiente con los datos obtenidos.
  const loadData = async () => {
  try {
    const userId = JSON.parse(localStorage.getItem("user"))?.ID_Usuario;

    // Notificaciones
    const { data: notifData } = await api.get(`/notificaciones_maquina/${userId}`);
    if (notifData.success) setNotificaciones(notifData.notificaciones);

    // Distribución
    const { data: distData } = await api.get("/maquina/etapa/Distribucion");
    if (distData.success) setMaquinasDistribucion(distData.maquinas);

    // Operativas
    const { data: opData } = await api.get("/maquina/estado/Operativa");
    if (opData.success) setMaquinasOperativas(opData.maquinas);

    // Retiradas
    const { data: retData } = await api.get("/maquina/estado/Retirada");
    if (retData.success) setMaquinasRetiradas(retData.maquinas);
  } catch (err) {
    console.error("Error loading data:", err);
  }
};
  //Permite cambiar el estado de una máquina a "Operativa". Se envía una solicitud POST a la API con el ID_Maquina de la máquina seleccionada.
  const handlePonerOperativa = async () => {
  if (!selectedMaquina) return;

  try {
    const { data } = await api.post("/maquina/poner-operativa", { 
      idMaquina: selectedMaquina.ID_Maquina 
    });
    
    if (data.success) {
      loadData();
      setSelectedMaquina(null);
    }
  } catch (err) {
    console.error("Error al poner operativa:", err);
  }
};

  //Permite enviar un mensaje de mantenimiento para una máquina seleccionada
 const handleDarMantenimiento = async () => {
  if (!selectedMaquina || !mensajeMantenimiento) return;

  setErrorMantenimiento("");

  try {
    const { data } = await api.post("/maquina/dar-mantenimiento", {
      idMaquina: selectedMaquina.ID_Maquina,
      mensaje: mensajeMantenimiento,
      idLogistica: user.ID_Usuario,
    });

    if (data.success) {
      loadData();
      setSelectedMaquina(null);
      setMensajeMantenimiento("");
      setMostrarMensajeMantenimiento(false);
    } else {
      setErrorMantenimiento("No hay técnicos de mantenimiento");
    }
  } catch (err) {
    console.error("Error al dar mantenimiento:", err);
    setErrorMantenimiento("No hay técnicos de mantenimiento");
  }
};
  return (
    <div className="dashboard-container">
      <AdminHeader />

      <div className="dashboard-sections">
        <hr />

        <ProfileSection user={user} additionalFields={[]} />
        {/**Muestra una lista de notificaciones para el usuario, con la posibilidad de mostrar u ocultar la lista.
         */}
        <hr />

        <NotificacionesList
          notificaciones={notificaciones}
          mostrarNotificaciones={mostrarNotificaciones}
          setMostrarNotificaciones={setMostrarNotificaciones}
          user={user}
        />

        <hr />

        <section className="machines-section">
          <h2>Máquinas Recreativas</h2>
          {/**Muestra una lista de máquinas en diferentes estados (Distribución, Operativa, Retirada), con botones de acción como "Poner operativa" o "Llevar a mantenimiento". */}
          {/* En Distribución */}
          <MaquinaList
            title="En Distribución"
            maquinas={maquinasDistribucion}
            selectedMaquina={selectedMaquina}
            onSelectMaquina={setSelectedMaquina}
            initiallyExpanded={false}
            emptyMessage="No hay máquinas para distribuir..."
            actionButtons={[
              {
                label: "Poner operativa ✅",
                onClick: handlePonerOperativa,
                disabled:
                  !selectedMaquina ||
                  !maquinasDistribucion.some(
                    (m) => m.ID_Maquina === selectedMaquina?.ID_Maquina
                  ),
              },
            ]}
          />

          {/* En Recaudación */}
          <div>
            <h3>En Recaudación</h3>
            {/* Operativas */}
            <MaquinaList
              title="Operativas"
              maquinas={maquinasOperativas}
              selectedMaquina={selectedMaquina}
              onSelectMaquina={setSelectedMaquina}
              initiallyExpanded={false}
              emptyMessage="No hay máquinas operativas..."
              actionButtons={[
                {
                  label: "Llevar a mantenimiento ⚙️",
                  onClick: () => setMostrarMensajeMantenimiento(true),
                  disabled:
                    !selectedMaquina ||
                    !maquinasOperativas.some(
                      (m) => m.ID_Maquina === selectedMaquina?.ID_Maquina
                    ),
                },
              ]}
            />

            {/* Retiradas - Versión de solo lectura */}
            <div className="machine-list">
              <h3
                style={{ cursor: "pointer" }}
                onClick={() => setMostrarMaquinasRetiradas((prev) => !prev)}
              >
                Retiradas {mostrarMaquinasRetiradas ? "🔽" : "▶️"}
              </h3>
              {mostrarMaquinasRetiradas &&
                (maquinasRetiradas.length > 0 ? (
                  <ul>
                    {maquinasRetiradas.map((maquina) => (
                      <li
                        key={maquina.ID_Maquina}
                        style={{ textDecoration: "line-through" }}
                      >
                        <p>
                          <span className="li-maquina-content">
                            <strong>{maquina.Nombre_Maquina}</strong>
                            <br />
                            <span className="span-comercio-details">
                              <strong>Comercio:</strong>{" "}
                              {maquina.NombreComercio}
                            </span>
                            <span className="span-comercio-details">
                              <strong>Dirección:</strong>{" "}
                              {maquina.DireccionComercio}
                            </span>
                          </span>
                        </p>
                        <br />
                      </li>
                    ))}
                  </ul>
                ) : (
                  <p>No hay máquinas retiradas...</p>
                ))}
            </div>
          </div>
        </section>

        <hr />

        <section className="actions-section">
          <h2>Acciones</h2>
          <button onClick={() => setMostrarComercioForm(true)}>
            Nuevo comercio
          </button>
          <button onClick={() => setMostrarMaquinaForm(true)}>
            Nueva máquina
          </button>
          <button
            onClick={() =>
              navigate("/logistica/consultar-informe-distribucion")
            }
          >
            Consultar Informes de Distribución
          </button>
        </section>
      </div>

      {mostrarMensajeMantenimiento && (
        <div className="message-modal">
          <h3>Mensaje para el técnico de mantenimiento</h3>
          {errorMantenimiento && (
            <p style={{ color: "red", marginBottom: "5px" }}>
              {errorMantenimiento}
            </p>
          )}
          <textarea
            value={mensajeMantenimiento}
            onChange={(e) => setMensajeMantenimiento(e.target.value)}
            placeholder="Describe el problema..."
            required
          />
          <div className="modal-actions">
            <button
              onClick={() => {
                setMostrarMensajeMantenimiento(false);
                setMensajeMantenimiento("");
              }}
            >
              Cancelar
            </button>
            <button
              onClick={handleDarMantenimiento}
              disabled={!mensajeMantenimiento}
            >
              Enviar
            </button>
          </div>
        </div>
      )}

      {mostrarComercioForm && (
        <ComercioForm
          onClose={() => setMostrarComercioForm(false)}
          onSuccess={() => {
            setMostrarComercioForm(false);
            loadData();
          }}
        />
      )}

      {mostrarMaquinaForm && (
        <MaquinaForm
          onClose={() => setMostrarMaquinaForm(false)}
          onSuccess={() => {
            setMostrarMaquinaForm(false);
            loadData();
          }}
        />
      )}
    </div>
  );
}
