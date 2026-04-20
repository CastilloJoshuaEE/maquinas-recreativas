import { Injectable, inject } from '@angular/core';
import { Observable, map, catchError, of } from 'rxjs';
import { ApiService } from '@core/services/api';

export interface NotificacionMaquina {
  ID_Notificacion: string;
  ID_Remitente: string;
  ID_Destinatario: string;
  ID_Maquina: string;
  Tipo: string;
  Mensaje: string;
  Fecha: string;
  Estado: string;
  Nombre_Maquina?: string;
  NombreComercio?: string;
}

@Injectable({ providedIn: 'root' })
export class NotificacionMaquinaService {
  private apiService = inject(ApiService);

  getNotificacionesMaquina(userId: string): Observable<NotificacionMaquina[]> {
    return this.apiService.get<{ notificaciones: NotificacionMaquina[] }>(
      `/notificaciones_maquina/${userId}`
    ).pipe(
      map(response => response.success && response['notificaciones'] ? response['notificaciones'] : []),
      catchError(() => of([]))
    );
  }
// notification-maquina.service.ts
marcarComoLeida(notificacionId: string): Observable<boolean> {
  console.log('🔧 NotificacionMaquinaService.marcarComoLeida:', notificacionId);
  
  if (!notificacionId) {
    console.error('❌ ID de notificación es undefined');
    return of(false);
  }
  
  // ✅ Usar el endpoint de notificaciones general (que ya existe)
  const url = `/notificaciones/${notificacionId}/marcarla-leida`;
  console.log('  URL:', url);
  
  return this.apiService.post(url, {}).pipe(
    map(response => {
      console.log('  Respuesta:', response);
      return response?.success ?? false;
    }),
    catchError((error) => {
      console.error('  Error:', error);
      return of(false);
    })
  );
}
}