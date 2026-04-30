export interface Notificacion {
  // Notificaciones de reportes
  ID_Notificaciones?: string;
  // Notificaciones de máquina
  ID_Notificacion?: string;
  
  mensaje: string;
  fecha_hora: string;
  leida: number | boolean;
  ID_Reporte?: string;
  reporte_descripcion?: string;
  emisor_nombre?: string;
  emisor_apellido?: string;
  Tipo?: string;
  Nombre_Maquina?: string;
  NombreComercio?: string;
  Estado?: string;
}