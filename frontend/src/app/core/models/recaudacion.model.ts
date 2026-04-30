/**
 * @fileoverview Modelo de Recaudación
 * @description Define la estructura de datos para recaudaciones e informes
 * @module models
 */

/**
 * Interfaz que representa una recaudación
 */
export interface Recaudacion {
  /** ID único de la recaudación */
  ID_Recaudacion: string;
  /** ID de la máquina */
  ID_Maquina: string;
  /** Nombre de la máquina */
  Nombre_Maquina?: string;
  /** ID del comercio */
  ID_Comercio?: string;
  /** Nombre del comercio */
  Nombre_Comercio?: string;
  /** Tipo de comercio */
  Tipo_Comercio: string;
  /** Monto total recaudado */
  Monto_Total: number;
  /** Monto para la empresa */
  Monto_Empresa: number;
  /** Monto para el comercio */
  Monto_Comercio: number;
  /** Porcentaje para el comercio (mayorista) */
  Porcentaje_Comercio?: number;
  /** Fecha de la recaudación */
  fecha: string;
  /** Detalles adicionales */
  detalle?: string;
  /** ID del usuario que registró */
  ID_Usuario?: string;
  /** ID del informe asociado */
  ID_Informe?: string;
}

/**
 * Interfaz para registro de recaudación
 */
export interface RegisterRecaudacionData {
  ID_Comercio: string;
  ID_Maquina: string;
  Tipo_Comercio: string;
  Porcentaje_Comercio?: number;
  Monto_Total: number;
  Monto_Comercio: number;
  Monto_Empresa: number;
  fecha: string;
  detalle?: string;
  id: string;
}

/**
 * Interfaz que representa un comercio
 */
export interface Comercio {
  ID_Comercio: string;
  Nombre: string;
  Tipo: string;
  Direccion: string;
  Telefono: string;
  fecha_registro?: string;
  nombre?: string;          //  minúscula (fallback)
  tipo?: string;            //  minúscula (fallback)
  NombreComercio?: string;  //  alternativa
}

/**
 * Interfaz que representa un informe de recaudación
 */
export interface InformeRecaudacion {
  ID_Informe: string;
  ID_Recaudacion: string;
  ID_Comercio: string;
  Nombre_Comercio: string;
  Direccion_Comercio: string;
  Telefono_Comercio: string;
  Nombre_Maquina: string;
  CI_Usuario: string;
  Pago_Ensamblador: number;
  Pago_Comprobador: number;
  Pago_Mantenimiento?: number;
  Monto_Total: number;
  fecha_emision: string;
  empresa_nombre?: string;
  empresa_descripcion?: string;
}

/**
 * Interfaz para resumen de recaudaciones
 */
export interface ResumenRecaudacion {
  Tipo_Comercio: string;
  TotalRecaudaciones: number;
  TotalRecaudado: number;
  TotalEmpresa: number;
  TotalComercio: number;
}