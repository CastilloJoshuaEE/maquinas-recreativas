// frontend/src/components/NotificacionesList.jsx
import { useState, useEffect } from "react";
import api from '../utils/api';

export default function NotificacionesList({
  notificaciones,
  mostrarNotificaciones,
  setMostrarNotificaciones,
  emptyMessage = "No hay notificaciones...",
  user,
}) {
  const [noLeidas, setNoLeidas] = useState(0);

  useEffect(() => {
     const fetchNoLeidas = async () => {
      if (!user || !user.ID_Usuario) return;

      try {
        const { data } = await api.get(`/notificaciones/no-leidas/${user.ID_Usuario}`);
        
        if (data.success) {
          setNoLeidas(parseInt(data.total, 10));
        }
      } catch (error) {
        console.error("Error fetching unread notifications:", error);
      }
    };

    fetchNoLeidas();
  }, [user?.ID_Usuario, notificaciones]);

  const handleMarcarLeida = async (id) => {
    try {
      await api.post("/notificaciones/marcar-leida", { idNotificacion: id });

      setNoLeidas((prev) => (prev > 0 ? prev - 1 : 0));
    } catch (error) {
      console.error("Error marking notification as read:", error);
    }
  };

  if (!user) return null;

  return (
    <section className="notifications-section">
      <h2 onClick={() => setMostrarNotificaciones(!mostrarNotificaciones)}>
        Notificaciones {noLeidas > 0 && `(${noLeidas})`}
        {mostrarNotificaciones ? "🔽" : "▶️"}
      </h2>
      {mostrarNotificaciones &&
        (notificaciones.length > 0 ? (
          <ul>
            {notificaciones.map((notif) => (
              <li
                key={notif.ID_Notificacion}
                className={`notificacion-item ${
                  notif.Estado === "No leido" ? "no-leida" : ""
                }`}
                onClick={() =>
                  notif.Estado === "No leido" &&
                  handleMarcarLeida(notif.ID_Notificacion)
                }
              >
                <p>
                  <strong>{notif.Tipo}</strong> -{" "}
                  {new Date(notif.Fecha).toLocaleString()}
                  {notif.Mensaje && (
                    <>
                      <br />
                      <span className="notificacion-content">
                        {notif.Mensaje}
                      </span>
                    </>
                  )}
                  <br />
                  <span className="notificacion-content">
                    <strong>Máquina recreativa: </strong> {notif.Nombre_Maquina}
                  </span>
                  <span className="notificacion-content">
                    <strong>Comercio: </strong> {notif.NombreComercio}
                  </span>
                  <span className="notificacion-content">
                    <strong>Dirección: </strong> {notif.DireccionComercio}
                  </span>
                </p>
              </li>
            ))}
          </ul>
        ) : (
          <p>{emptyMessage}</p>
        ))}
    </section>
  );
}