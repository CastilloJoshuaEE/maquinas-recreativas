/**
 * @fileoverview Servicio de Reportes
 * @description Maneja la creación y gestión de reportes y comentarios
 * @service ReportService
 */

import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
import { ApiService } from './api';
import { Reporte, Comentario, CreateReporteData } from '@core/models/reporte.model';
import { API_ENDPOINTS } from '@core/constants/app.constants';

/**
 * Servicio para gestión de reportes
 */
@Injectable({
  providedIn: 'root'
})
export class ReportService {
  private readonly apiService = inject(ApiService);

  /**
   * Crea un nuevo reporte
   * @param reporteData - Datos del reporte
   * @returns Observable con ID del reporte creado
   */
  createReporte(reporteData: CreateReporteData): Observable<string | null> {
    return this.apiService.post<{ reporteId: string }>(API_ENDPOINTS.REPORTES_CREAR, reporteData).pipe(
      map(response => response.success && response.reporteId ? response.reporteId : null)
    );
  }

  /**
   * Obtiene reportes de un usuario
   * @param userId - ID del usuario
   * @returns Observable con lista de reportes
   */
  getReportesByUser(userId: string): Observable<Reporte[]> {
    return this.apiService.get<{ reportes: Reporte[] }>(API_ENDPOINTS.REPORTES_BY_USER(userId)).pipe(
      map(response => response.success && response.reportes ? response.reportes : [])
    );
  }

  /**
   * Obtiene chat entre dos usuarios
   * @param emisorId - ID del emisor
   * @param destinatarioId - ID del destinatario
   * @returns Observable con datos del chat
   */
  getChat(emisorId: string, destinatarioId: string): Observable<{ reportes: Reporte[]; comentarios: Comentario[] }> {
    return this.apiService.get<{ reportes: Reporte[]; comentarios: Comentario[] }>(
      API_ENDPOINTS.REPORTES_CHAT(emisorId, destinatarioId)
    ).pipe(
      map(response => response.success ? { reportes: response.reportes || [], comentarios: response.comentarios || [] } : { reportes: [], comentarios: [] })
    );
  }

  /**
   * Actualiza el estado de un reporte
   * @param reporteId - ID del reporte
   * @param estado - Nuevo estado
   * @returns Observable con resultado
   */
  updateReporteStatus(reporteId: string, estado: string): Observable<boolean> {
    return this.apiService.put(API_ENDPOINTS.REPORTES_UPDATE_ESTADO(reporteId), { estado }).pipe(
      map(response => response.success)
    );
  }

  /**
   * Obtiene usuarios disponibles para chat
   * @param userId - ID del usuario actual
   * @returns Observable con lista de usuarios
   */
  getUsuariosChat(userId: string): Observable<any[]> {
    return this.apiService.get<{ usuarios: any[] }>(API_ENDPOINTS.REPORTES_USUARIOS_CHAT, { userId }).pipe(
      map(response => response.success && response.usuarios ? response.usuarios : [])
    );
  }

  /**
   * Crea un comentario en un reporte
   * @param reporteId - ID del reporte
   * @param usuarioId - ID del usuario emisor
   * @param comentario - Contenido del comentario
   * @returns Observable con resultado
   */
  createComentario(reporteId: string, usuarioId: string, comentario: string): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.COMENTARIOS, {
      ID_Reporte: reporteId,
      ID_Usuario_Emisor: usuarioId,
      comentario
    }).pipe(
      map(response => response.success)
    );
  }

  /**
   * Obtiene comentarios de un reporte
   * @param reporteId - ID del reporte
   * @returns Observable con lista de comentarios
   */
  getComentariosByReporte(reporteId: string): Observable<Comentario[]> {
    return this.apiService.get<{ data: Comentario[] }>(API_ENDPOINTS.COMENTARIOS_BY_REPORTE(reporteId)).pipe(
      map(response => response.success && response.data ? response.data : [])
    );
  }
}