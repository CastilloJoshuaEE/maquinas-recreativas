/**
 * @fileoverview Modelos para el historial de máquinas recreativas
 */

export interface HistorialMaquina {
  ID_Historial: string;
  ID_Maquina: string;
  ID_Usuario: string;
  tipo_usuario: string;
  accion: string;
  descripcion: string;
  estado_anterior: string | null;
  estado_ string | null;
  etapa_anterior: string | null;
  etapa_nueva: string | null;
  ip_address: string | null;
  detalles_adicionales: Record<string, any> | null;
  fecha_hora: string;
  // Campos JOIN
  usuario_nombre?: string;
  usuario_apellido?: string;
  usuario_tipo?: string;
  Nombre_Maquina?: string;
  NombreComercio?: string;
}

export interface PaginacionHistorial {
  pagina_actual: number;
  por_pagina: number;
  total: number;
  total_paginas: number;
}

export interface HistorialResponse {
  success: boolean;
  historial: HistorialMaquina[];
  paginacion: PaginacionHistorial;
}

export interface ResumenHistorial {
  total: number;
  por_accion: Record<string, number>;
  recientes: {
    id: string;
    accion: string;
    descripcion: string;
    fecha: string;
    maquina: string;
  }[];
}

export interface FiltrosHistorial {
  idMaquina?: string;
  idUsuario?: string;
  tipoUsuario?: string;
  accion?: string;
  fechaInicio?: string;
  fechaFin?: string;
  pagina?: number;
  por_pagina?: number;
}