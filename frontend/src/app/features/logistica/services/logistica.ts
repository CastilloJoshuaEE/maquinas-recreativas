/**
 * @fileoverview Servicio de Logística
 * @description Maneja todas las operaciones relacionadas con máquinas, comercios y distribución
 * @service LogisticaService
 */

import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
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
      map(response => response.success && response['maquinas'] ? response['maquinas'] : [])
    );
  }

  getMaquinasPorEtapa(etapa: string): Observable<Maquina[]> {
    return this.apiService.get<{ maquinas: Maquina[] }>(API_ENDPOINTS.MAQUINA_BY_ETAPA(etapa)).pipe(
      map(response => response.success && response['maquinas'] ? response['maquinas'] : [])
    );
  }

  getMaquinasDistribucion(): Observable<Maquina[]> {
    return this.apiService.get<{ maquinas: Maquina[] }>(API_ENDPOINTS.MAQUINA_BY_ETAPA('Distribucion')).pipe(
      map(response => response.success && response['maquinas'] ? response['maquinas'] : [])
    );
  }

  getMaquinasOperativas(): Observable<Maquina[]> {
    return this.apiService.get<{ maquinas: Maquina[] }>(API_ENDPOINTS.MAQUINA_BY_ESTADO('Operativa')).pipe(
      map(response => response.success && response['maquinas'] ? response['maquinas'] : [])
    );
  }

  getMaquinasRetiradas(): Observable<Maquina[]> {
    return this.apiService.get<{ maquinas: Maquina[] }>(API_ENDPOINTS.MAQUINA_BY_ESTADO('Retirada')).pipe(
      map(response => response.success && response['maquinas'] ? response['maquinas'] : [])
    );
  }

  ponerMaquinaOperativa(idMaquina: string): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.MAQUINA_OPERATIVA, { idMaquina }).pipe(
      map(response => response.success)
    );
  }

  solicitarMantenimiento(data: { idMaquina: string; mensaje: string; idLogistica: string }): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.MAQUINA_MANTENIMIENTO, data).pipe(
      map(response => response.success)
    );
  }

  registrarComercio(comercio: Partial<Comercio>): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.COMERCIO_REGISTER, comercio).pipe(
      map(response => response.success)
    );
  }

  registrarMaquina(maquina: any): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.MAQUINA_REGISTER, maquina).pipe(
      map(response => response.success)
    );
  }

  generarPlaca(idUsuario: string): Observable<{ id_componente: string; placa: string } | null> {
    return this.apiService.post(API_ENDPOINTS.MAQUINA_GENERAR_PLACA, { id: idUsuario }).pipe(
      map(response => response.success ? { 
        id_componente: response['id_componente'], 
        placa: response['placa'] 
      } : null)
    );
  }

  asignarCarcasa(idComponente: string, idUsuario: string): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.COMPONENTES_ASIGNAR_CARCASA, { ID_Componente: idComponente, id: idUsuario }).pipe(
      map(response => response.success)
    );
  }

  getComercios(): Observable<Comercio[]> {
    return this.apiService.get<{ comercios: Comercio[] }>(API_ENDPOINTS.COMERCIOS).pipe(
      map(response => response.success && response['comercios'] ? response['comercios'] : [])
    );
  }

  getTecnicosPorEspecialidad(especialidad: string): Observable<User[]> {
    return this.apiService.get<{ tecnicos: User[] }>(`/usuario/tecnicos/${especialidad}`).pipe(
      map(response => response.success && response['tecnicos'] ? response['tecnicos'] : [])
    );
  }

  getComponentesDisponibles(tipo?: string): Observable<any[]> {
    const params = tipo ? { tipo } : {};
    // Usar URL directa o agregar a API_ENDPOINTS
    return this.apiService.get<{ componentes: any[] }>(`/componentes/disponibles`, params).pipe(
      map(response => response.success && response['componentes'] ? response['componentes'] : [])
    );
  }

  getInformesDistribucion(params?: any): Observable<any[]> {
    return this.apiService.get<{ informes: any[] }>(API_ENDPOINTS.DISTRIBUCION_INFORMES, params).pipe(
      map(response => response.success && response['informes'] ? response['informes'] : [])
    );
  }

  registrarMontaje(data: { idMaquina: string; idEnsamblador: string }): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.MAQUINA_MONTAR, data).pipe(
      map(response => response.success)
    );
  }
}