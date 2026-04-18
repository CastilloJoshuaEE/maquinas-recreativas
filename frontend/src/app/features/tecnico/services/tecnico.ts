/**
 * @fileoverview Servicio de Técnico
 * @description Maneja todas las operaciones relacionadas con técnicos y máquinas
 * @service TecnicoService
 */

import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
import { ApiService } from '@core/services/api';
import { API_ENDPOINTS } from '@core/constants/app.constants';
import { Maquina, MaquinaActionData } from '@core/models/maquina.model';
import { Componente, UsarComponenteData, LiberarComponenteData } from '@core/models/componente.model';
import {  of } from 'rxjs';
import { catchError } from 'rxjs/operators';
@Injectable({ providedIn: 'root' })
export class TecnicoService {
  private apiService = inject(ApiService);

getMaquinasEnsamblador(idTecnico: string): Observable<Maquina[]> {
    return this.apiService.get<{ maquinas: any[] }>(API_ENDPOINTS.MAQUINA_BY_ENSAMBLADOR(idTecnico)).pipe(
        map(response => {
            if (!response.success || !response['maquinas']) return [];
            
            // Normalizar los datos para que el frontend los entienda
            return response['maquinas'].map((m: any) => ({
                ID_Maquina: m.ID_Maquina,
                Nombre_Maquina: m.Nombre_Maquina,
                tipo: m.Tipo || m.tipo,
                estado: m.Estado || m.estado,
                etapa: m.Etapa || m.etapa,
                ID_Comercio: m.ID_Comercio,
                NombreComercio: m.NombreComercio,
                DireccionComercio: m.DireccionComercio,
                ID_Tecnico_Ensamblador: m.ID_Tecnico_Ensamblador,
                ID_Tecnico_Comprobador: m.ID_Tecnico_Comprobador,
                Fecha_Registro: m.Fecha_Registro
            }));
        })
    );
}
  getMaquinasComprobador(idTecnico: string): Observable<Maquina[]> {
    return this.apiService.get<{ maquinas: Maquina[] }>(API_ENDPOINTS.MAQUINA_BY_COMPROBADOR(idTecnico)).pipe(
      map(response => response.success && response['maquinas'] ? response['maquinas'] : [])
    );
  }
getMaquinasMantenimiento(idTecnico: string): Observable<Maquina[]> {
    return this.apiService.get<{ maquinas: any[] }>(API_ENDPOINTS.MAQUINA_BY_MANTENIMIENTO(idTecnico)).pipe(
        map(response => {
            console.log('Respuesta máquinas mantenimiento (raw):', response);
            
            // Verificar estructura de la respuesta
            if (!response) {
                console.log('Respuesta es null o undefined');
                return [];
            }
            
            if (!response.success) {
                console.log('Respuesta success es false');
                return [];
            }
            
            // Obtener las máquinas - puede estar en response.maquinas o response['maquinas']
            let maquinasData = response['maquinas'];
            
            if (!maquinasData) {
                console.log('No hay propiedad maquinas en la respuesta');
                return [];
            }
            
            // Verificar si es array
            if (!Array.isArray(maquinasData)) {
                console.log('maquinasData no es un array, es:', typeof maquinasData);
                // Si es un objeto, convertirlo a array
                if (typeof maquinasData === 'object' && maquinasData !== null) {
                    maquinasData = [maquinasData];
                } else {
                    return [];
                }
            }
            
            console.log('Máquinas a procesar:', maquinasData.length);
            
            // Normalizar datos
            return maquinasData.map((m: any) => ({
                ID_Maquina: m.ID_Maquina,
                Nombre_Maquina: m.Nombre_Maquina,
                tipo: m.Tipo || m.tipo,
                estado: m.Estado || m.estado,
                etapa: m.Etapa || m.etapa,
                ID_Comercio: m.ID_Comercio,
                NombreComercio: m.NombreComercio,
                DireccionComercio: m.DireccionComercio,
                ID_Tecnico_Ensamblador: m.ID_Tecnico_Ensamblador,
                ID_Tecnico_Comprobador: m.ID_Tecnico_Comprobador,
                Fecha_Registro: m.Fecha_Registro
            }));
        }),
        catchError(error => {
            console.error('Error en getMaquinasMantenimiento:', error);
            return of([]);
        })
    );
}

  mandarAComprobacion(data: MaquinaActionData): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.MAQUINA_COMPROBACION, data).pipe(map(response => response.success));
  }

  mandarAReensamblar(data: MaquinaActionData): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.MAQUINA_REENSAMBLAR, data).pipe(map(response => response.success));
  }

  mandarADistribucion(data: MaquinaActionData): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.MAQUINA_DISTRIBUCION, data).pipe(map(response => response.success));
  }

  finalizarMantenimiento(data: { idMaquina: string; idRemitente: string; exito: boolean; mensaje: string }): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.MAQUINA_FINALIZAR_MANTENIMIENTO, data).pipe(map(response => response.success));
  }

  getComponentesDisponibles(tipo?: string, page: number = 1, limit: number = 10): Observable<{ componentes: Componente[]; total: number }> {
    const params: any = { page, limit };
    if (tipo) params.tipo = tipo;
    return this.apiService.get<{ componentes: Componente[]; total: number }>('/componentes/disponibles', params).pipe(
      map(response => ({ 
        componentes: response.success && response['componentes'] ? response['componentes'] : [], 
        total: response['total'] || 0 
      }))
    );
  }

  usarComponente(data: UsarComponenteData): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.COMPONENTES_USAR, data).pipe(map(response => response.success));
  }

  liberarComponente(data: LiberarComponenteData): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.COMPONENTES_LIBERAR, data).pipe(map(response => response.success));
  }

  getComponentesEnUso(idUsuario: string, idMaquina?: string): Observable<Componente[]> {
    let url = API_ENDPOINTS.COMPONENTES_EN_USO(idUsuario);
    if (idMaquina) url += `?id_maquina=${idMaquina}`;
    return this.apiService.get<{ componentes: Componente[] }>(url).pipe(
      map(response => response.success && response['componentes'] ? response['componentes'] : [])
    );
  }

getHistorialMaquina(idMaquina: string, pagina: number = 1, porPagina: number = 20): Observable<{ historial: any[]; paginacion: any }> {
    return this.apiService.get<any>(API_ENDPOINTS.HISTORIAL_MAQUINA(idMaquina), { pagina, por_pagina: porPagina }).pipe(
        map(response => {
            console.log('Respuesta historial:', response);
            // Verificar que response no sea null
            if (!response) {
                return { historial: [], paginacion: { pagina_actual: 1, total_paginas: 1, total: 0 } };
            }
            // Si la respuesta tiene la estructura esperada
            if (response.success && response['historial']) {
                return { 
                    historial: response['historial'], 
                    paginacion: response['paginacion'] || { pagina_actual: 1, total_paginas: 1, total: 0 } 
                };
            }
            // Si la respuesta es directamente un array
            if (Array.isArray(response)) {
                return { historial: response, paginacion: { pagina_actual: 1, total_paginas: 1, total: response.length } };
            }
            return { historial: [], paginacion: { pagina_actual: 1, total_paginas: 1, total: 0 } };
        }),
        catchError(error => {
            console.error('Error en getHistorialMaquina:', error);
            return of({ historial: [], paginacion: { pagina_actual: 1, total_paginas: 1, total: 0 } });
        })
    );
}
}