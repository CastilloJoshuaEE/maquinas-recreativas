/**
 * @fileoverview Servicio base para comunicación con API
 * @description Servicio que maneja todas las peticiones HTTP al backend
 * @service ApiService
 */

import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams, HttpErrorResponse } from '@angular/common/http';
import { Observable, throwError, of } from 'rxjs';
import { catchError, retry, timeout, map } from 'rxjs/operators';
import { environment } from '@env/environment';

/**
 * Interfaz para respuesta estándar de la API
 */
export interface ApiResponse<T = any> {
  success: boolean;
  message?: string;
  data?: T;
  [key: string]: any;
}

/**
 * Servicio base para peticiones HTTP
 * Maneja errores, timeouts y reintentos automáticos
 */
@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private readonly http = inject(HttpClient);
  
  /** URL base de la API desde variables de entorno */
  private readonly baseUrl = environment.apiUrl;
  
  /** Timeout por defecto para peticiones (30 segundos) */
  private readonly defaultTimeout = 30000;
  
  /** Número de reintentos para peticiones fallidas */
  private readonly retryCount = 1;

  /**
   * Realiza una petición GET
   * @param endpoint - Endpoint de la API
   * @param params - Parámetros de consulta opcionales
   * @returns Observable con la respuesta
   */
  get<T = any>(endpoint: string, params?: any): Observable<ApiResponse<T>> {
    const url = this.buildUrl(endpoint);
    const httpParams = this.buildParams(params);
    
    return this.http.get<ApiResponse<T>>(url, { params: httpParams })
      .pipe(
        timeout(this.defaultTimeout),
        retry(this.retryCount),
        catchError(this.handleError<T>)
      );
  }

  /**
   * Realiza una petición POST
   * @param endpoint - Endpoint de la API
   * @param body - Cuerpo de la petición
   * @returns Observable con la respuesta
   */
  post<T = any>(endpoint: string, body: any): Observable<ApiResponse<T>> {
    const url = this.buildUrl(endpoint);
    
    return this.http.post<ApiResponse<T>>(url, body)
      .pipe(
        timeout(this.defaultTimeout),
        retry(this.retryCount),
        catchError(this.handleError<T>)
      );
  }

  /**
   * Realiza una petición PUT
   * @param endpoint - Endpoint de la API
   * @param body - Cuerpo de la petición
   * @returns Observable con la respuesta
   */
  put<T = any>(endpoint: string, body: any): Observable<ApiResponse<T>> {
    const url = this.buildUrl(endpoint);
    
    return this.http.put<ApiResponse<T>>(url, body)
      .pipe(
        timeout(this.defaultTimeout),
        retry(this.retryCount),
        catchError(this.handleError<T>)
      );
  }

  /**
   * Realiza una petición PATCH
   * @param endpoint - Endpoint de la API
   * @param body - Cuerpo de la petición
   * @returns Observable con la respuesta
   */
  patch<T = any>(endpoint: string, body: any): Observable<ApiResponse<T>> {
    const url = this.buildUrl(endpoint);
    
    return this.http.patch<ApiResponse<T>>(url, body)
      .pipe(
        timeout(this.defaultTimeout),
        retry(this.retryCount),
        catchError(this.handleError<T>)
      );
  }

  /**
   * Realiza una petición DELETE
   * @param endpoint - Endpoint de la API
   * @returns Observable con la respuesta
   */
  delete<T = any>(endpoint: string): Observable<ApiResponse<T>> {
    const url = this.buildUrl(endpoint);
    
    return this.http.delete<ApiResponse<T>>(url)
      .pipe(
        timeout(this.defaultTimeout),
        retry(this.retryCount),
        catchError(this.handleError<T>)
      );
  }

  /**
   * Construye la URL completa
   * @param endpoint - Endpoint relativo
   * @returns URL completa
   */
  private buildUrl(endpoint: string): string {
    // Eliminar slash inicial si existe para evitar doble slash
    const cleanEndpoint = endpoint.startsWith('/') ? endpoint.substring(1) : endpoint;
    return `${this.baseUrl}/${cleanEndpoint}`;
  }

  /**
   * Construye los parámetros HTTP
   * @param params - Objeto con parámetros
   * @returns HttpParams
   */
  private buildParams(params?: any): HttpParams {
    let httpParams = new HttpParams();
    
    if (params) {
      Object.keys(params).forEach(key => {
        if (params[key] !== null && params[key] !== undefined && params[key] !== '') {
          httpParams = httpParams.set(key, params[key].toString());
        }
      });
    }
    
    return httpParams;
  }

  /**
   * Maneja errores de las peticiones HTTP
   * @param error - Error de la petición
   * @returns Observable con error
   */
  private handleError<T>(error: HttpErrorResponse): Observable<ApiResponse<T>> {
    let errorMessage = 'Error de conexión con el servidor';
    
    if (error.error instanceof ErrorEvent) {
      // Error del lado del cliente
      errorMessage = `Error: ${error.error.message}`;
    } else {
      // Error del lado del servidor
      switch (error.status) {
        case 0:
          errorMessage = 'No se pudo conectar con el servidor. Verifique su conexión.';
          break;
        case 400:
          errorMessage = error.error?.message || 'Solicitud incorrecta';
          break;
        case 401:
          errorMessage = 'No autorizado. Por favor inicie sesión nuevamente.';
          break;
        case 403:
          errorMessage = 'No tiene permisos para realizar esta acción';
          break;
        case 404:
          errorMessage = 'Recurso no encontrado';
          break;
        case 500:
          errorMessage = 'Error interno del servidor';
          break;
        default:
          errorMessage = error.error?.message || `Error ${error.status}: ${error.statusText}`;
      }
    }
    
    console.error('API Error:', error);
    
    return throwError(() => ({
      success: false,
      message: errorMessage,
      status: error.status
    }));
  }
}