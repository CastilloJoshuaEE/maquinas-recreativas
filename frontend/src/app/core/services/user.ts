/**
 * @fileoverview Servicio de Usuario
 * @description Maneja operaciones relacionadas con usuarios (perfil, actualizaciones, etc.)
 * @service UserService
 */

import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
import { ApiService } from './api.service';
import { User, UpdateProfileData, HistorialActividad } from '@core/models/user.model';
import { API_ENDPOINTS } from '@core/constants/app.constants';

/**
 * Servicio para gestión de usuarios
 */
@Injectable({
  providedIn: 'root'
})
export class UserService {
  private readonly apiService = inject(ApiService);

  /**
   * Obtiene el perfil de un usuario
   * @param userId - ID del usuario (UUID)
   * @returns Observable con datos del perfil
   */
  getProfile(userId: string): Observable<User | null> {
    return this.apiService.get<{ usuario: User }>(API_ENDPOINTS.USER_PROFILE, { id: userId }).pipe(
      map(response => response.success && response.usuario ? response.usuario : null)
    );
  }

  /**
   * Actualiza el perfil de un usuario
   * @param profileData - Datos actualizados del perfil
   * @returns Observable con resultado
   */
  updateProfile(profileData: UpdateProfileData): Observable<{ success: boolean; message?: string }> {
    return this.apiService.post(API_ENDPOINTS.UPDATE_PROFILE, profileData).pipe(
      map(response => ({ success: response.success, message: response.message }))
    );
  }

  /**
   * Recupera contraseña
   * @param email - Correo electrónico
   * @param newPassword - Nueva contraseña
   * @returns Observable con resultado
   */
  recoverPassword(email: string, newPassword: string): Observable<{ success: boolean; message?: string }> {
    return this.apiService.post(API_ENDPOINTS.RECOVER_PASSWORD, { email, nueva_contrasena: newPassword }).pipe(
      map(response => ({ success: response.success, message: response.message }))
    );
  }

  /**
   * Recupera nombre de usuario
   * @param email - Correo electrónico
   * @param newUsername - Nuevo nombre de usuario
   * @returns Observable con resultado
   */
  recoverUsername(email: string, newUsername: string): Observable<{ success: boolean; message?: string }> {
    return this.apiService.post(API_ENDPOINTS.RECOVER_USERNAME, { email, usuario_asignado: newUsername }).pipe(
      map(response => ({ success: response.success, message: response.message }))
    );
  }

  /**
   * Busca usuario por email
   * @param email - Correo electrónico
   * @returns Observable con datos del usuario
   */
  searchByEmail(email: string): Observable<User | null> {
    return this.apiService.post<{ usuario: User }>(API_ENDPOINTS.SEARCH_BY_EMAIL, { email }).pipe(
      map(response => response.success && response.usuario ? response.usuario : null)
    );
  }

  /**
   * Registra una actividad en el historial
   * @param userId - ID del usuario
   * @param descripcion - Descripción de la actividad
   * @returns Observable con resultado
   */
  registrarActividad(userId: string, descripcion: string): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.HISTORIAL_ACTIVIDADES, { ID_Usuario: userId, descripcion }).pipe(
      map(response => response.success)
    );
  }

  /**
   * Obtiene historial de actividades de un usuario
   * @param userId - ID del usuario
   * @param params - Parámetros de filtrado
   * @returns Observable con historial
   */
  getHistorialActividades(userId: string, params?: any): Observable<HistorialActividad[]> {
    return this.apiService.get<{ historial: HistorialActividad[] }>(
      API_ENDPOINTS.HISTORIAL_ACTIVIDADES, 
      { usuarioId: userId, ...params }
    ).pipe(
      map(response => response.success && response.historial ? response.historial : [])
    );
  }

  /**
   * Obtiene técnicos por especialidad
   * @param especialidad - Especialidad del técnico
   * @returns Observable con lista de técnicos
   */
  getTecnicosByEspecialidad(especialidad: string): Observable<User[]> {
    return this.apiService.get<{ tecnicos: User[] }>(`/usuario/tecnicos/${especialidad}`).pipe(
      map(response => response.success && response.tecnicos ? response.tecnicos : [])
    );
  }

  /**
   * Obtiene usuarios por tipo
   * @param tipo - Tipo de usuario
   * @returns Observable con lista de usuarios
   */
  getUsersByTipo(tipo: string, emisorId?: string): Observable<User[]> {
    return this.apiService.get<{ usuarios: User[] }>(
      API_ENDPOINTS.USERS_BY_TIPO, 
      { tipo, emisorId }
    ).pipe(
      map(response => response.success && response.usuarios ? response.usuarios : [])
    );
  }
}