/**
 * @fileoverview Servicio de Máquinas Compartido
 * @description Maneja operaciones relacionadas con máquinas recreativas
 * @service MaquinasSharedService
 */

import { Injectable, inject } from '@angular/core';
import { Observable, catchError, map, of } from 'rxjs';
import { ApiService } from '@core/services/api';
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
  private readonly apiService = inject(ApiService);

  /**
   * Obtiene máquinas con filtro opcional de estado o etapa.
   * Sin filtros obtiene todas las máquinas.
   */
  getMaquinas(params?: { estado?: string; etapa?: string }): Observable<{ success: boolean; maquinas: Maquina[] }> {
    if (params?.estado) {
      return this.apiService.get<{ maquinas: Maquina[] }>(API_ENDPOINTS.MAQUINA_BY_ESTADO(params.estado)).pipe(
        map(response => ({
          success: response.success,
          maquinas: response.success && response['maquinas'] ? response['maquinas'] : []
        })),
        catchError(() => of({ success: false, maquinas: [] }))
      );
    }
    
    if (params?.etapa) {
      return this.apiService.get<{ maquinas: Maquina[] }>(API_ENDPOINTS.MAQUINA_BY_ETAPA(params.etapa)).pipe(
        map(response => ({
          success: response.success,
          maquinas: response.success && response['maquinas'] ? response['maquinas'] : []
        })),
        catchError(() => of({ success: false, maquinas: [] }))
      );
    }
    
    return this.getTodasMaquinas();
  }

  /**
   * Obtiene TODAS las máquinas con datos de comercio
   */
getTodasMaquinas(): Observable<{ success: boolean; maquinas: Maquina[] }> {
  return this.apiService.get<{ maquinas: any[] }>(API_ENDPOINTS.MAQUINA_ALL).pipe(
    map(response => {
      const maquinas = (response.success && response['maquinas']) ? response['maquinas'].map((m: any) => ({
        ID_Maquina: m.ID_Maquina,
        Nombre_Maquina: m.Nombre_Maquina,
        tipo: m.tipo || m.Tipo || '',      // ← Normalizar
        estado: m.estado || m.Estado || '', // ← Normalizar
        etapa: m.etapa || m.Etapa || '',    // ← Normalizar
        ID_Comercio: m.ID_Comercio,
        NombreComercio: m.NombreComercio,
        DireccionComercio: m.DireccionComercio,
        ID_Tecnico_Ensamblador: m.ID_Tecnico_Ensamblador,
        ID_Tecnico_Comprobador: m.ID_Tecnico_Comprobador,
        Fecha_Registro: m.Fecha_Registro
      })) : [];
      
      return {
        success: response.success,
        maquinas: maquinas
      };
    }),
    catchError(() => of({ success: false, maquinas: [] }))
  );
}
  /**
   * Obtiene una máquina por su ID
   */
  getMaquinaById(id: string): Observable<{ success: boolean; maquina: Maquina }> {
    return this.apiService.get<{ maquina: Maquina }>(`/maquina/${id}`).pipe(
      map(response => ({
        success: response.success,
        maquina: response.success && response['maquina'] ? response['maquina'] : null as any
      })),
      catchError(() => of({ success: false, maquina: null as any }))
    );
  }

  /**
   * Crea una nueva máquina
   */
  createMaquina(data: CreateMaquinaData): Observable<{ success: boolean; message: string; maquinaId?: string }> {
    return this.apiService.post<{ message: string; idMaquina?: string }>(API_ENDPOINTS.MAQUINA_REGISTER, data).pipe(
      map(response => ({
        success: response.success,
        message: response.message || '',
        maquinaId: response['idMaquina']
      })),
      catchError(() => of({ success: false, message: 'Error al crear máquina' }))
    );
  }

  /**
   * Actualiza una máquina
   */
  updateMaquina(data: UpdateMaquinaData): Observable<{ success: boolean; message: string }> {
    return this.apiService.put<{ message: string }>(`/maquina/${data.idMaquina}`, data).pipe(
      map(response => ({
        success: response.success,
        message: response.message || ''
      })),
      catchError(() => of({ success: false, message: 'Error al actualizar máquina' }))
    );
  }

  /**
   * Elimina una máquina
   */
  deleteMaquina(id: string): Observable<{ success: boolean; message: string }> {
    return this.apiService.delete<{ message: string }>(`/maquina/${id}`).pipe(
      map(response => ({
        success: response.success,
        message: response.message || ''
      })),
      catchError(() => of({ success: false, message: 'Error al eliminar máquina' }))
    );
  }

  /**
   * Obtiene el historial de una máquina (paginado)
   */
  getHistorialMaquina(
    idMaquina: string,
    pagina: number = 1,
    porPagina: number = 50
  ): Observable<{
    success: boolean;
    historial: HistorialEvento[];
    paginacion: { total: number; pagina: number; por_pagina: number; total_paginas: number };
  }> {
    return this.apiService.get<{
      historial: HistorialEvento[];
      paginacion: { total: number; pagina: number; por_pagina: number; total_paginas: number };
    }>(`/historial/maquina/${idMaquina}`, { pagina, por_pagina: porPagina }).pipe(
      map(response => ({
        success: response.success,
        historial: response.success && response['historial'] ? response['historial'] : [],
        paginacion: response.success && response['paginacion'] ? response['paginacion'] : {
          total: 0,
          pagina: pagina,
          por_pagina: porPagina,
          total_paginas: 0
        }
      })),
      catchError(() => of({
        success: false,
        historial: [],
        paginacion: { total: 0, pagina: 1, por_pagina: porPagina, total_paginas: 0 }
      }))
    );
  }

  /**
   * Obtiene los componentes de una máquina
   */
  getComponentesMaquina(idMaquina: string): Observable<{ success: boolean; componentes: any[] }> {
    return this.apiService.get<{ componentes: any[] }>(API_ENDPOINTS.MAQUINA_COMPONENTES(idMaquina)).pipe(
      map(response => ({
        success: response.success,
        componentes: response.success && response['componentes'] ? response['componentes'] : []
      })),
      catchError(() => of({ success: false, componentes: [] }))
    );
  }
}