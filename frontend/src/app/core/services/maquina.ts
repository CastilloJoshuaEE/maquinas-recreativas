import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { API_ENDPOINTS } from '@core/constants/app.constants';
import { Maquina, CreateMaquinaData, UpdateMaquinaData } from '@core/models/maquina.model';

export interface HistorialEvento {
  ID_Historial: string;
  ID_Maquina: string;
  ID_Usuario: string;
  tipo_usuario: string;
  accion: string;
  descripcion: string;
  estado_anterior: string;
  estado_nuevo: string;
  etapa_anterior: string;
  etapa_nueva: string;
  fecha_hora: string;
  ip_address: string;
  usuario_nombre?: string;
  usuario_apellido?: string;
  Nombre_Maquina?: string;
}

@Injectable({ providedIn: 'root' })
export class MaquinasSharedService {
  private http = inject(HttpClient);

  // Obtener todas las máquinas (con filtro opcional)
  getMaquinas(params?: { estado?: string; etapa?: string }): Observable<{ success: boolean; maquinas: Maquina[] }> {
    let url = API_ENDPOINTS.MAQUINA_BY_ESTADO(params?.estado || '');
    if (params?.etapa) {
      url = API_ENDPOINTS.MAQUINA_BY_ETAPA(params.etapa);
    }
    return this.http.get<{ success: boolean; maquinas: Maquina[] }>(url);
  }

  // Obtener máquina por ID
  getMaquinaById(id: string): Observable<{ success: boolean; maquina: Maquina }> {
    return this.http.get<{ success: boolean; maquina: Maquina }>(`/maquina/${id}`);
  }

  // Crear máquina
  createMaquina(data: CreateMaquinaData): Observable<{ success: boolean; message: string; maquinaId?: string }> {
    return this.http.post<{ success: boolean; message: string; maquinaId?: string }>(
      API_ENDPOINTS.MAQUINA_REGISTER,
      data
    );
  }

  // Actualizar máquina
  updateMaquina(data: UpdateMaquinaData): Observable<{ success: boolean; message: string }> {
    return this.http.put<{ success: boolean; message: string }>(`/maquina/${data.idMaquina}`, data);
  }

  // Eliminar máquina (si aplica)
  deleteMaquina(id: string): Observable<{ success: boolean; message: string }> {
    return this.http.delete<{ success: boolean; message: string }>(`/maquina/${id}`);
  }

  // Obtener historial de máquina
  getHistorialMaquina(idMaquina: string, pagina: number = 1, porPagina: number = 50): Observable<{
    success: boolean;
    historial: HistorialEvento[];
    paginacion: { total: number; pagina: number; por_pagina: number; total_paginas: number };
  }> {
    return this.http.get<any>(`/historial/maquina/${idMaquina}?pagina=${pagina}&por_pagina=${porPagina}`);
  }

  // Obtener componentes de máquina
  getComponentesMaquina(idMaquina: string): Observable<{ success: boolean; componentes: any[] }> {
    return this.http.get<{ success: boolean; componentes: any[] }>(API_ENDPOINTS.MAQUINA_COMPONENTES(idMaquina));
  }
}