/**
 * @fileoverview Servicio de Usuario
 * @description Maneja operaciones relacionadas con usuarios
 * @service UserService
 */
import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
import { ApiService } from './api';
import { User, UpdateProfileData, HistorialActividad } from '@core/models/user.model';
import { API_ENDPOINTS } from '@core/constants/app.constants';
import { catchError } from 'rxjs/operators'; 
import { of } from 'rxjs';
@Injectable({ providedIn: 'root' })
export class UserService {
  private readonly apiService = inject(ApiService);

  getProfile(userId: string): Observable<User | null> {
    return this.apiService.get<{ usuario: User }>(API_ENDPOINTS.USER_PROFILE, { id: userId }).pipe(
      map(response => response.success && response['usuario'] ? response['usuario'] : null)
    );
  }

  updateProfile(profileData: UpdateProfileData): Observable<{ success: boolean; message?: string }> {
    return this.apiService.post(API_ENDPOINTS.UPDATE_PROFILE, profileData).pipe(
      map(response => ({ success: response.success, message: response.message }))
    );
  }

  recoverPassword(email: string, newPassword: string): Observable<{ success: boolean; message?: string }> {
    return this.apiService.post(API_ENDPOINTS.RECOVER_PASSWORD, { email, nueva_contrasena: newPassword }).pipe(
      map(response => ({ success: response.success, message: response.message }))
    );
  }

  // CORREGIDO: el backend espera "nuevo_usuario", no "usuario_asignado"
  recoverUsername(email: string, newUsername: string): Observable<{ success: boolean; message?: string }> {
    return this.apiService.post(API_ENDPOINTS.RECOVER_USERNAME, { email, nuevo_usuario: newUsername }).pipe(
      map(response => ({ success: response.success, message: response.message }))
    );
  }

  searchByEmail(email: string): Observable<User | null> {
    return this.apiService.post<{ usuario: User }>(API_ENDPOINTS.SEARCH_BY_EMAIL, { email }).pipe(
      map(response => response.success && response['usuario'] ? response['usuario'] : null)
    );
  }

registrarActividad(userId: string, descripcion: string): Observable<boolean> {
  return this.apiService.post(API_ENDPOINTS.HISTORIAL_ACTIVIDADES, { ID_Usuario: userId, descripcion }).pipe(
    map(response => response?.success ?? false),
    catchError(() => of(false))   
  );
}

  getHistorialActividades(userId: string, params?: any): Observable<HistorialActividad[]> {
    return this.apiService.get<{ historial: HistorialActividad[] }>(
      API_ENDPOINTS.HISTORIAL_ACTIVIDADES,
      { usuarioId: userId, ...params }
    ).pipe(
      map(response => response.success && response['historial'] ? response['historial'] : [])
    );
  }

  getTecnicosByEspecialidad(especialidad: string): Observable<User[]> {
    return this.apiService.get<{ tecnicos: User[] }>(`/usuario/tecnicos/${especialidad}`).pipe(
      map(response => response.success && response['tecnicos'] ? response['tecnicos'] : [])
    );
  }

  getUsersByTipo(tipo: string, emisorId?: string): Observable<User[]> {
    return this.apiService.get<{ usuarios: User[] }>('/usuarios/por-tipo', { tipo, emisorId }).pipe(
      map(response => response.success && response['usuarios'] ? response['usuarios'] : [])
    );
  }
}