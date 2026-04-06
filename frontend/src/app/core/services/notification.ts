/**
 * @fileoverview Servicio de Notificaciones
 * @description Maneja la obtención y gestión de notificaciones del sistema
 * @service NotificationService
 */

import { Injectable, inject, signal } from '@angular/core';
import { Observable, map, tap } from 'rxjs';
import { ApiService } from './api.service';
import { API_ENDPOINTS } from '@core/constants/app.constants';

/**
 * Interfaz para notificación
 */
export interface Notificacion {
  ID_Notificaciones: string;
  mensaje: string;
  fecha_hora: string;
  leida: number;
  ID_Reporte?: string;
  reporte_descripcion?: string;
  emisor_nombre?: string;
  emisor_apellido?: string;
  Tipo?: string;
  Nombre_Maquina?: string;
  NombreComercio?: string;
  DireccionComercio?: string;
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
   * Obtiene notificaciones de un usuario
   * @param userId - ID del usuario
   * @returns Observable con lista de notificaciones
   */
  getNotifications(userId: string): Observable<Notificacion[]> {
    return this.apiService.get<{ notificaciones: Notificacion[] }>(API_ENDPOINTS.NOTIFICACIONES(userId)).pipe(
      tap(response => {
        if (response.success && response.notificaciones) {
          const unread = response.notificaciones.filter(n => !n.leida).length;
          this.unreadCountSignal.set(unread);
        }
      }),
      map(response => response.success && response.notificaciones ? response.notificaciones : [])
    );
  }

  /**
   * Obtiene notificaciones de máquina por usuario
   * @param userId - ID del usuario
   * @returns Observable con lista de notificaciones de máquina
   */
  getMaquinaNotifications(userId: string): Observable<Notificacion[]> {
    return this.apiService.get<{ notificaciones: Notificacion[] }>(API_ENDPOINTS.NOTIFICACIONES_MAQUINA(userId)).pipe(
      map(response => response.success && response.notificaciones ? response.notificaciones : [])
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
        if (response.success) {
          this.unreadCountSignal.update(count => Math.max(count - 1, 0));
        }
      }),
      map(response => response.success)
    );
  }

  /**
   * Marca todas las notificaciones como leídas
   * @returns Observable con resultado
   */
  markAllAsRead(): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.NOTIFICACIONES_MARCAR_TODAS, {}).pipe(
      tap(response => {
        if (response.success) {
          this.unreadCountSignal.set(0);
        }
      }),
      map(response => response.success)
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
        if (response.success && response.total !== undefined) {
          this.unreadCountSignal.set(response.total);
        }
      }),
      map(response => response.success && response.total ? response.total : 0)
    );
  }

  /**
   * Crea una notificación
   * @param notificacion - Datos de la notificación
   * @returns Observable con resultado
   */
  createNotification(notificacion: any): Observable<boolean> {
    return this.apiService.post('/notificaciones/create', notificacion).pipe(
      map(response => response.success)
    );
  }
}