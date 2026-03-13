// frontend/src/pages/form/ComercioForm.jsx
import { useState, useEffect } from "react";
import api from "../../utils/api";
export default function ComercioForm({ onClose, onSuccess }) {
  const [formData, setFormData] = useState({
    nombre: "",
    tipo: "Minorista",
    direccion: "",
    telefono: "",
  });
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);
  const [isFormValid, setIsFormValid] = useState(false);

  useEffect(() => {
    const { nombre, direccion, telefono } = formData;
    const isValid =
      nombre.trim() !== "" && 
      direccion.trim() !== "" && 
      telefono.trim() !== "" &&
      /^\d{10}$/.test(telefono); // Validate phone number format
    setIsFormValid(isValid);
  }, [formData]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData({
      ...formData,
      [name]: value,
    });
  };
 const handleSubmit = async (e) => {
    e.preventDefault();
    if (!isFormValid) return;

    setLoading(true);
    setError("");

    try {
      const { data } = await api.post("/comercio/register", formData);

      if (data.success) {
        onSuccess();
      } else {
        if (data.message && data.message.includes("Duplicate entry")) {
          setError(`El comercio "${formData.nombre}" ya está registrado`);
        } else {
          setError(data.message || "Error al registrar comercio");
        }
      }
    } catch (err) {
      setError("Error de conexión con el servidor");
    } finally {
      setLoading(false);
    }
  };
  return (
    <div className="modal-overlay">
      <div className="modal-content">
        <h2>Registrar Nuevo Comercio</h2>
        {error && <div className="error-message">{error}</div>}

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
            <select
              name="tipo"
              value={formData.tipo}
              onChange={handleChange}
              required
            >
              <option value="Minorista">Minorista</option>
              <option value="Mayorista">Mayorista</option>
            </select>
          </div>

          <div className="form-group">
            <label>Dirección: </label>
            <input
              type="text"
              name="direccion"
              value={formData.direccion}
              onChange={handleChange}
              required
            />
          </div>

          <div className="form-group">
            <label>Teléfono: </label>
            <input
              type="tel"
              name="telefono"
              placeholder="Formato: 0987654321"
              pattern="[0-9]{10}"
              value={formData.telefono}
              onChange={handleChange}
              required
            />
          </div>

          <div className="form-actions">
            <button type="submit" disabled={!isFormValid || loading}>
              {loading ? "Registrando..." : "Registrar"}
            </button>
            <button type="button" onClick={onClose}>
              Cancelar
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}