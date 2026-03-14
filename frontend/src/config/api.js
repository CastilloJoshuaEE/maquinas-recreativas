const API_BASE_URL =
  import.meta.env.VITE_API_URL ||
  (import.meta.env.PROD
    ? "https://recreasys.infinityfree.me/api/public"
    : "http://localhost:8000");
export const API_ENDPOINTS = {
  LOGIN: `/usuario/login`,
  REGISTER: `/usuario/register`,
  LOGOUT: `/usuario/logout`,
  USERS: `/administrador/usuarios`,
  USER_PROFILE: (id) => `/usuario/profile/${id}`,
  NOTIFICACIONES: (id) => `/notificaciones_maquina/${id}`,
  MAQUINAS_ENSAMBLADOR: (id) => `/maquina/ensamblador/${id}`,
  MAQUINAS_COMPROBADOR: (id) => `/maquina/comprobador/${id}`,
  MAQUINAS_MANTENIMIENTO: (id) => `/maquina/mantenimiento/${id}`,
  MAQUINAS_ESTADO: (estado) => `/maquina/estado/${estado}`,
  MAQUINAS_ETAPA: (etapa) => `/maquina/etapa/${etapa}`,
  COMERCIOS: `/comercio/all`,
  REPORTES: `/reportes`,
  COMPONENTES: `/componentes`,
};

export default API_BASE_URL;