/**
 * @fileoverview Servicio de Administrador
 * @description Servicio para la gestión de usuarios por parte del administrador
 * @service AdminService
 */

import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
import { ApiService } from '@core/services/api';
import { API_ENDPOINTS } from '@core/constants/app.constants';
import { User } from '@core/models/user.model';

@Injectable({
  providedIn: 'root'
})
export class AdminService {
  private apiService = inject(ApiService);

  /**
   * Obtiene todos los usuarios del sistema
   * @param params - Parámetros de filtrado y paginación
   * @returns Observable con lista de usuarios y total
   */
  getUsuarios(params?: any): Observable<{ usuarios: User[]; total: number }> {
    return this.apiService.get<{ usuarios: User[]; total: number }>(API_ENDPOINTS.ADMIN_USERS, params).pipe(
      map(response => ({
        usuarios: response.success && response.usuarios ? response.usuarios : [],
        total: response.total || 0
      }))
    );
  }

  /**
   * Obtiene un usuario por su UUID
   * @param uuid - UUID del usuario
   * @returns Observable con el usuario
   */
  getUsuarioById(uuid: string): Observable<User | null> {
    return this.apiService.get<{ usuario: User }>(API_ENDPOINTS.ADMIN_USER_BY_ID(uuid)).pipe(
      map(response => response.success && response.usuario ? response.usuario : null)
    );
  }

  /**
   * Crea un nuevo usuario
   * @param userData - Datos del usuario
   * @returns Observable con resultado
   */
  crearUsuario(userData: Partial<User> & { contrasena: string }): Observable<{ success: boolean; message?: string }> {
    return this.apiService.post(API_ENDPOINTS.ADMIN_USERS, userData).pipe(
      map(response => ({ success: response.success, message: response.message }))
    );
  }

  /**
   * Actualiza un usuario existente
   * @param uuid - UUID del usuario
   * @param userData - Datos actualizados
   * @returns Observable con resultado
   */
  actualizarUsuario(uuid: string, userData: Partial<User>): Observable<{ success: boolean; message?: string }> {
    return this.apiService.put(API_ENDPOINTS.ADMIN_USER_BY_ID(uuid), userData).pipe(
      map(response => ({ success: response.success, message: response.message }))
    );
  }

  /**
   * Actualiza parcialmente un usuario (ej: solo estado)
   * @param uuid - UUID del usuario
   * @param userData - Datos parciales a actualizar
   * @returns Observable con resultado
   */
  actualizarUsuarioParcial(uuid: string, userData: Partial<User>): Observable<{ success: boolean; message?: string }> {
    return this.apiService.patch(API_ENDPOINTS.ADMIN_USER_BY_ID(uuid), userData).pipe(
      map(response => ({ success: response.success, message: response.message }))
    );
  }

  /**
   * Elimina un usuario
   * @param uuid - UUID del usuario
   * @returns Observable con resultado
   */
  eliminarUsuario(uuid: string): Observable<{ success: boolean; message?: string }> {
    return this.apiService.delete(API_ENDPOINTS.ADMIN_USER_BY_ID(uuid)).pipe(
      map(response => ({ success: response.success, message: response.message }))
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
  getUsuariosByTipo(tipo: string): Observable<User[]> {
    return this.apiService.get<{ usuarios: User[] }>('/usuarios/por-tipo', { tipo }).pipe(
      map(response => response.success && response.usuarios ? response.usuarios : [])
    );
  }
}