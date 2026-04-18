/**
 * @fileoverview Modelo de Usuario
 * @description Define la estructura de datos para usuarios del sistema
 * @module models
 */

/**
 * Interfaz que representa un usuario del sistema
 */
export interface User {
  /** ID único del usuario (UUID) */
  id: string;
  /** Número de cédula */
  ci: string;
  /** Nombre del usuario */
  nombre: string;
  /** Apellido del usuario */
  apellido: string;
  /** Correo electrónico */
  email: string;
  /** Nombre de usuario asignado */
  usuario_asignado: string;
  /** Estado del usuario (Activo, Inhabilitado, Pendiente de asignacion) */
  estado: string;
  /** Tipo/Rol del usuario */
  tipo: string;
  /** Especialidad (solo para técnicos) */
  Especialidad?: string;
    especialidad?: string;

  /** ID del técnico (solo para técnicos) */
  ID_Tecnico?: string;
  /** Fecha de creación */
  fecha_creacion?: string;
  /** Fecha de última sesión */
  ultima_sesion?: string;
}

/**
 * Interfaz para credenciales de login
 */
export interface LoginCredentials {
  /** Nombre de usuario */
  usuario_asignado: string;
  /** Contraseña */
  contrasena: string;
}

/**
 * Interfaz para registro de usuario
 */
export interface RegisterData {
  nombre: string;
  apellido: string;
  ci: string;
  email: string;
  usuario_asignado: string;
  contrasena: string;
  tipo: string;
  especialidad?: string;
}

/**
 * Interfaz para actualización de perfil
 */
export interface UpdateProfileData {
  id: string;
  nombre: string;
  apellido: string;
  email: string;
  ci: string;
  tipo: string;
  estado: string;
  especialidad?: string | null;
  contrasena?: string;
}

/**
 * Interfaz para respuesta de autenticación
 */
export interface AuthResponse {
  success: boolean;
  message?: string;
  usuario?: User;
  token?: string;
  fecha_inicio?: string;
}

/**
 * Interfaz para historial de actividades
 */
export interface HistorialActividad {
  ID_Historial: number;
  ID_Usuario: string;
  accion: string;
  descripcion: string;
  fecha_hora: string;
  ip_address: string;
  usuario_nombre?: string;
  usuario_apellido?: string;
  tipo_usuario?: string;
}