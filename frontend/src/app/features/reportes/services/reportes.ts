import { Injectable, inject } from '@angular/core';
import { Observable, map, catchError, of } from 'rxjs';
import { ApiService } from '@core/services/api';
import { API_ENDPOINTS } from '@core/constants/app.constants';
import { Reporte, Comentario, CreateReporteData } from '@core/models/reporte.model';
import { User } from '@core/models/user.model';

@Injectable({ providedIn: 'root' })
export class ReportesService {
  private apiService = inject(ApiService);

  crearReporte(data: CreateReporteData): Observable<string | null> {
    console.log('Enviando reporte al backend:', data);
    return this.apiService.post(API_ENDPOINTS.REPORTES_CREAR, data).pipe(
      map(response => {
        console.log('Respuesta del backend:', response);
        if (response && response.success) {
          return response['id'] || response['reporteId'] || null;
        }
        return null;
      }),
      catchError(error => {
        console.error('Error en crearReporte:', error);
        return of(null);
      })
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

  getUsuariosChat(userId: string): Observable<User[]> {
    return this.apiService.get<{ usuarios: User[] }>(API_ENDPOINTS.REPORTES_USUARIOS_CHAT, { userId }).pipe(
      map(response => response.success && response['usuarios'] ? response['usuarios'] : [])
    );
  }
crearComentario(reporteId: string, usuarioId: string, comentario: string): Observable<boolean> {
  return this.apiService.post(API_ENDPOINTS.COMENTARIOS, {
    idReporte: reporteId,     //  camelCase
    comentario: comentario    //  camelCase
  }).pipe(
    map(response => response.success)
  );
}

  getComentarios(reporteId: string): Observable<Comentario[]> {
    return this.apiService.get<any>(API_ENDPOINTS.COMENTARIOS_BY_REPORTE(reporteId)).pipe(
      map(response => response.success && response['data'] ? response['data'] : [])
    );
  }

  getNotificaciones(userId: string): Observable<any[]> {
    return this.apiService.get<{ notificaciones: any[] }>(API_ENDPOINTS.NOTIFICACIONES(userId)).pipe(
      map(response => response.success && response['notificaciones'] ? response['notificaciones'] : [])
    );
  }

  marcarNotificacionLeida(notificacionId: string): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.NOTIFICACIONES_MARCAR_LEIDA(notificacionId), {}).pipe(
      map(response => response.success)
    );
  }
  marcarTodasNotificacionesLeidas(): Observable<boolean> {
  return this.apiService.post(API_ENDPOINTS.NOTIFICACIONES_MARCAR_TODAS, {}).pipe(
    map(response => response?.success ?? false),
    catchError(error => {
      console.error('Error marcando todas como leídas:', error);
      return of(false);
    })
  );
}
}