/**
 * @fileoverview Servicio de Logística
 * @description Maneja todas las operaciones relacionadas con máquinas, comercios y distribución
 * @service LogisticaService
 */

import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
import { ApiService } from '@core/services/api.service';
import { API_ENDPOINTS } from '@core/constants/app.constants';
import { Maquina, MaquinaActionData } from '@core/models/maquina.model';
import { Comercio } from '@core/models/recaudacion.model';
import { User } from '@core/models/user.model';

@Injectable({
  providedIn: 'root'
})
export class LogisticaService {
  private apiService = inject(ApiService);

  /**
   * Obtiene todas las máquinas por estado
   * @param estado - Estado de la máquina
   * @returns Observable con lista de máquinas
   */
  getMaquinasPorEstado(estado: string): Observable<Maquina[]> {
    return this.apiService.get<{ maquinas: Maquina[] }>(API_ENDPOINTS.MAQUINA_BY_ESTADO(estado)).pipe(
      map(response => response.success && response.maquinas ? response.maquinas : [])
    );
  }

  /**
   * Obtiene todas las máquinas por etapa
   * @param etapa - Etapa de la máquina
   * @returns Observable con lista de máquinas
   */
  getMaquinasPorEtapa(etapa: string): Observable<Maquina[]> {
    return this.apiService.get<{ maquinas: Maquina[] }>(API_ENDPOINTS.MAQUINA_BY_ETAPA(etapa)).pipe(
      map(response => response.success && response.maquinas ? response.maquinas : [])
    );
  }

  /**
   * Obtiene todas las máquinas en distribución
   * @returns Observable con lista de máquinas en distribución
   */
  getMaquinasDistribucion(): Observable<Maquina[]> {
    return this.apiService.get<{ maquinas: Maquina[] }>(API_ENDPOINTS.MAQUINA_BY_ETAPA('Distribucion')).pipe(
      map(response => response.success && response.maquinas ? response.maquinas : [])
    );
  }

  /**
   * Obtiene todas las máquinas operativas
   * @returns Observable con lista de máquinas operativas
   */
  getMaquinasOperativas(): Observable<Maquina[]> {
    return this.apiService.get<{ maquinas: Maquina[] }>(API_ENDPOINTS.MAQUINA_BY_ESTADO('Operativa')).pipe(
      map(response => response.success && response.maquinas ? response.maquinas : [])
    );
  }

  /**
   * Obtiene todas las máquinas retiradas
   * @returns Observable con lista de máquinas retiradas
   */
  getMaquinasRetiradas(): Observable<Maquina[]> {
    return this.apiService.get<{ maquinas: Maquina[] }>(API_ENDPOINTS.MAQUINA_BY_ESTADO('Retirada')).pipe(
      map(response => response.success && response.maquinas ? response.maquinas : [])
    );
  }

  /**
   * Pone una máquina como operativa
   * @param idMaquina - ID de la máquina
   * @returns Observable con resultado
   */
  ponerMaquinaOperativa(idMaquina: string): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.MAQUINA_OPERATIVA, { idMaquina }).pipe(
      map(response => response.success)
    );
  }

  /**
   * Solicita mantenimiento para una máquina
   * @param data - Datos de la solicitud de mantenimiento
   * @returns Observable con resultado
   */
  solicitarMantenimiento(data: { idMaquina: string; mensaje: string; idLogistica: string }): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.MAQUINA_MANTENIMIENTO, data).pipe(
      map(response => response.success)
    );
  }

  /**
   * Registra un nuevo comercio
   * @param comercio - Datos del comercio
   * @returns Observable con resultado
   */
  registrarComercio(comercio: Partial<Comercio>): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.COMERCIO_REGISTER, comercio).pipe(
      map(response => response.success)
    );
  }

  /**
   * Registra una nueva máquina
   * @param maquina - Datos de la máquina
   * @returns Observable con resultado
   */
  registrarMaquina(maquina: any): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.MAQUINA_REGISTER, maquina).pipe(
      map(response => response.success)
    );
  }

  /**
   * Genera una placa para una nueva máquina
   * @param idUsuario - ID del usuario logística
   * @returns Observable con datos de la placa generada
   */
  generarPlaca(idUsuario: string): Observable<{ id_componente: string; placa: string } | null> {
    return this.apiService.post(API_ENDPOINTS.MAQUINA_GENERAR_PLACA, { ID_Usuario: idUsuario }).pipe(
      map(response => response.success ? { id_componente: response.id_componente, placa: response.placa } : null)
    );
  }

  /**
   * Asigna una carcasa a una máquina
   * @param idComponente - ID del componente carcasa
   * @param idUsuario - ID del usuario logística
   * @returns Observable con resultado
   */
  asignarCarcasa(idComponente: string, idUsuario: string): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.COMPONENTES_ASIGNAR_CARCASA, { ID_Componente: idComponente, ID_Usuario: idUsuario }).pipe(
      map(response => response.success)
    );
  }

  /**
   * Obtiene todos los comercios
   * @returns Observable con lista de comercios
   */
  getComercios(): Observable<Comercio[]> {
    return this.apiService.get<{ comercios: Comercio[] }>(API_ENDPOINTS.COMERCIOS).pipe(
      map(response => response.success && response.comercios ? response.comercios : [])
    );
  }

  /**
   * Obtiene técnicos por especialidad
   * @param especialidad - Especialidad del técnico
   * @returns Observable con lista de técnicos
   */
  getTecnicosPorEspecialidad(especialidad: string): Observable<User[]> {
    return this.apiService.get<{ tecnicos: User[] }>(`/usuario/tecnicos/${especialidad}`).pipe(
      map(response => response.success && response.tecnicos ? response.tecnicos : [])
    );
  }

  /**
   * Obtiene componentes disponibles por tipo
   * @param tipo - Tipo de componente
   * @returns Observable con lista de componentes
   */
  getComponentesDisponibles(tipo?: string): Observable<any[]> {
    const params = tipo ? { tipo } : {};
    return this.apiService.get<{ componentes: any[] }>(API_ENDPOINTS.COMPONENTES_DISPONIBLES, params).pipe(
      map(response => response.success && response.componentes ? response.componentes : [])
    );
  }

  /**
   * Obtiene informes de distribución
   * @param params - Parámetros de filtrado
   * @returns Observable con lista de informes
   */
  getInformesDistribucion(params?: any): Observable<any[]> {
    return this.apiService.get<{ informes: any[] }>(API_ENDPOINTS.DISTRIBUCION_INFORMES, params).pipe(
      map(response => response.success && response.informes ? response.informes : [])
    );
  }

  /**
   * Registra montaje de máquina
   * @param data - Datos del montaje
   * @returns Observable con resultado
   */
  registrarMontaje(data: { idMaquina: string; idEnsamblador: string }): Observable<boolean> {
    return this.apiService.post(API_ENDPOINTS.MAQUINA_MONTAR, data).pipe(
      map(response => response.success)
    );
  }
}