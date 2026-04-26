/**
 * @fileoverview Servicio de Autenticación
 * @description Maneja login, logout, registro y gestión de sesión
 * @service AuthService
 */

import { Injectable, inject, signal, computed } from '@angular/core';
import { Router } from '@angular/router';
import { Observable, tap, catchError, map, of } from 'rxjs';
import { ApiService } from './api';
import { User, LoginCredentials, RegisterData, AuthResponse } from '@core/models/user.model';
import { API_ENDPOINTS } from '@core/constants/app.constants';

/**
 * Servicio que gestiona la autenticación y estado del usuario
 */
@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private readonly apiService = inject(ApiService);
  private readonly router = inject(Router);
  
  /** Señal del usuario actual */
  private currentUserSignal = signal<User | null>(null);
  
  /** Señal computada para verificar si está autenticado */
  public isAuthenticated = computed(() => {return this.currentUserSignal() !== null || localStorage.getItem('user') !== null;
});
  
  /** Señal computada para obtener el rol del usuario */
  public userRole = computed(() => this.currentUserSignal()?.tipo || null);

  constructor() {
    this.loadUserFromStorage();
  }

  /**
   * Inicia sesión con credenciales
   * @param credentials - Credenciales de login
   * @returns Observable con respuesta de autenticación
   */
  login(credentials: LoginCredentials): Observable<AuthResponse> {
    return this.apiService.post<AuthResponse>(API_ENDPOINTS.LOGIN, credentials).pipe(
      tap(response => {
if (response.success && response['usuario']) {
          this.setSession(response);
        }
      }),
      map(response => response as AuthResponse)
    );
  }

  /**
   * Registra un nuevo usuario
   * @param userData - Datos de registro
   * @returns Observable con respuesta
   */
  register(userData: RegisterData): Observable<AuthResponse> {
    return this.apiService.post<AuthResponse>(API_ENDPOINTS.REGISTER, userData).pipe(
      map(response => response as AuthResponse)
    );
  }

  /**
   * Cierra la sesión del usuario
   * @returns Observable con respuesta
   */
  logout(): Observable<AuthResponse> {
    const userId = this.currentUserSignal()?.id;
    
    return this.apiService.post<AuthResponse>(API_ENDPOINTS.LOGOUT, { ID_Usuario: userId }).pipe(
      tap(() => {
        this.clearSession();
        this.router.navigate(['/auth/login']);
      }),
      catchError(error => {
        this.clearSession();
        this.router.navigate(['/auth/login']);
        return of(error);
      })
    );
  }

  /**
   * Obtiene el usuario actual
   * @returns Usuario actual o null
   */
  getCurrentUser(): User | null {
    return this.currentUserSignal();
  }

  /**
   * Verifica si el usuario tiene un rol específico
   * @param allowedRoles - Lista de roles permitidos
   * @returns true si el usuario tiene uno de los roles
   */
  hasRole(allowedRoles: string[]): boolean {
    const user = this.currentUserSignal();
    if (!user) return false;
    
    return allowedRoles.includes(user.tipo);
  }

  /**
   * Obtiene el token de autenticación
   * @returns Token o null
   */
  getToken(): string | null {
    return localStorage.getItem('token');
  }

  /**
   * Guarda la sesión en localStorage
   * @param response - Respuesta de autenticación
   */
private setSession(response: AuthResponse): void {
    if (response.usuario) {
        // Asegurar que la especialidad se guarde correctamente
        const userToSave = {
            ...response.usuario,
            // Asegurar que especialidad se guarde en ambos formatos para compatibilidad
            Especialidad: response.usuario.Especialidad || response.usuario.especialidad || '',
            especialidad: response.usuario.Especialidad || response.usuario.especialidad || ''
        };
        
        console.log('Guardando usuario en sesión:', userToSave);
        localStorage.setItem('user', JSON.stringify(userToSave));
        this.currentUserSignal.set(userToSave);
    }
    if (response.token) {
        localStorage.setItem('token', response.token);
    }
}
  /**
   * Limpia la sesión actual
   */
  public clearSession(): void {
    localStorage.removeItem('user');
    localStorage.removeItem('token');
    this.currentUserSignal.set(null);
  }

  /**
   * Carga el usuario desde localStorage al iniciar
   */
private loadUserFromStorage(): void {
    const userStr = localStorage.getItem('user');
    if (userStr && !this.currentUserSignal()) {
        try {
            const user = JSON.parse(userStr);
            // Normalizar la especialidad
            if (user && !user.Especialidad && user.especialidad) {
                user.Especialidad = user.especialidad;
            }
            if (user && !user.especialidad && user.Especialidad) {
                user.especialidad = user.Especialidad;
            }
            console.log('Usuario cargado desde storage:', user);
            this.currentUserSignal.set(user);
        } catch (e) {
            console.error('Error al cargar usuario:', e);
        }
    }
}
}