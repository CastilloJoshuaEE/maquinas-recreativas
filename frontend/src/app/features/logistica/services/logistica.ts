/**
 * @fileoverview Servicio de Logística
 * @description Maneja todas las operaciones relacionadas con máquinas, comercios y distribución
 * @service LogisticaService
 */

import { Injectable, inject } from '@angular/core';
import { Observable, catchError, map, of } from 'rxjs';
import { ApiService } from '@core/services/api';
import { API_ENDPOINTS } from '@core/constants/app.constants';
import { Maquina } from '@core/models/maquina.model';
import { Comercio } from '@core/models/recaudacion.model';
import { User } from '@core/models/user.model';

@Injectable({
  providedIn: 'root'
})
export class LogisticaService {
  private apiService = inject(ApiService);

  getMaquinasPorEstado(estado: string): Observable<Maquina[]> {
    return this.apiService.get<{ maquinas: Maquina[] }>(API_ENDPOINTS.MAQUINA_BY_ESTADO(estado)).pipe(
      map(response => response && response.success && response['maquinas'] ? response['maquinas'] : []),
      catchError(() => of([]))
    );
  }

getMaquinasPorEtapa(etapa: string): Observable<Maquina[]> {
    return this.apiService.get<{ maquinas: any[] }>(API_ENDPOINTS.MAQUINA_BY_ETAPA(etapa)).pipe(
        map(response => {
            console.log(`Respuesta máquinas por etapa ${etapa}:`, response);
            if (!response || !response.success || !response['maquinas']) return [];
            
            // Normalizar datos
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
        }),
        catchError(error => {
            console.error(`Error en getMaquinasPorEtapa(${etapa}):`, error);
            return of([]);
        })
    );
}
getMaquinasDistribucion(): Observable<Maquina[]> {
    return this.getMaquinasPorEtapa('Distribucion');
}

getMaquinasOperativas(): Observable<Maquina[]> {
    return this.apiService.get<{ maquinas: any[] }>(API_ENDPOINTS.MAQUINA_BY_ESTADO('Operativa')).pipe(
        map(response => {
            console.log('Respuesta máquinas operativas:', response);
            if (!response || !response.success || !response['maquinas']) return [];
            
            // Normalizar datos
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
        }),
        catchError(error => {
            console.error('Error en getMaquinasOperativas:', error);
            return of([]);
        })
    );
}

  getMaquinasRetiradas(): Observable<Maquina[]> {
    return this.apiService.get<{ maquinas: Maquina[] }>(API_ENDPOINTS.MAQUINA_BY_ESTADO('Retirada')).pipe(
      map(response => response && response.success && response['maquinas'] ? response['maquinas'] : []),
      catchError(() => of([]))
    );
  }

  ponerMaquinaOperativa(idMaquina: string): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.MAQUINA_OPERATIVA, { idMaquina }).pipe(
      map(response => !!(response && response.success)),
      catchError(() => of(false))
    );
  }

  solicitarMantenimiento(data: { idMaquina: string; mensaje: string; idLogistica: string }): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.MAQUINA_MANTENIMIENTO, data).pipe(
      map(response => !!(response && response.success)),
      catchError(() => of(false))
    );
  }

  registrarComercio(comercio: Partial<Comercio>): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.COMERCIO_REGISTER, comercio).pipe(
      map(response => !!(response && response.success)),
      catchError(() => of(false))
    );
  }

  actualizarComercio(idComercio: string, comercio: Partial<Comercio>): Observable<boolean> {
    return this.apiService.put(API_ENDPOINTS.COMERCIO_UPDATE(idComercio), comercio).pipe(
      map(response => !!(response && response.success)),
      catchError(error => {
        console.error('Error actualizando comercio:', error);
        return of(false);
      })
    );
  }

  eliminarComercio(idComercio: string): Observable<boolean> {
    return this.apiService.delete(API_ENDPOINTS.COMERCIO_DELETE(idComercio)).pipe(
      map(response => !!(response && response.success)),
      catchError(error => {
        console.error('Error eliminando comercio:', error);
        return of(false);
      })
    );
  }

  registrarMaquina(maquina: any): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.MAQUINA_REGISTER, maquina).pipe(
      map(response => !!(response && response.success)),
      catchError(() => of(false))
    );
  }

  /** FIX: null-guard en map para evitar "Cannot read properties of null" */
  generarPlaca(idUsuario: string): Observable<{ id_componente: string; placa: string } | null> {
    return this.apiService.post(API_ENDPOINTS.MAQUINA_GENERAR_PLACA, { idUsuario }).pipe(
      map(response => {
        console.log('Respuesta generar placa:', response);
        if (!response) return null;
        return response.success
          ? { id_componente: response['idComponente'], placa: response['placa'] }
          : null;
      }),
      catchError(error => {
        console.error('Error generando placa:', error);
        return of(null);
      })
    );
  }

asignarCarcasa(idComponente: string, idUsuario: string): Observable<boolean> {
    const body = { 
        idComponente: idComponente,  //  Cambiado de ID_Componente a idComponente
        id: idUsuario
    };
    console.log('Enviando asignar carcasa:', body);
    
    return this.apiService.post(API_ENDPOINTS.COMPONENTES_ASIGNAR_CARCASA, body).pipe(
        map(response => {
            console.log('Respuesta asignar carcasa:', response);
            return !!(response && response.success);
        }),
        catchError(error => {
            console.error('Error asignando carcasa:', error);
            return of(false);
        })
    );
}

getComercios(): Observable<any[]> {
    return this.apiService.get<{ comercios: any[] }>(API_ENDPOINTS.COMERCIOS).pipe(
        map(response => {
            console.log('Respuesta getComercios:', response);
            let comercios: any[] = [];
            if (response && response.success && response['comercios']) {
                comercios = response['comercios'];
            } else if (Array.isArray(response)) {
                comercios = response;
            }
            // Normalizar cada comercio
            return comercios.map(c => ({
                ID_Comercio: c.ID_Comercio,
                nombre: c.nombre || c.Nombre || 'Sin nombre',
                tipo: c.tipo || c.Tipo || 'Minorista',
                direccion: c.direccion || c.Direccion || '',
                telefono: c.telefono || c.Telefono || ''
            }));
        }),
        catchError(error => {
            console.error('Error en getComercios:', error);
            return of([]);
        })
    );
}
getTecnicosPorEspecialidad(especialidad: string): Observable<User[]> {
    return this.apiService.get<{ tecnicos: User[] }>(`/usuario/tecnicos/${especialidad}`).pipe(
        map(response => {
            console.log(`Respuesta técnicos ${especialidad}:`, response);
            // Si response es null o undefined, retornar array vacío
            if (!response) {
                return [];
            }
            // Si response tiene success false, retornar array vacío
            if (response.success === false) {
                return [];
            }
            // Si tiene la propiedad tecnicos y es array
            if (response['tecnicos'] && Array.isArray(response['tecnicos'])) {
                return response['tecnicos'];
            }
            // Si la respuesta es directamente un array
            if (Array.isArray(response)) {
                return response;
            }
            return [];
        }),
        catchError(error => {
            console.error(`Error cargando técnicos ${especialidad}:`, error);
            return of([]);
        })
    );
}

  getComponentesDisponibles(tipo?: string): Observable<any[]> {
    const params = tipo ? { tipo } : {};
    return this.apiService.get<{ componentes: any[] }>('/componentes/disponibles', params).pipe(
      map(response => {
        console.log('Respuesta componentes disponibles:', response);
        if (response && response.success) {
          if (response['componentes']) return response['componentes'];
          if (Array.isArray(response)) return response;
        }
        return [];
      }),
      catchError(error => {
        console.error('Error en getComponentesDisponibles:', error);
        return of([]);
      })
    );
  }
getInformesDistribucion(params?: any): Observable<any[]> {
    // Limpiar parámetros vacíos
    const cleanParams: any = {};
    if (params) {
        Object.keys(params).forEach(key => {
            if (params[key] && params[key] !== '') {
                cleanParams[key] = params[key];
            }
        });
    }
    console.log('Enviando parámetros a API:', cleanParams);
    
    return this.apiService.get<{ informes: any[] }>(API_ENDPOINTS.DISTRIBUCION_INFORMES, cleanParams).pipe(
        map(response => {
            console.log('Respuesta informes distribución:', response);
            if (response && response.success && response['informes']) {
                return response['informes'];
            }
            if (Array.isArray(response)) {
                return response;
            }
            return [];
        }),
        catchError(error => {
            console.error('Error en getInformesDistribucion:', error);
            return of([]);
        })
    );
}

  registrarMontaje(data: { idMaquina: string; idEnsamblador: string }): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.MAQUINA_MONTAR, data).pipe(
      map(response => !!(response && response.success)),
      catchError(() => of(false))
    );
  }
}