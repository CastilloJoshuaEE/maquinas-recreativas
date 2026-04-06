/**
 * @fileoverview Servicio de Reportes
 * @description Maneja la lógica de negocio para reportes y notificaciones
 * @service ReportesService
 */

import { Injectable, inject } from '@angular/core';
import { Observable, map, BehaviorSubject } from 'rxjs';
import { ApiService } from '@core/services/api';
import { API_ENDPOINTS } from '@core/constants/app.constants';
import { Reporte, Comentario, CreateReporteData } from '@core/models/reporte.model';
import { User } from '@core/models/user.model';

@Injectable({
  providedIn: 'root'
})
export class ReportesService {
  private apiService = inject(ApiService);
  
  private reportesSubject = new BehaviorSubject<Reporte[]>([]);
  reportes$ = this.reportesSubject.asObservable();
  
  private notificacionesSubject = new BehaviorSubject<any[]>([]);
  notificaciones$ = this.notificacionesSubject.asObservable();
  
  private notificacionesNoLeidasSubject = new BehaviorSubject<number>(0);
  notificacionesNoLeidas$ = this.notificacionesNoLeidasSubject.asObservable();

  /**
   * Crea un nuevo reporte
   * @param data - Datos del reporte
   * @returns Observable con ID del reporte
   */
  crearReporte(data: CreateReporteData): Observable<string | null> {
    return this.apiService.post<{ reporteId: string }>(API_ENDPOINTS.REPORTES_CREAR, data).pipe(
      map(response => {
        if (response.success) {
          this.actualizarReportes();
          return response.reporteId || null;
        }
        return null;
      })
    );
  }

  /**
   * Obtiene reportes de un usuario
   * @param userId - ID del usuario
   * @returns Observable con lista de reportes
   */
  getReportesByUser(userId: string): Observable<Reporte[]> {
    return this.apiService.get<{ reportes: Reporte[] }>(API_ENDPOINTS.REPORTES_BY_USER(userId)).pipe(
      map(response => {
        const reportes = response.success && response.reportes ? response.reportes : [];
        this.reportesSubject.next(reportes);
        return reportes;
      })
    );
  }

  /**
   * Actualiza el estado de un reporte
   * @param reporteId - ID del reporte
   * @param estado - Nuevo estado
   * @returns Observable con resultado
   */
  actualizarEstado(reporteId: string, estado: string): Observable<boolean> {
    return this.apiService.put(API_ENDPOINTS.REPORTES_UPDATE_ESTADO(reporteId), { estado }).pipe(
      map(response => {
        if (response.success) {
          this.actualizarReportes();
          return true;
        }
        return false;
      })
    );
  }

  /**
   * Obtiene chat entre dos usuarios
   * @param emisorId - ID del emisor
   * @param destinatarioId - ID del destinatario
   * @returns Observable con reportes y comentarios
   */
  getChat(emisorId: string, destinatarioId: string): Observable<{ reportes: Reporte[]; comentarios: Comentario[] }> {
    return this.apiService.get<{ reportes: Reporte[]; comentarios: Comentario[] }>(
      API_ENDPOINTS.REPORTES_CHAT(emisorId, destinatarioId)
    ).pipe(
      map(response => ({
        reportes: response.success && response.reportes ? response.reportes : [],
        comentarios: response.success && response.comentarios ? response.comentarios : []
      }))
    );
  }

  /**
   * Obtiene usuarios disponibles para chat
   * @param userId - ID del usuario actual
   * @returns Observable con lista de usuarios
   */
  getUsuariosChat(userId: string): Observable<User[]> {
    return this.apiService.get<{ usuarios: User[] }>(API_ENDPOINTS.REPORTES_USUARIOS_CHAT, { userId }).pipe(
      map(response => response.success && response.usuarios ? response.usuarios : [])
    );
  }

  /**
   * Crea un comentario en un reporte
   * @param reporteId - ID del reporte
   * @param usuarioId - ID del usuario
   * @param comentario - Texto del comentario
   * @returns Observable con resultado
   */
  crearComentario(reporteId: string, usuarioId: string, comentario: string): Observable<boolean> {
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
  getComentarios(reporteId: string): Observable<Comentario[]> {
    return this.apiService.get<{ data: Comentario[] }>(API_ENDPOINTS.COMENTARIOS_BY_REPORTE(reporteId)).pipe(
      map(response => response.success && response.data ? response.data : [])
    );
  }

  /**
   * Obtiene notificaciones de un usuario
   * @param userId - ID del usuario
   * @returns Observable con lista de notificaciones
   */
  getNotificaciones(userId: string): Observable<any[]> {
    return this.apiService.get<{ notificaciones: any[] }>(API_ENDPOINTS.NOTIFICACIONES(userId)).pipe(
      map(response => {
        const notificaciones = response.success && response.notificaciones ? response.notificaciones : [];
        this.notificacionesSubject.next(notificaciones);
        const noLeidas = notificaciones.filter(n => !n.leida).length;
        this.notificacionesNoLeidasSubject.next(noLeidas);
        return notificaciones;
      })
    );
  }

  /**
   * Marca una notificación como leída
   * @param notificacionId - ID de la notificación
   * @returns Observable con resultado
   */
  marcarNotificacionLeida(notificacionId: string): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.NOTIFICACIONES_MARCAR_LEIDA(notificacionId), {}).pipe(
      map(response => {
        if (response.success) {
          const current = this.notificacionesNoLeidasSubject.value;
          this.notificacionesNoLeidasSubject.next(Math.max(current - 1, 0));
          return true;
        }
        return false;
      })
    );
  }

  /**
   * Marca todas las notificaciones como leídas
   * @returns Observable con resultado
   */
  marcarTodasNotificacionesLeidas(): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.NOTIFICACIONES_MARCAR_TODAS, {}).pipe(
      map(response => {
        if (response.success) {
          this.notificacionesNoLeidasSubject.next(0);
          return true;
        }
        return false;
      })
    );
  }

  /**
   * Actualiza la lista de reportes
   */
  private actualizarReportes(): void {
    const userStr = localStorage.getItem('user');
    if (userStr) {
      const user = JSON.parse(userStr);
      if (user.ID_Usuario) {
        this.getReportesByUser(user.ID_Usuario).subscribe();
      }
    }
  }
}