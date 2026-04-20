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
    idReporte: reporteId,     // ✅ camelCase
    comentario: comentario    // ✅ camelCase
  }).pipe(
    map(response => response.success)
  );
}

  getComentariosByReporte(reporteId: string): Observable<Comentario[]> {
    return this.apiService.get<any>(API_ENDPOINTS.COMENTARIOS_BY_REPORTE(reporteId)).pipe(
      map(response => response.success && response['data'] ? response['data'] : [])
    );
  }
}