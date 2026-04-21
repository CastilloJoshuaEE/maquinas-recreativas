/**
 * @fileoverview Modelo de Máquina Recreativa
 * @description Define la estructura de datos para máquinas recreativas
 * @module models
 */

import { Comercio } from './recaudacion.model';
import { Componente } from './componente.model';

/**
 * Interfaz que representa una máquina recreativa
 */
export interface Maquina {
  /** ID único de la máquina (UUID) */
  ID_Maquina: string;
  /** Nombre de la máquina */
  Nombre_Maquina: string;
  /** Tipo de máquina */
  tipo: string;
  /** Estado actual */
  estado: string;
  /** Etapa actual del ciclo de vida */
  etapa: string;
  /** ID del comercio asociado */
  ID_Comercio: string;
  /** Nombre del comercio */
  NombreComercio?: string;
  /** Dirección del comercio */
  DireccionComercio?: string;
  /** ID del técnico ensamblador */
  ID_Tecnico_Ensamblador?: string;
  /** ID del técnico comprobador */
  ID_Tecnico_Comprobador?: string;
  /** ID del técnico de mantenimiento */
  ID_Tecnico_Mantenimiento?: string;
  /** ID de la placa base */
  ID_Placa?: string;
  /** ID de la carcasa */
  ID_Carcasa?: string;
  /** Fecha de creación */
  fecha_creacion?: string;
  /** Fecha de última actualización */
  fecha_actualizacion?: string;
}

/**
 * Interfaz para registro de máquina
 */
export interface RegisterMaquinaData {
  nombre: string;
  tipo: string;
  idComercio: string;
  idUsuarioLogistica: string;
  idPlaca: string;
  idCarcasa: string;
}

/**
 * Interfaz para respuesta de máquinas
 */
export interface MaquinaResponse {
  success: boolean;
  message?: string;
  maquinas?: Maquina[];
  maquina?: Maquina;
  total?: number;
}

/**
 * Interfaz para acción sobre máquina
 */
export interface MaquinaActionData {
  idMaquina: string;
  idRemitente?: string;
  mensaje?: string;
  exito?: boolean;
}

/**
 * Interfaz para informe de distribución
 */
export interface InformeDistribucion {
  ID_Distribucion: string;
  ID_Maquina: string;
  Nombre_Maquina: string;
  ID_Tecnico: string;
  Nombre_Tecnico?: string;
  ID_Comercio: string;
  Nombre_Comercio?: string;
  fecha_alta: string;
  fecha_baja?: string;
  estado: string;
}

export interface CreateMaquinaData {
  nombre: string;
  tipo: string;
  idComercio: string;
  idUsuarioLogistica: string;
  idPlaca: string;
  idCarcasa: string;
}

export interface UpdateMaquinaData {
  idMaquina: string;
  nombre?: string;
  tipo?: string;
  idComercio?: string;
  estado?: string;
}