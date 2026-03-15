import { useState, useEffect } from "react";
import api from "../../utils/api";

export default function MaquinaForm({ onClose, onSuccess }) {
  const [step, setStep] = useState(1);
  const [formData, setFormData] = useState({
    nombre: "",
    tipo: "",
    idComercio: "",
  });
  const [comercios, setComercios] = useState([]);
  const [ensambladores, setEnsambladores] = useState([]);
  const [comprobadores, setComprobadores] = useState([]);
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);
  const [componentesCreados, setComponentesCreados] = useState({
    placa: false,
    carcasa: false,
  });
  const [componentErrors, setComponentErrors] = useState({
    placa: "",
    carcasa: ""
  });
  const [selectedCarcasa, setSelectedCarcasa] = useState("");
  const [carcasasDisponibles, setCarcasasDisponibles] = useState([]);
  const [placaData, setPlacaData] = useState(null);
  const user = JSON.parse(localStorage.getItem("user"));

  useEffect(() => {
    const loadData = async () => {
      try {
        const { data: comerciosData } = await api.get("/comercio/all");
        if (comerciosData.success) setComercios(comerciosData.comercios);

        const { data: ensData } = await api.get("/usuario/tecnicos/Ensamblador");
        if (ensData.success) setEnsambladores(ensData.tecnicos);

        const { data: compData } = await api.get("/usuario/tecnicos/Comprobador");
        if (compData.success) setComprobadores(compData.tecnicos);

        const { data: carcasasData } = await api.get("/componentes?tipo=Logistico");
        if (carcasasData.success) {
          setCarcasasDisponibles(
            carcasasData.componentes.filter(c => c.nombre.includes('Carcasa'))
          );
        }
      } catch (err) {
        console.error("Error loading data:", err);
      }
    };

    loadData();
  }, []);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData({
      ...formData,
      [name]: value,
    });
  };

  const handleCancel = async () => {
    if (placaData?.id_componente && componentesCreados.carcasa) {
      try {
        await api.post('/componentes/liberar-cancelacion', {
          ID_Placa: placaData.id_componente,
          ID_Carcasa: componentesCreados.carcasa,
          ID_Usuario: user.ID_Usuario
        });
      } catch (err) {
        console.error('Error al liberar componentes:', err);
      }
    }
    onClose();
  };

  const crearComponenteLogistico = async (tipoComponente) => {
    try {
      setLoading(true);
      setError("");
      setComponentErrors(prev => ({...prev, [tipoComponente]: ""}));

      if (tipoComponente === "placa") {
        if (!user?.ID_Usuario) {
          throw new Error("No se pudo identificar al usuario actual");
        }
        
        const { data } = await api.post("/maquina/generar-placa", {
          ID_Usuario: user.ID_Usuario
        });
        
        setPlacaData(data);
        setComponentesCreados(prev => ({
          ...prev,
          placa: data.placa
        }));
        
      } else if (tipoComponente === "carcasa") {
        if (!selectedCarcasa) {
          throw new Error("Debe seleccionar una carcasa");
        }
        
        const { data } = await api.post('/componentes/asignar-carcasa', {
          ID_Componente: selectedCarcasa,
          ID_Usuario: user.ID_Usuario
        });

        if (!data.success) {
          throw new Error(data.message || "Error al asignar carcasa");
        }

        setComponentesCreados(prev => ({
          ...prev,
          carcasa: selectedCarcasa
        }));
      }
    } catch (err) {
      setComponentErrors(prev => ({
        ...prev,
        [tipoComponente]: err.message || `Error al crear ${tipoComponente}`
      }));
      console.error(`Error en crearComponenteLogistico (${tipoComponente}):`, err);
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (
      !formData.nombre ||
      !formData.tipo ||
      !formData.idComercio ||
      !placaData ||
      !componentesCreados.carcasa
    ) {
      setError("Complete todos los campos antes de registrar");
      return;
    }

    if (ensambladores.length === 0 || comprobadores.length === 0) {
      setError("No hay técnicos disponibles para asignar");
      return;
    }

    setLoading(true);
    setError("");

    try {
      const { data } = await api.post("/maquina/register", {
        nombre: formData.nombre,
        tipo: formData.tipo,
        idComercio: formData.idComercio,
        idUsuarioLogistica: user.ID_Usuario,
        idPlaca: placaData.id_componente,
        idCarcasa: componentesCreados.carcasa,
      });

      if (data.success) {
        onSuccess();
      } else {
        setError(data.message || "Error al registrar máquina");
      }
    } catch (err) {
      console.error("Error en handleSubmit:", err);
      setError(err.message || "Error de conexión con el servidor");
    } finally {
      setLoading(false);
    }
  };

  const handleNextStep = () => {
    if (!componentesCreados.placa || !componentesCreados.carcasa) {
      setError("Debe crear ambos componentes logísticos antes de continuar");
      return;
    }
    setError("");
    setStep(2);
  };

  return (
    <div className="modal-overlay">
      <div className="modal-content">
        <h2>Registrar Nueva Máquina Recreativa</h2>
        {error && <div className="error-message">{error}</div>}

        {step === 1 ? (
          <div className="componentes-logisticos">
            <h3>Paso 1: Crear componentes logísticos</h3>
            <p>
              Por favor, cree primero una placa y una carcasa antes de registrar
              la máquina.
            </p>

            <div className="componente-item">
              <h4>Placa base Logística</h4>
              <button
                onClick={() => crearComponenteLogistico("placa")}
                disabled={componentesCreados.placa || loading}
              >
                {componentesCreados.placa
                  ? `Placa creada: ${componentesCreados.placa}`
                  : "Crear Placa"}
              </button>
              {componentErrors.placa && (
                <div className="component-error">{componentErrors.placa}</div>
              )}
            </div>

            <div className="componente-item">
              <h4>Carcasa estándar</h4>
              <select 
                value={selectedCarcasa} 
                onChange={(e) => setSelectedCarcasa(e.target.value)}
                disabled={componentesCreados.carcasa}
              >
                <option value="">Seleccione una carcasa</option>
                {carcasasDisponibles.map(c => (
                  <option key={c.ID_Componente} value={c.ID_Componente}>
                    {c.nombre} (${c.precio})
                  </option>
                ))}
              </select>
              <button
                onClick={() => crearComponenteLogistico("carcasa")}
                disabled={componentesCreados.carcasa || !selectedCarcasa || loading}
              >
                {componentesCreados.carcasa
                  ? "Carcasa asignada"
                  : "Asignar Carcasa"}
              </button>
              {componentErrors.carcasa && (
                <div className="component-error">{componentErrors.carcasa}</div>
              )}
            </div>

            <div className="form-actions">
              <button
                type="button"
                onClick={handleNextStep}
                disabled={!(componentesCreados.placa && componentesCreados.carcasa)}
              >
                Siguiente
              </button>
              <button type="button" onClick={handleCancel}>
                Cancelar
              </button>
            </div>
          </div>
        ) : (
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
              <label>Tipo: </label>
              <input
                type="text"
                name="tipo"
                value={formData.tipo}
                onChange={handleChange}
                required
              />
            </div>

            <div className="form-group">
              <label>Comercio: </label>
              <select
                name="idComercio"
                value={formData.idComercio}
                onChange={handleChange}
                required
              >
                <option value="">Seleccione un comercio</option>
                {comercios.map((comercio) => (
                  <option
                    key={comercio.ID_Comercio}
                    value={comercio.ID_Comercio}
                  >
                    {comercio.Nombre} ({comercio.Tipo})
                  </option>
                ))}
              </select>
            </div>

            <div className="form-group">
              <label>Técnico Ensamblador: </label>
              <input
                type="text"
                value={
                  ensambladores[0]
                    ? `${ensambladores[0].nombre} ${ensambladores[0].apellido}`
                    : "No hay técnicos ensambladores disponibles"
                }
                disabled
              />
            </div>

            <div className="form-group">
              <label>Técnico Comprobador: </label>
              <input
                type="text"
                value={
                  comprobadores[0]
                    ? `${comprobadores[0].nombre} ${comprobadores[0].apellido}`
                    : "No hay técnicos comprobadores disponibles"
                }
                disabled
              />
            </div>

            <div className="form-actions">
              <button
                type="button"
                onClick={() => {
                  setStep(1);
                  setError("");
                }}
                disabled={loading}
              >
                Atrás
              </button>
              <button
                type="submit"
                disabled={
                  loading ||
                  !formData.nombre ||
                  !formData.tipo ||
                  !formData.idComercio ||
                  ensambladores.length === 0 ||
                  comprobadores.length === 0
                }
              >
                {loading ? "Registrando..." : "Registrar"}
              </button>
              <button type="button" onClick={handleCancel}>
                Cancelar
              </button>
            </div>
          </form>
        )}
      </div>
    </div>
  );
}