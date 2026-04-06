/**
 * @fileoverview Servicio de Contabilidad
 * @description Maneja todas las operaciones relacionadas con recaudaciones e informes
 * @service ContabilidadService
 */

import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
import { ApiService } from '@core/services/api';
import { API_ENDPOINTS } from '@core/constants/app.constants';
import { Recaudacion, RegisterRecaudacionData, ResumenRecaudacion, Comercio, InformeRecaudacion } from '@core/models/recaudacion.model';
import { Maquina } from '@core/models/maquina.model';

@Injectable({
  providedIn: 'root'
})
export class ContabilidadService {
  private apiService = inject(ApiService);

  /**
   * Registra una nueva recaudación
   * @param data - Datos de la recaudación
   * @returns Observable con resultado
   */
  registrarRecaudacion(data: RegisterRecaudacionData): Observable<{ success: boolean; message?: string; ID_Recaudacion?: string }> {
    return this.apiService.post(API_ENDPOINTS.RECAUDACION_REGISTRAR, data).pipe(
      map(response => ({
        success: response.success,
        message: response.message,
        ID_Recaudacion: response.ID_Recaudacion
      }))
    );
  }

  /**
   * Obtiene todas las recaudaciones con filtros
   * @param params - Parámetros de filtrado
   * @returns Observable con lista de recaudaciones
   */
  getRecaudaciones(params?: any): Observable<Recaudacion[]> {
    return this.apiService.get<{ recaudaciones: Recaudacion[] }>(API_ENDPOINTS.RECAUDACIONES, params).pipe(
      map(response => response.success && response.recaudaciones ? response.recaudaciones : [])
    );
  }

  /**
   * Obtiene una recaudación por ID
   * @param id - ID de la recaudación
   * @returns Observable con la recaudación
   */
  getRecaudacionById(id: string): Observable<Recaudacion | null> {
    return this.apiService.get<{ recaudacion: Recaudacion }>(API_ENDPOINTS.RECAUDACION_BY_ID(id)).pipe(
      map(response => response.success && response.recaudacion ? response.recaudacion : null)
    );
  }

  /**
   * Actualiza una recaudación
   * @param data - Datos actualizados
   * @returns Observable con resultado
   */
  actualizarRecaudacion(data: any): Observable<{ success: boolean; message?: string }> {
    return this.apiService.put(API_ENDPOINTS.RECAUDACION_ACTUALIZAR, data).pipe(
      map(response => ({ success: response.success, message: response.message }))
    );
  }

  /**
   * Elimina una recaudación
   * @param id - ID de la recaudación
   * @returns Observable con resultado
   */
  eliminarRecaudacion(id: string): Observable<{ success: boolean; message?: string }> {
    return this.apiService.delete(API_ENDPOINTS.RECAUDACION_ELIMINAR(id)).pipe(
      map(response => ({ success: response.success, message: response.message }))
    );
  }

  /**
   * Obtiene resumen de recaudaciones
   * @returns Observable con resumen
   */
  getResumenRecaudaciones(): Observable<ResumenRecaudacion[]> {
    return this.apiService.get<{ resumen: ResumenRecaudacion[] }>(API_ENDPOINTS.RECAUDACION_RESUMEN).pipe(
      map(response => response.success && response.resumen ? response.resumen : [])
    );
  }

  /**
   * Obtiene máquinas disponibles para recaudación
   * @returns Observable con lista de máquinas
   */
  getMaquinasRecaudacion(): Observable<Maquina[]> {
    return this.apiService.get<{ maquinas: Maquina[] }>(API_ENDPOINTS.MAQUINAS_RECAUDACION).pipe(
      map(response => response.success && response.maquinas ? response.maquinas : [])
    );
  }

  /**
   * Obtiene máquinas operativas por comercio
   * @param idComercio - ID del comercio
   * @returns Observable con lista de máquinas
   */
  getMaquinasOperativasPorComercio(idComercio: string): Observable<Maquina[]> {
    return this.apiService.get<{ maquinas: Maquina[] }>(API_ENDPOINTS.MAQUINAS_OPERATIVAS_POR_COMERCIO, { ID_Comercio: idComercio }).pipe(
      map(response => response.success && response.maquinas ? response.maquinas : [])
    );
  }

  /**
   * Obtiene todos los comercios
   * @returns Observable con lista de comercios
   */
  getComercios(): Observable<Comercio[]> {
    return this.apiService.get<{ comercios: Comercio[] }>(API_ENDPOINTS.COMERCIOS).pipe(
      map(response => response.success && response.comercios ? response.comercios : [])
    );
  }

  /**
   * Guarda un informe de recaudación
   * @param data - Datos del informe
   * @returns Observable con resultado
   */
  guardarInforme(data: any): Observable<{ success: boolean; message?: string }> {
    return this.apiService.post(API_ENDPOINTS.INFORME_GUARDAR, data).pipe(
      map(response => ({ success: response.success, message: response.message }))
    );
  }

  /**
   * Obtiene un informe por ID de recaudación
   * @param idRecaudacion - ID de la recaudación
   * @returns Observable con el informe
   */
  getInformeByRecaudacion(idRecaudacion: string): Observable<InformeRecaudacion | null> {
    return this.apiService.get<{ informe: InformeRecaudacion }>(API_ENDPOINTS.INFORME_BY_RECAUDACION(idRecaudacion)).pipe(
      map(response => response.success && response.informe ? response.informe : null)
    );
  }

  /**
   * Obtiene informes de distribución
   * @param params - Parámetros de filtrado
   * @returns Observable con lista de informes
   */
  getInformesDistribucion(params?: any): Observable<any[]> {
    return this.apiService.get<{ informes: any[] }>(API_ENDPOINTS.DISTRIBUCION_INFORMES, params).pipe(
      map(response => response.success && response.informes ? response.informes : [])
    );
  }
}