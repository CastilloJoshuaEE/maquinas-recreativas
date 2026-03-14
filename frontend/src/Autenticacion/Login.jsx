import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "../context/AuthContext";
import "../../css/modulo_usuario/main.css";
import Chatbot from "../components/Chatbot";
import api from "../utils/api";

class CredencialesIncorrectasError extends Error {
  constructor(message = "¡Credenciales incorrectas OwO!") {
    super(message);
    this.name = "CredencialesIncorrectasError";
  }
}

export default function Login() {
  const [windowWidth, setWindowWidth] = useState(window.innerWidth);
  const navigate = useNavigate();
  const [error, setError] = useState("");
  const [formData, setFormData] = useState({
    usuario_asignado: "",
    contrasena: "",
  });
  const [loading, setLoading] = useState(false);
  const { setCurrentUser } = useAuth();
  const [showChatbot, setShowChatbot] = useState(false);

  useEffect(() => {
    const handleResize = () => {
      setWindowWidth(window.innerWidth);
    };

    window.addEventListener("resize", handleResize);
    return () => window.removeEventListener("resize", handleResize);
  }, []);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError("");

    if (!formData.usuario_asignado || formData.usuario_asignado.length > 15) {
      setError("Usuario inválido (máximo 15 caracteres)");
      setLoading(false);
      return;
    }

    if (!formData.contrasena) {
      setError("La contraseña no puede estar vacía");
      setLoading(false);
      return;
    }

    try {
      const { response, data } = await api.post('/api/usuario/login', {
        usuario_asignado: formData.usuario_asignado.trim(),
        contrasena: formData.contrasena,
      });

      if (!response.ok) {
        if (data.message === "Usuario o contraseña incorrectos") {
          throw new CredencialesIncorrectasError();
        }
        throw new Error(data.message || "Error al iniciar sesión");
      }

      if (!data.success) {
        if (data.message === "Usuario o contraseña incorrectos") {
          throw new CredencialesIncorrectasError();
        }
        throw new Error(data.message || "Error desconocido");
      }

      const userData = {
        ...data.usuario,
        ID_Usuario: data.usuario.ID_Usuario,
        fecha_inicio: data.fecha_inicio,
      };

      localStorage.setItem("user", JSON.stringify(userData));
      setCurrentUser(userData);

      if (userData.estado === "Pendiente de asignacion") {
        await createInactiveUserReport(userData);
        navigate("/reportes", {
          state: {
            userData,
            message: "Su cuenta está pendiente de asignación. Por favor contacte al administrador.",
          },
        });
      } else if (userData.estado === "Inhabilitado") {
        navigate("/reportes/gestion", {
          state: {
            userData,
            isDisabledUser: true,
          },
        });
      } else {
        redirectUser(userData);
      }
    } catch (err) {
      if (err instanceof CredencialesIncorrectasError) {
        setError(err.message);
      } else {
        setError(err.message || "Error de conexión con el servidor");
      }
      console.error("Login error:", err);
    } finally {
      setLoading(false);
    }
  };

  const createInactiveUserReport = async (userData) => {
    try {
      if (userData.estado === "Pendiente de asignacion") {
        const { data } = await api.get("/usuarios/por-tipo?tipo=Administrador");

        if (data.success && data.usuarios.length > 0) {
          const admin = data.usuarios[0];
          await api.post("/reportes/crear", {
            ID_Usuario_Emisor: userData.ID_Usuario,
            ID_Usuario_Destinatario: admin.ID_Usuario,
            descripcion: `Usuario con estado ${userData.estado} intentó iniciar sesión. Por favor revisar.`,
          });
        }
      }
    } catch (error) {
      console.error("Error creating report:", error);
    }
  };

  const redirectUser = (userData) => {
    const userType = userData.tipo === "Técnico" ? "Tecnico" : userData.tipo;

    switch (userType) {
      case "Logistica":
        navigate("/dashboard/logistica", {
          state: { userId: userData.ID_Usuario },
        });
        break;
      case "Tecnico":
        if (userData.Especialidad) {
          const path = `/dashboard/${userData.Especialidad.toLowerCase()}`;
          navigate(path, { state: { userId: userData.ID_Usuario } });
        } else {
          navigate("/dashboard/tecnico", {
            state: { userId: userData.ID_Usuario },
          });
        }
        break;
      case "Contabilidad":
        navigate("/contabilidad", {
          state: { userId: userData.ID_Usuario },
        });
        break;
      case "Administrador":
        navigate("/dashboard/admin", {
          state: {
            userId: userData.ID_Usuario,
            userData: userData,
          },
        });
        break;
      default:
        navigate("/", { state: { userId: userData.ID_Usuario } });
    }
  };

  const handleWorkWithUs = () => {
    navigate("/register");
  };

  return (
    <div className="login-page">
      <header>
        <h1>Bienvenido a Recrea Sys</h1>
        <nav>
          <a href="/">Iniciar sesión</a>
          <button onClick={handleWorkWithUs}>
            ¿QUIERES TRABAJAR CON NOSOTROS?
          </button>
        </nav>
      </header>

      <main className="pantalla_completa">
        <div className="contenedor_todo">
          <button
            className="boton-info-sistema"
            onClick={() => navigate("/informacion")}
            title="Información del sistema"
          >
            <span className="icono-info">Información{<br />}del sistema</span>
          </button>

          <div className="contenedor_login_register">
            <form onSubmit={handleSubmit} className="formulario_login">
              <h2>Iniciar Sesión</h2>
              {error && <div className="error-message">{error}</div>}
              <input
                type="text"
                name="usuario_asignado"
                placeholder="Usuario asignado"
                value={formData.usuario_asignado}
                onChange={handleChange}
                required
              />
              <input
                type="password"
                name="contrasena"
                placeholder="Contraseña"
                value={formData.contrasena}
                onChange={handleChange}
                required
              />
              <div className="enlaces_recuperacion">
                <a href="/usuario/recuperar-contrasena">
                  ¿Olvidaste tu contraseña?
                </a>
                <a href="/usuario/recuperar-usuario">¿Olvidaste tu usuario?</a>
              </div>
              <button type="submit" disabled={loading}>
                {loading ? "Verificando..." : "Entrar"}
              </button>
            </form>
          </div>
          <button
            className="chatbot-toggle"
            onClick={() => setShowChatbot(!showChatbot)}
          >
            {showChatbot ? "Ocultar asistente" : "Necesito ayuda"}
          </button>
          {showChatbot && <Chatbot />}
        </div>
      </main>
    </div>
  );
}