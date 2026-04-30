/**
 * @fileoverview Servicio para consulta de historial de máquinas recreativas
 * @service HistorialService
 */

import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
import { ApiService } from '@core/services/api';
import {
  HistorialMaquina,
  HistorialResponse,
  ResumenHistorial,
  FiltrosHistorial
} from '@core/models/historial.model';

@Injectable({
  providedIn: 'root'
})
export class HistorialService {
  private readonly api = inject(ApiService);

  /**
   * Obtiene el historial de una máquina específica (paginado)
   */
  getHistorialPorMaquina(
    idMaquina: string,
    pagina: number = 1,
    porPagina: number = 50
  ): Observable<HistorialResponse> {
    return this.api
      .getLong<HistorialResponse>(
        `historial/maquina/${idMaquina}`,
        { pagina, por_pagina: porPagina }
      )
      .pipe(
        map(res => ({
          success: res.success,
          historial: (res as any).historial ?? [],
          paginacion: (res as any).paginacion ?? {
            pagina_actual: pagina,
            por_pagina: porPagina,
            total: 0,
            total_paginas: 0
          }
        }))
      );
  }

  /**
   * Obtiene el historial de actividades de un usuario (paginado)
   */
  getHistorialPorUsuario(
    idUsuario: string,
    pagina: number = 1,
    porPagina: number = 50
  ): Observable<HistorialResponse> {
    return this.api
      .getLong<HistorialResponse>(
        `historial/usuario/${idUsuario}`,
        { pagina, por_pagina: porPagina }
      )
      .pipe(
        map(res => ({
          success: res.success,
          historial: (res as any).historial ?? [],
          paginacion: (res as any).paginacion ?? {
            pagina_actual: pagina,
            por_pagina: porPagina,
            total: 0,
            total_paginas: 0
          }
        }))
      );
  }

  /**
   * Obtiene el historial general con filtros avanzados
   */
  getHistorialGeneral(filtros: FiltrosHistorial = {}): Observable<HistorialResponse> {
    const params: Record<string, any> = {};
    if (filtros.idMaquina)   params['idMaquina']   = filtros.idMaquina;
    if (filtros.idUsuario)   params['idUsuario']   = filtros.idUsuario;
    if (filtros.tipoUsuario) params['tipoUsuario'] = filtros.tipoUsuario;
    if (filtros.accion)      params['accion']      = filtros.accion;
    if (filtros.fechaInicio) params['fechaInicio'] = filtros.fechaInicio;
    if (filtros.fechaFin)    params['fechaFin']    = filtros.fechaFin;
    params['pagina']    = filtros.pagina    ?? 1;
    params['por_pagina'] = filtros.por_pagina ?? 100;

    return this.api
      .getLong<HistorialResponse>('historial/general', params)
      .pipe(
        map(res => ({
          success: res.success,
          historial: (res as any).historial ?? [],
          paginacion: (res as any).paginacion ?? {
            pagina_actual: params['pagina'],
            por_pagina: params['por_pagina'],
            total: 0,
            total_paginas: 0
          }
        }))
      );
  }

  /**
   * Obtiene el resumen de actividades recientes
   */
  getResumenReciente(limite: number = 20): Observable<ResumenHistorial> {
    return this.api
      .get<ResumenHistorial>('historial/resumen', { limite })
      .pipe(map(res => (res as any).resumen as ResumenHistorial));
  }
}