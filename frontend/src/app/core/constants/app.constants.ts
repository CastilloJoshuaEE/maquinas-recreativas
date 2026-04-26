/**
 * @fileoverview Constantes globales de la aplicación
 * @description Define constantes reutilizables como roles, estados, mensajes, etc.
 * @module constants
 */

/**
 * Roles de usuario disponibles en el sistema
 */
export const USER_ROLES = {
  ADMIN: 'Administrador',
  CONTABILIDAD: 'Contabilidad',
  LOGISTICA: 'Logistica',
  TECNICO: 'Tecnico',
  USUARIO: 'Usuario'
} as const;

export type UserRole = typeof USER_ROLES[keyof typeof USER_ROLES];

/**
 * Especialidades para técnicos
 */
export const TECNICO_ESPECIALIDADES = {
  ENSAMBLADOR: 'Ensamblador',
  COMPROBADOR: 'Comprobador',
  MANTENIMIENTO: 'Mantenimiento'
} as const;

export type TecnicoEspecialidad = typeof TECNICO_ESPECIALIDADES[keyof typeof TECNICO_ESPECIALIDADES];

/**
 * Estados de usuario
 */
export const USER_STATES = {
  ACTIVO: 'Activo',
  INHABILITADO: 'Inhabilitado',
  PENDIENTE_ASIGNACION: 'Pendiente de asignacion'
} as const;

export type UserState = typeof USER_STATES[keyof typeof USER_STATES];

/**
 * Estados de máquina
 */
export const MAQUINA_ESTADOS = {
  OPERATIVA: 'Operativa',
  RETIRADA: 'Retirada',
  NO_OPERATIVA: 'No operativa',
  ENSAMBLANDOSE: 'Ensamblandose',
  REENSAMBLANDOSE: 'Reensamblandose',
  COMPROBANDO: 'Comprobando',
  DISTRIBUCION: 'Distribucion',
  MANTENIMIENTO: 'Mantenimiento'
} as const;

export type MaquinaEstado = typeof MAQUINA_ESTADOS[keyof typeof MAQUINA_ESTADOS];

/**
 * Etapas de máquina
 */
export const MAQUINA_ETAPAS = {
  ENSAMBLAJE: 'Ensamblaje',
  COMPROBACION: 'Comprobacion',
  DISTRIBUCION: 'Distribucion',
  RECAUDACION: 'Recaudacion',
  MANTENIMIENTO: 'Mantenimiento'
} as const;

export type MaquinaEtapa = typeof MAQUINA_ETAPAS[keyof typeof MAQUINA_ETAPAS];

/**
 * Tipos de comercio
 */
export const COMERCIO_TIPOS = {
  MINORISTA: 'Minorista',
  MAYORISTA: 'Mayorista'
} as const;

export type ComercioTipo = typeof COMERCIO_TIPOS[keyof typeof COMERCIO_TIPOS];

/**
 * Estados de reporte
 */
export const REPORTE_ESTADOS = {
  PENDIENTE: 'Pendiente',
  EN_PROCESO: 'En proceso',
  RESUELTO: 'Resuelto'
} as const;

export type ReporteEstado = typeof REPORTE_ESTADOS[keyof typeof REPORTE_ESTADOS];

/**
 * Mensajes del sistema
 */
export const SYSTEM_MESSAGES = {
  // Mensajes de éxito
  SUCCESS_LOGIN: 'Inicio de sesión exitoso',
  SUCCESS_LOGOUT: 'Sesión cerrada correctamente',
  SUCCESS_REGISTER: 'Registro exitoso',
  SUCCESS_UPDATE: 'Actualización exitosa',
  SUCCESS_DELETE: 'Eliminación exitosa',
  SUCCESS_CREATE: 'Creación exitosa',
  
  // Mensajes de error
  ERROR_CONNECTION: 'Error de conexión con el servidor',
  ERROR_UNAUTHORIZED: 'No autorizado. Por favor inicie sesión nuevamente',
  ERROR_FORBIDDEN: 'No tiene permisos para realizar esta acción',
  ERROR_NOT_FOUND: 'Recurso no encontrado',
  ERROR_VALIDATION: 'Error de validación',
  ERROR_DUPLICATE: 'Registro duplicado',
  
  // Mensajes de confirmación
  CONFIRM_DELETE: '¿Está seguro de eliminar este registro?',
  CONFIRM_UPDATE: '¿Está seguro de guardar los cambios?',
  CONFIRM_LOGOUT: '¿Está seguro de cerrar sesión?'
} as const;

/**
 * Configuración de paginación por defecto
 */
export const DEFAULT_PAGINATION = {
  PAGE_SIZE: 10,
  PAGE_SIZE_OPTIONS: [5, 10, 20, 50, 100],
  PAGE: 1
};

/**
 * URLs de API
 */
export const API_ENDPOINTS = {
  // Autenticación
  LOGIN: '/usuario/login',
  LOGOUT: '/usuario/logout',
  REGISTER: '/usuario/register',
  
  // Usuario
  USER_PROFILE: '/usuario/perfil',
  UPDATE_PROFILE: '/usuario/actualizar-perfil',
  RECOVER_PASSWORD: '/usuario/recuperar-contrasena',
  RECOVER_USERNAME: '/usuario/recuperar-usuario',
  SEARCH_BY_EMAIL: '/usuario/buscar-email',
  
  // Administrador
  ADMIN_USERS: '/administrador/usuarios',
  ADMIN_USER_BY_ID: (uuid: string) => `/administrador/usuarios/${uuid}`,
  
  // Comercio
  COMERCIOS: '/comercio/all',
  COMERCIO_REGISTER: '/comercio/register',

  COMERCIO_UPDATE: (id: string) => `/comercio/actualizar/${id}`,
  COMERCIO_DELETE: (id: string) => `/comercio/eliminar/${id}`,
  // Máquina
  MAQUINA_REGISTER: '/maquina/register',
  MAQUINA_ALL: '/maquina/all',
  MAQUINA_GENERAR_PLACA: '/maquina/generar-placa',
  MAQUINA_MONTAR: '/maquina/registrar-montaje',
  MAQUINA_COMPROBACION: '/maquina/mandar-comprobacion',
  MAQUINA_REENSAMBLAR: '/maquina/mandar-reensamblar',
  MAQUINA_DISTRIBUCION: '/maquina/mandar-distribucion',
  MAQUINA_OPERATIVA: '/maquina/poner-operativa',
  MAQUINA_MANTENIMIENTO: '/maquina/dar-mantenimiento',
  MAQUINA_FINALIZAR_MANTENIMIENTO: '/maquina/finalizar-mantenimiento',
  MAQUINA_BY_ENSAMBLADOR: (id: string) => `/maquina/ensamblador/${id}`,
  MAQUINA_BY_COMPROBADOR: (id: string) => `/maquina/comprobador/${id}`,
  MAQUINA_BY_MANTENIMIENTO: (id: string) => `/maquina/mantenimiento/${id}`,
  MAQUINA_BY_ESTADO: (estado: string) => `/maquina/estado/${estado}`,
  MAQUINA_BY_ETAPA: (etapa: string) => `/maquina/etapa/${etapa}`,
  MAQUINA_COMPONENTES: (id: string) => `/maquina/componentes/${id}`,
  
  // Componente
  COMPONENTES: '/componentes',
  COMPONENTES_USAR: '/componentes/usar',
  COMPONENTES_LIBERAR: '/componentes/liberar',
  COMPONENTES_ASIGNAR_CARCASA: '/componentes/asignar-carcasa',
  COMPONENTES_EN_USO: (id: string) => `/componentes/en-uso/${id}`,
  COMPONENTES_DISPONIBLES: '/componentes/disponibles',
  // Contabilidad
  RECAUDACION_REGISTRAR: '/contabilidad/registrar-recaudacion',
  RECAUDACIONES: '/contabilidad/recaudaciones',
  RECAUDACION_BY_ID: (id: string) => `/contabilidad/recaudaciones/${id}`,
  RECAUDACION_ACTUALIZAR: '/contabilidad/actualizar-recaudacion',
  RECAUDACION_ELIMINAR: (id: string) => `/contabilidad/eliminar-recaudacion/${id}`,
  RECAUDACION_RESUMEN: '/contabilidad/resumen-recaudaciones',
  MAQUINAS_RECAUDACION: '/contabilidad/maquinas-recaudacion',
  INFORME_GUARDAR: '/contabilidad/guardar-informe',
  INFORME_BY_RECAUDACION: (id: string) => `/contabilidad/informe/${id}`,
  
  // Distribución
  DISTRIBUCION_INFORMES: '/distribucion/informes',
  
  // Reportes
  REPORTES_CREAR: '/reportes/crear',
  REPORTES_BY_USER: (id: string) => `/reportes/usuario/${id}`,
  REPORTES_CHAT: (emisor: string, receptor: string) => `/reportes/chat/${emisor}/${receptor}`,
  REPORTES_UPDATE_ESTADO: (id: string) => `/reportes/${id}/estado`,
  REPORTES_USUARIOS_CHAT: '/reportes/usuarios-chat',
  
  // Comentarios
  COMENTARIOS: '/comentarios',
  COMENTARIOS_BY_REPORTE: (id: string) => `/comentarios/reporte/${id}`,
    COMENTARIO_EDITAR: (id: string) => `/comentarios/${id}`,
  COMENTARIO_ELIMINAR: (id: string) => `/comentarios/${id}`,
  // Notificaciones
  NOTIFICACIONES_MAQUINA: (id: string) => `/notificaciones_maquina/${id}`,
  NOTIFICACIONES: (id: string) => `/notificaciones/${id}`,
  NOTIFICACIONES_MARCAR_LEIDA: (id: string) => `/notificaciones/${id}/marcarla-leida`,
  NOTIFICACIONES_MARCAR_TODAS: '/notificaciones/marcarla-todas-leidas',
  NOTIFICACIONES_NO_LEIDAS: (id: string) => `/notificaciones/no-leidas/${id}`,
  
  // Historial
  HISTORIAL_MAQUINA: (id: string) => `/historial/maquina/${id}`,
  HISTORIAL_USUARIO: (id: string) => `/historial/usuario/${id}`,
  HISTORIAL_GENERAL: '/historial/general',
  HISTORIAL_ACTIVIDADES: '/historial-actividades'
};

/**
 * Configuración de caché
 */
export const CACHE_CONFIG = {
  TTL: 5 * 60 * 1000, // 5 minutos
  MAX_ITEMS: 100
};

/**
 * Expresiones regulares para validación
 */
export const VALIDATION_PATTERNS = {
  EMAIL: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
  CI: /^\d{10}$/,
  TELEFONO: /^\d{10}$/,
  UUID: /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i,
  PASSWORD: /^.{8,}$/
};