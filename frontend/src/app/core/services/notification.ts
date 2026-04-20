/**
 * @fileoverview Servicio de Notificaciones
 * @description Maneja la obtención y gestión de notificaciones del sistema
 * @service NotificationService
 */

import { Injectable, inject, signal } from '@angular/core';
import { Observable, map, tap, of } from 'rxjs';
import { catchError } from 'rxjs/operators';
import { ApiService } from './api';
import { API_ENDPOINTS } from '@core/constants/app.constants';


/**
 * Interfaz para notificación de reportes
 */
export interface NotificacionReporte {
  ID_Notificaciones: string;
  mensaje: string;
  fecha_hora: string;
  leida: number;            // 0 = no leída, 1 = leída
  ID_Reporte?: string;
  reporte_descripcion?: string;
  emisor_nombre?: string;
  emisor_apellido?: string;
}

/**
 * Servicio para gestión de notificaciones
 */
@Injectable({
  providedIn: 'root'
})
export class NotificationService {
  private readonly apiService = inject(ApiService);
  
  /** Señal de notificaciones no leídas */
  private unreadCountSignal = signal<number>(0);
  
  /** Señal computada para contar notificaciones no leídas */
  unreadCount = this.unreadCountSignal.asReadonly();

  /**
   * Obtiene notificaciones de reportes de un usuario
   * @param userId - ID del usuario
   * @returns Observable con lista de notificaciones de reportes
   */
  getNotifications(userId: string): Observable<NotificacionReporte[]> {
    return this.apiService.get<{ notificaciones: NotificacionReporte[] }>(API_ENDPOINTS.NOTIFICACIONES(userId)).pipe(
      tap(response => {
        if (response && response.success && response['notificaciones']) {
          const unread = response['notificaciones'].filter((n: NotificacionReporte) => n.leida === 0).length;
          this.unreadCountSignal.set(unread);
        }
      }),
      map(response => (response && response.success && response['notificaciones']) ? response['notificaciones'] : []),
      catchError(error => {
        console.error('Error obteniendo notificaciones:', error);
        return of([]);
      })
    );
  }

  /**
   * Marca una notificación como leída
   * @param notificacionId - ID de la notificación
   * @returns Observable con resultado
   */
  markAsRead(notificacionId: string): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.NOTIFICACIONES_MARCAR_LEIDA(notificacionId), {}).pipe(
      tap(response => {
        if (response && response.success) {
          this.unreadCountSignal.update(count => Math.max(count - 1, 0));
        }
      }),
      map(response => response ? response.success : false),
      catchError(error => {
        console.error('Error marcando notificación como leída:', error);
        return of(false);
      })
    );
  }

  /**
   * Marca todas las notificaciones como leídas
   * @returns Observable con resultado
   */
  markAllAsRead(): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.NOTIFICACIONES_MARCAR_TODAS, {}).pipe(
      tap(response => {
        if (response && response.success) {
          this.unreadCountSignal.set(0);
        }
      }),
      map(response => response ? response.success : false),
      catchError(error => {
        console.error('Error marcando todas como leídas:', error);
        return of(false);
      })
    );
  }

  /**
   * Obtiene el conteo de notificaciones no leídas
   * @param userId - ID del usuario
   * @returns Observable con el conteo
   */
  getUnreadCount(userId: string): Observable<number> {
    return this.apiService.get<{ total: number }>(API_ENDPOINTS.NOTIFICACIONES_NO_LEIDAS(userId)).pipe(
      tap(response => {
        if (response && response.success && response['total'] !== undefined) {
          this.unreadCountSignal.set(response['total']);
        }
      }),
      map(response => (response && response.success && response['total']) ? response['total'] : 0),
      catchError(error => {
        console.error('Error obteniendo conteo de no leídas:', error);
        return of(0);
      })
    );
  }

  /**
   * Crea una notificación de máquina
   * @param notificacion - Datos de la notificación
   * @returns Observable con resultado
   */
  createNotification(notificacion: any): Observable<boolean> {
    return this.apiService.post('/notificaciones/create', notificacion).pipe(
      map(response => response ? response.success : false),
      catchError(error => {
        console.error('Error creando notificación:', error);
        return of(false);
      })
    );
  }
}