/**
 * @fileoverview Servicio de Técnico
 * @description Maneja todas las operaciones relacionadas con técnicos y máquinas
 * @service TecnicoService
 */

import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
import { ApiService } from '@core/services/api';
import { API_ENDPOINTS } from '@core/constants/app.constants';
import { Maquina, MaquinaActionData } from '@core/models/maquina.model';
import { Componente, UsarComponenteData, LiberarComponenteData } from '@core/models/componente.model';

@Injectable({ providedIn: 'root' })
export class TecnicoService {
  private apiService = inject(ApiService);

  getMaquinasEnsamblador(idTecnico: string): Observable<Maquina[]> {
    return this.apiService.get<{ maquinas: Maquina[] }>(API_ENDPOINTS.MAQUINA_BY_ENSAMBLADOR(idTecnico)).pipe(
      map(response => response.success && response['maquinas'] ? response['maquinas'] : [])
    );
  }

  getMaquinasComprobador(idTecnico: string): Observable<Maquina[]> {
    return this.apiService.get<{ maquinas: Maquina[] }>(API_ENDPOINTS.MAQUINA_BY_COMPROBADOR(idTecnico)).pipe(
      map(response => response.success && response['maquinas'] ? response['maquinas'] : [])
    );
  }

  getMaquinasMantenimiento(idTecnico: string): Observable<Maquina[]> {
    return this.apiService.get<{ maquinas: Maquina[] }>(API_ENDPOINTS.MAQUINA_BY_MANTENIMIENTO(idTecnico)).pipe(
      map(response => response.success && response['maquinas'] ? response['maquinas'] : [])
    );
  }

  mandarAComprobacion(data: MaquinaActionData): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.MAQUINA_COMPROBACION, data).pipe(map(response => response.success));
  }

  mandarAReensamblar(data: MaquinaActionData): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.MAQUINA_REENSAMBLAR, data).pipe(map(response => response.success));
  }

  mandarADistribucion(data: MaquinaActionData): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.MAQUINA_DISTRIBUCION, data).pipe(map(response => response.success));
  }

  finalizarMantenimiento(data: { idMaquina: string; idRemitente: string; exito: boolean; mensaje: string }): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.MAQUINA_FINALIZAR_MANTENIMIENTO, data).pipe(map(response => response.success));
  }

  // Corregir: Usar URL directa ya que COMPONENTES_DISPONIBLES no existe en API_ENDPOINTS
  getComponentesDisponibles(tipo?: string, page: number = 1, limit: number = 10): Observable<{ componentes: Componente[]; total: number }> {
    const params: any = { page, limit };
    if (tipo) params.tipo = tipo;
    return this.apiService.get<{ componentes: Componente[]; total: number }>('/componentes/disponibles', params).pipe(
      map(response => ({ 
        componentes: response.success && response['componentes'] ? response['componentes'] : [], 
        total: response['total'] || 0 
      }))
    );
  }

  usarComponente(data: UsarComponenteData): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.COMPONENTES_USAR, data).pipe(map(response => response.success));
  }

  liberarComponente(data: LiberarComponenteData): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.COMPONENTES_LIBERAR, data).pipe(map(response => response.success));
  }

  getComponentesEnUso(idUsuario: string, idMaquina?: string): Observable<Componente[]> {
    let url = API_ENDPOINTS.COMPONENTES_EN_USO(idUsuario);
    if (idMaquina) url += `?id_maquina=${idMaquina}`;
    return this.apiService.get<{ componentes: Componente[] }>(url).pipe(
      map(response => response.success && response['componentes'] ? response['componentes'] : [])
    );
  }

  getHistorialMaquina(idMaquina: string, pagina: number = 1, porPagina: number = 20): Observable<{ historial: any[]; paginacion: any }> {
    return this.apiService.get<{ historial: any[]; paginacion: any }>(API_ENDPOINTS.HISTORIAL_MAQUINA(idMaquina), { pagina, por_pagina: porPagina }).pipe(
      map(response => ({ 
        historial: response.success && response['historial'] ? response['historial'] : [], 
        paginacion: response['paginacion'] || { pagina_actual: 1, total_paginas: 1, total: 0 } 
      }))
    );
  }
}