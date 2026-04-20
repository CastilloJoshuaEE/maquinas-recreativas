/**
 * @fileoverview Modelo de Reportes
 * @description Define la estructura de datos para reportes y comentarios
 * @module models
 */

import { User } from './user.model';

/**
 * Interfaz que representa un reporte
 */
// reporte.model.ts
export interface Reporte {
  // Campos del backend (minúsculas)
  id?: string;
  id_emisor?: string;
  id_destinatario?: string;
  
  // Campos alternativos (PascalCase)
  ID_Reporte?: string;
  ID_Usuario_Emisor?: string;
  ID_Usuario_Destinatario?: string;
  
  // Campos comunes
  descripcion: string;
  estado: string;
  fecha_hora: string;
  
  // Nombres (pueden venir o no)
  emisor_nombre?: string;
  emisor_apellido?: string;
  nombre_emisor?: string;
  destinatario_nombre?: string;
  destinatario_apellido?: string;
  nombre_destinatario?: string;
}

/**
 * Interfaz para creación de reporte
 */
export interface CreateReporteData {
  ID_Usuario_Emisor: string;
  ID_Usuario_Destinatario: string;
  descripcion: string;
}

/**
 * Interfaz que representa un comentario
 */
export interface Comentario {
  /** ID único del comentario */
  ID_Comentario: string;
  /** ID del reporte asociado */
  ID_Reporte: string;
  /** ID del usuario emisor */
  ID_Usuario_Emisor: string;
  /** Contenido del comentario */
  comentario: string;
  /** Fecha y hora */
  fecha_hora: string;
  /** Nombre del usuario */
  nombre?: string;
  /** Apellido del usuario */
  apellido?: string;
}

/**
 * Interfaz para chat entre usuarios
 */
export interface ChatData {
  reportes: Reporte[];
  comentarios: Comentario[];
  usuario_actual: User;
  usuario_destino: User;
}