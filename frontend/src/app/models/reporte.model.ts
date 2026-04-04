/**
 * @fileoverview Modelo de Reportes
 * @description Define la estructura de datos para reportes y comentarios
 * @module models
 */

import { User } from './user.model';

/**
 * Interfaz que representa un reporte
 */
export interface Reporte {
  /** ID único del reporte */
  ID_Reporte: string;
  /** ID del usuario emisor */
  ID_Usuario_Emisor: string;
  /** ID del usuario destinatario */
  ID_Usuario_Destinatario: string;
  /** Descripción del reporte */
  descripcion: string;
  /** Estado del reporte */
  estado: string;
  /** Fecha y hora de creación */
  fecha_hora: string;
  /** Nombre del emisor */
  emisor_nombre?: string;
  /** Apellido del emisor */
  emisor_apellido?: string;
  /** Nombre del destinatario */
  destinatario_nombre?: string;
  /** Apellido del destinatario */
  destinatario_apellido?: string;
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