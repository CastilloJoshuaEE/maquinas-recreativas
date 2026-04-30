/**
 * @fileoverview Modelo de Componente
 * @description Define la estructura de datos para componentes de máquinas
 * @module models
 */

/**
 * Interfaz que representa un componente
 */
export interface Componente {
  /** ID único del componente (UUID) */
  ID_Componente: string;
  /** Nombre del componente */
  nombre: string;
  /** Tipo de componente */
  tipo: string;
  /** Precio del componente */
  precio: number;
  /** Estado de uso (Usado, Disponible, Liberado) */
  estado_uso?: string;
  /** ID de la máquina donde está instalado */
  ID_Maquina?: string;
  /** ID del usuario que lo usa */
  id?: string;
  /** Fecha de asignación */
  fecha_asignacion?: string;
  /** Nombre de la máquina (para joins) */
  Nombre_Maquina?: string;
}

/**
 * Interfaz para uso de componente
 */
export interface UsarComponenteData {
  ID_Componente: string;
  id: string;
  ID_Maquina: string;
}

/**
 * Interfaz para liberar componente
 */
export interface LiberarComponenteData {
  idComponente: string;
  id?: string;
}

/**
 * Interfaz para asignar carcasa
 */
export interface AsignarCarcasaData {
  ID_Componente: string;
  ID_Usuario: string;
}

/**
 * Interfaz para respuesta de componentes
 */
export interface ComponenteResponse {
  success: boolean;
  message?: string;
  componentes?: Componente[];
  componente?: Componente;
  total?: number;
  id_componente?: string;
  placa?: string;
}