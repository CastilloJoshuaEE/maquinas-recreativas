/**
 * @fileoverview Servicio de Contabilidad
 * @description Maneja todas las operaciones relacionadas con recaudaciones e informes
 * @service ContabilidadService
 */

import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
import { ApiService } from '@core/services/api';
import { API_ENDPOINTS } from '@core/constants/app.constants';
import { Recaudacion, RegisterRecaudacionData, ResumenRecaudacion, Comercio, InformeRecaudacion } from '@core/models/recaudacion.model';
import { Maquina } from '@core/models/maquina.model';
import { catchError, of } from 'rxjs';
@Injectable({
  providedIn: 'root'
})
export class ContabilidadService {
  private apiService = inject(ApiService);

  /**
   * Registra una nueva recaudación
   * @param data - Datos de la recaudación
   * @returns Observable con resultado
   */
registrarRecaudacion(data: any): Observable<{ success: boolean; message?: string; ID_Recaudacion?: string }> {
    console.log('Enviando a API:', data);
    return this.apiService.post(API_ENDPOINTS.RECAUDACION_REGISTRAR, data).pipe(
        map(response => {
            console.log('Respuesta API:', response);
            return {
                success: response.success,
                message: response.message,
                ID_Recaudacion: response['idRecaudacion']
            };
        }),
        catchError(error => {
            console.error('Error en registrarRecaudacion:', error);
            return of({ success: false, message: error.message });
        })
    );
}
  /**
   * Obtiene todas las recaudaciones con filtros
   * @param params - Parámetros de filtrado
   * @returns Observable con lista de recaudaciones
   */
  getRecaudaciones(params?: any): Observable<Recaudacion[]> {
    return this.apiService.get<{ recaudaciones: Recaudacion[] }>(API_ENDPOINTS.RECAUDACIONES, params).pipe(
      map(response => response.success && response['recaudaciones'] ? response['recaudaciones'] : [])
    );
  }

/**
 * Obtiene una recaudación por ID
 * @param id - ID de la recaudación
 * @returns Observable con la recaudación
 */
getRecaudacionById(id: string): Observable<Recaudacion | null> {
    return this.apiService.get<{ recaudacion: any }>(API_ENDPOINTS.RECAUDACION_BY_ID(id)).pipe(
        map(response => {
            console.log('Respuesta getRecaudacionById:', response);
            
            if (!response || !response.success) {
                return null;
            }
            
            const data = response['recaudacion'] || response;
            
            if (!data) {
                return null;
            }
            
            // Mapear correctamente las propiedades
            return {
                ID_Recaudacion: data.id || data.ID_Recaudacion,
                ID_Maquina: data.id_maquina || data.ID_Maquina,
                ID_Comercio: data.id_comercio || data.ID_Comercio,
                Nombre_Comercio: data.nombre_comercio || data.Nombre_Comercio,
                Nombre_Maquina: data.nombre_maquina || data.Nombre_Maquina,
                Tipo_Comercio: data.tipo_comercio || data.Tipo_Comercio,
                Monto_Total: data.monto_total || data.Monto_Total,
                Monto_Empresa: data.monto_empresa || data.Monto_Empresa,
                Monto_Comercio: data.monto_comercio || data.Monto_Comercio,
                Porcentaje_Comercio: data.porcentaje_comercio || data.Porcentaje_Comercio,
                fecha: data.fecha,
                detalle: data.detalle || ''
            } as Recaudacion;
        }),
        catchError(error => {
            console.error('Error en getRecaudacionById:', error);
            return of(null);
        })
    );
}

  /**
   * Actualiza una recaudación
   * @param data - Datos actualizados
   * @returns Observable con resultado
   */
  actualizarRecaudacion(data: any): Observable<{ success: boolean; message?: string }> {
    return this.apiService.put(API_ENDPOINTS.RECAUDACION_ACTUALIZAR, data).pipe(
      map(response => ({ success: response.success, message: response.message }))
    );
  }

  /**
   * Elimina una recaudación
   * @param id - ID de la recaudación
   * @returns Observable con resultado
   */
  eliminarRecaudacion(id: string): Observable<{ success: boolean; message?: string }> {
    return this.apiService.delete(API_ENDPOINTS.RECAUDACION_ELIMINAR(id)).pipe(
      map(response => ({ success: response.success, message: response.message }))
    );
  }
  /**
   * Obtiene resumen de recaudaciones
   * @returns Observable con resumen
   */
  getResumenRecaudaciones(): Observable<ResumenRecaudacion[]> {
    return this.apiService.get<{ resumen: ResumenRecaudacion[] }>(API_ENDPOINTS.RECAUDACION_RESUMEN).pipe(
      map(response => response.success && response['resumen'] ? response['resumen'] : [])
    );
  }
  /**
   * Obtiene máquinas disponibles para recaudación
   * @returns Observable con lista de máquinas
   */
  getMaquinasRecaudacion(): Observable<Maquina[]> {
    return this.apiService.get<{ maquinas: Maquina[] }>(API_ENDPOINTS.MAQUINAS_RECAUDACION).pipe(
      map(response => response.success && response['maquinas'] ? response['maquinas'] : [])
    
    );
  }
  /**
   * Obtiene máquinas operativas por comercio
   * @param idComercio - ID del comercio
   * @returns Observable con lista de máquinas
   */
getMaquinasOperativasPorComercio(idComercio: string): Observable<Maquina[]> {
    return this.apiService.get<{ maquinas: any[] }>(
        '/contabilidad/maquinas-operativas-por-comercio', 
        { idComercio: idComercio }
    ).pipe(
        map(response => {
            console.log('Respuesta máquinas por comercio:', response);
            // Verificar que response existe y tiene la estructura correcta
            if (!response) {
                console.log('Respuesta es null');
                return [];
            }
            // Si la respuesta tiene success y maquinas
            if (response.success && response['maquinas']) {
                const maquinasData = response['maquinas'];
                // Verificar si es array
                if (Array.isArray(maquinasData)) {
                    return maquinasData;
                }
                // Si es un objeto, convertirlo a array
                if (typeof maquinasData === 'object' && maquinasData !== null) {
                    return [maquinasData];
                }
                return [];
            }
            // Si la respuesta es directamente un array
            if (Array.isArray(response)) {
                return response;
            }
            return [];
        }),
        catchError(error => {
            console.error('Error en getMaquinasOperativasPorComercio:', error);
            return of([]);
        })
    );
}

/**
 * Obtiene todos los comercios
 * @returns Observable con lista de comercios
 */
getComercios(): Observable<Comercio[]> {
    console.log('Llamando a getComercios...');
    return this.apiService.get<{ comercios: Comercio[] }>(API_ENDPOINTS.COMERCIOS).pipe(
        map(response => {
            console.log('Respuesta completa de getComercios:', response);
            
            // Verificar si la respuesta es null o undefined
            if (!response) {
                console.log('Respuesta es null o undefined');
                return [];
            }
            
            // Si la respuesta tiene la estructura con success y comercios
            if (response.success && response['comercios']) {
                const comerciosData = response['comercios'];
                console.log('Comercios encontrados:', comerciosData.length);
                return comerciosData;
            }
            
            // Si la respuesta es directamente un array
            if (Array.isArray(response)) {
                console.log('Respuesta es un array directo:', response.length);
                return response;
            }
            
            // Si la respuesta tiene data
            if (response.data && Array.isArray(response.data)) {
                console.log('Respuesta tiene data:', response.data.length);
                return response.data;
            }
            
            console.log('Formato de respuesta no reconocido, devolviendo array vacío');
            return [];
        }),
        catchError(error => {
            console.error('Error en getComercios:', error);
            return of([]);
        })
    );
}
  /**
   * Guarda un informe de recaudación
   * @param data - Datos del informe
   * @returns Observable con resultado
   */
  guardarInforme(data: any): Observable<{ success: boolean; message?: string }> {
    return this.apiService.post(API_ENDPOINTS.INFORME_GUARDAR, data).pipe(
      map(response => ({ success: response.success, message: response.message }))
    );
  }

  /**
   * Obtiene un informe por ID de recaudación
   * @param idRecaudacion - ID de la recaudación
   * @returns Observable con el informe
   */
  getInformeByRecaudacion(idRecaudacion: string): Observable<InformeRecaudacion | null> {
    return this.apiService.get<{ informe: InformeRecaudacion }>(API_ENDPOINTS.INFORME_BY_RECAUDACION(idRecaudacion)).pipe(
      map(response => response.success && response['informe'] ? response['informe'] : null)
    );
  }

  /**
   * Obtiene informes de distribución
   * @param params - Parámetros de filtrado
   * @returns Observable con lista de informes
   */

  getInformesDistribucion(params?: any): Observable<any[]> {
    return this.apiService.get<{ informes: any[] }>(API_ENDPOINTS.DISTRIBUCION_INFORMES, params).pipe(
      map(response => response.success && response['informes'] ? response['informes'] : [])
    );
  }
}