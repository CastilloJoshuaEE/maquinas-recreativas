/**
 * @fileoverview Servicio de Técnico
 * @description Maneja todas las operaciones relacionadas con técnicos y máquinas
 * @service TecnicoService
 */

import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
import { ApiService } from '@core/services/api.service';
import { API_ENDPOINTS } from '@core/constants/app.constants';
import { Maquina, MaquinaActionData } from '@core/models/maquina.model';
import { Componente, UsarComponenteData, LiberarComponenteData } from '@core/models/componente.model';
import { User } from '@core/models/user.model';

@Injectable({
  providedIn: 'root'
})
export class TecnicoService {
  private apiService = inject(ApiService);

  /**
   * Obtiene máquinas asignadas a un técnico ensamblador
   * @param idTecnico - ID del técnico ensamblador
   * @returns Observable con lista de máquinas
   */
  getMaquinasEnsamblador(idTecnico: string): Observable<Maquina[]> {
    return this.apiService.get<{ maquinas: Maquina[] }>(API_ENDPOINTS.MAQUINA_BY_ENSAMBLADOR(idTecnico)).pipe(
      map(response => response.success && response.maquinas ? response.maquinas : [])
    );
  }

  /**
   * Obtiene máquinas asignadas a un técnico comprobador
   * @param idTecnico - ID del técnico comprobador
   * @returns Observable con lista de máquinas
   */
  getMaquinasComprobador(idTecnico: string): Observable<Maquina[]> {
    return this.apiService.get<{ maquinas: Maquina[] }>(API_ENDPOINTS.MAQUINA_BY_COMPROBADOR(idTecnico)).pipe(
      map(response => response.success && response.maquinas ? response.maquinas : [])
    );
  }

  /**
   * Obtiene máquinas asignadas a un técnico de mantenimiento
   * @param idTecnico - ID del técnico de mantenimiento
   * @returns Observable con lista de máquinas
   */
  getMaquinasMantenimiento(idTecnico: string): Observable<Maquina[]> {
    return this.apiService.get<{ maquinas: Maquina[] }>(API_ENDPOINTS.MAQUINA_BY_MANTENIMIENTO(idTecnico)).pipe(
      map(response => response.success && response.maquinas ? response.maquinas : [])
    );
  }

  /**
   * Envía una máquina a comprobación
   * @param data - Datos de la acción
   * @returns Observable con resultado
   */
  mandarAComprobacion(data: MaquinaActionData): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.MAQUINA_COMPROBACION, data).pipe(
      map(response => response.success)
    );
  }

  /**
   * Envía una máquina a reensamblar
   * @param data - Datos de la acción
   * @returns Observable con resultado
   */
  mandarAReensamblar(data: MaquinaActionData): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.MAQUINA_REENSAMBLAR, data).pipe(
      map(response => response.success)
    );
  }

  /**
   * Envía una máquina a distribución
   * @param data - Datos de la acción
   * @returns Observable con resultado
   */
  mandarADistribucion(data: MaquinaActionData): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.MAQUINA_DISTRIBUCION, data).pipe(
      map(response => response.success)
    );
  }

  /**
   * Finaliza el mantenimiento de una máquina
   * @param data - Datos de la acción
   * @returns Observable con resultado
   */
  finalizarMantenimiento(data: { idMaquina: string; idRemitente: string; exito: boolean; mensaje: string }): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.MAQUINA_FINALIZAR_MANTENIMIENTO, data).pipe(
      map(response => response.success)
    );
  }

  /**
   * Obtiene componentes disponibles para un técnico
   * @param tipo - Tipo de componente (opcional)
   * @param page - Página actual
   * @param limit - Límite por página
   * @returns Observable con lista de componentes
   */
  getComponentesDisponibles(tipo?: string, page: number = 1, limit: number = 10): Observable<{ componentes: Componente[]; total: number }> {
    const params: any = { page, limit };
    if (tipo) params.tipo = tipo;
    
    return this.apiService.get<{ componentes: Componente[]; total: number }>(API_ENDPOINTS.COMPONENTES_DISPONIBLES, params).pipe(
      map(response => ({
        componentes: response.success && response.componentes ? response.componentes : [],
        total: response.total || 0
      }))
    );
  }

  /**
   * Usa un componente en una máquina
   * @param data - Datos de uso del componente
   * @returns Observable con resultado
   */
  usarComponente(data: UsarComponenteData): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.COMPONENTES_USAR, data).pipe(
      map(response => response.success)
    );
  }

  /**
   * Libera un componente de una máquina
   * @param data - Datos de liberación del componente
   * @returns Observable con resultado
   */
  liberarComponente(data: LiberarComponenteData): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.COMPONENTES_LIBERAR, data).pipe(
      map(response => response.success)
    );
  }

  /**
   * Obtiene componentes en uso por un técnico en una máquina
   * @param idUsuario - ID del usuario técnico
   * @param idMaquina - ID de la máquina (opcional)
   * @returns Observable con lista de componentes en uso
   */
  getComponentesEnUso(idUsuario: string, idMaquina?: string): Observable<Componente[]> {
    let url = API_ENDPOINTS.COMPONENTES_EN_USO(idUsuario);
    if (idMaquina) {
      url += `?id_maquina=${idMaquina}`;
    }
    
    return this.apiService.get<{ componentes: Componente[] }>(url).pipe(
      map(response => response.success && response.componentes ? response.componentes : [])
    );
  }

  /**
   * Obtiene el historial de una máquina
   * @param idMaquina - ID de la máquina
   * @param pagina - Número de página
   * @param porPagina - Elementos por página
   * @returns Observable con historial y paginación
   */
  getHistorialMaquina(idMaquina: string, pagina: number = 1, porPagina: number = 20): Observable<{ historial: any[]; paginacion: any }> {
    return this.apiService.get<{ historial: any[]; paginacion: any }>(API_ENDPOINTS.HISTORIAL_MAQUINA(idMaquina), { pagina, por_pagina: porPagina }).pipe(
      map(response => ({
        historial: response.success && response.historial ? response.historial : [],
        paginacion: response.paginacion || { pagina_actual: 1, total_paginas: 1, total: 0 }
      }))
    );
  }
}