/**
 * @fileoverview Servicio de Reportes
 * @description Maneja la creación y gestión de reportes y comentarios
 * @service ReportService
 */

import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
import { ApiService } from './api';
import { Reporte, Comentario, CreateReporteData,EditarComentarioData  } from '@core/models/reporte.model';
import { API_ENDPOINTS } from '@core/constants/app.constants';

@Injectable({
  providedIn: 'root'
})
export class ReportService {
  private readonly apiService = inject(ApiService);

  createReporte(reporteData: CreateReporteData): Observable<string | null> {
    return this.apiService.post<{ reporteId: string }>(API_ENDPOINTS.REPORTES_CREAR, reporteData).pipe(
      map(response => response.success && response['reporteId'] ? response['reporteId'] : null)
    );
  }

  getReportesByUser(userId: string): Observable<Reporte[]> {
    return this.apiService.get<{ reportes: Reporte[] }>(API_ENDPOINTS.REPORTES_BY_USER(userId)).pipe(
      map(response => response.success && response['reportes'] ? response['reportes'] : [])
    );
  }

  getChat(emisorId: string, destinatarioId: string): Observable<{ reportes: Reporte[]; comentarios: Comentario[] }> {
    return this.apiService.get<{ reportes: Reporte[]; comentarios: Comentario[] }>(
      API_ENDPOINTS.REPORTES_CHAT(emisorId, destinatarioId)
    ).pipe(
      map(response => response.success ? { 
        reportes: response['reportes'] || [], 
        comentarios: response['comentarios'] || [] 
      } : { reportes: [], comentarios: [] })
    );
  }

  updateReporteStatus(reporteId: string, estado: string): Observable<boolean> {
    return this.apiService.put(API_ENDPOINTS.REPORTES_UPDATE_ESTADO(reporteId), { estado }).pipe(
      map(response => response.success)
    );
  }

  getUsuariosChat(userId: string): Observable<any[]> {
    return this.apiService.get<{ usuarios: any[] }>(API_ENDPOINTS.REPORTES_USUARIOS_CHAT, { userId }).pipe(
      map(response => response.success && response['usuarios'] ? response['usuarios'] : [])
    );
  }

createComentario(reporteId: string, usuarioId: string, comentario: string): Observable<boolean> {
  return this.apiService.post(API_ENDPOINTS.COMENTARIOS, {
    idReporte: reporteId,     //  camelCase
    comentario: comentario    //  camelCase
  }).pipe(
    map(response => response.success)
  );
}

  getComentariosByReporte(reporteId: string): Observable<Comentario[]> {
    return this.apiService.get<any>(API_ENDPOINTS.COMENTARIOS_BY_REPORTE(reporteId)).pipe(
      map(response => response.success && response['data'] ? response['data'] : [])
    );
  }
    /**
   * Editar un comentario existente
   * @param data Datos de edición (idComentario, comentario)
   * @returns Observable con éxito de la operación
   */
  editarComentario(data: EditarComentarioData): Observable<boolean> {
    return this.apiService.put<{ success: boolean }>(`/comentarios/${data.idComentario}`, {
      comentario: data.comentario
    }).pipe(
      map(response => response.success)
    );
  }

  /**
   * Eliminar un comentario
   * @param idComentario ID del comentario a eliminar
   * @returns Observable con éxito de la operación
   */
  eliminarComentario(idComentario: string): Observable<boolean> {
    return this.apiService.delete<{ success: boolean }>(`/comentarios/${idComentario}`).pipe(
      map(response => response.success)
    );
  }

/**
 * Obtener comentarios con información de permisos
 * @param reporteId ID del reporte
 * @returns Observable con lista de comentarios
 */
getComentariosConPermisos(reporteId: string): Observable<Comentario[]> {
  return this.apiService.get(API_ENDPOINTS.COMENTARIOS_BY_REPORTE(reporteId)).pipe(
    map((response: any) => {
      if (response?.success && response?.data && Array.isArray(response.data)) {
        return response.data as Comentario[];
      }
      if (response?.success && response?.comentarios && Array.isArray(response.comentarios)) {
        return response.comentarios as Comentario[];
      }
      return [];
    })
  );
}
}