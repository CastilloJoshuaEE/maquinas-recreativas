/**
 * @fileoverview Levantar Informe de Recaudación
 * @description Genera y guarda el informe completo de una recaudación
 * @component LevantarInformeComponent
 *
 * FIX: el backend devuelve claves en minúsculas (id_maquina, nombre_maquina, etc.)
 * y el comercio/técnicos no se cargaban porque se usaban claves PascalCase.
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header';
import { ContabilidadService } from '../../services/contabilidad';
import { Recaudacion, Comercio } from '@core/models/recaudacion.model';
import { Maquina } from '@core/models/maquina.model';
import { Componente } from '@core/models/componente.model';
import { User } from '@core/models/user.model';
import { AuthService } from '@core/services/auth';
import { ApiService } from '@core/services/api';
import { UserService } from '@app/core/services/user';

@Component({
  selector: 'app-levantar-informe',
  standalone: true,
  imports: [CommonModule, MatCardModule, MatButtonModule, MatIconModule, MatProgressSpinnerModule, AdminHeaderComponent],
  templateUrl: './levantar-informe.html',
  styleUrls: ['./levantar-informe.css']
})
export class LevantarInformeComponent implements OnInit {
  private route = inject(ActivatedRoute);
  private router = inject(Router);
  private contabilidadService = inject(ContabilidadService);
  private authService = inject(AuthService);
  private apiService = inject(ApiService);
  private snackBar = inject(MatSnackBar);
  private userService = inject(UserService);
  recaudacion: any = null;   // any para manejar claves minúsculas del backend
  maquina: any = null;
  comercio: any = null;
  componentes: Componente[] = [];
  tecnicos: { ensamblador: User | null; comprobador: User | null; mantenimiento: User | null } = {
    ensamblador: null, comprobador: null, mantenimiento: null
  };

  cargandoRecaudacion = true;
  cargandoMaquina     = true;
  cargandoComercio    = true;
  cargandoTecnicos    = true;
  cargandoComponentes = true;

  guardando = false;
  success   = false;
  error     = '';
  fechaEmision = new Date();

  get loading(): boolean {
    return this.cargandoRecaudacion || this.cargandoMaquina ||
           this.cargandoComercio   || this.cargandoTecnicos ||
           this.cargandoComponentes;
  }

  // ── Getters tolerantes a minúsculas/mayúsculas ────────────────────────────
  get idMaquina(): string {
    if (!this.recaudacion) return '';
    return this.recaudacion.ID_Maquina || this.recaudacion.id_maquina || '';
  }

  get idComercio(): string {
    if (this.maquina) {
      return this.maquina.ID_Comercio || this.maquina.id_comercio || '';
    }
    if (this.recaudacion) {
      return this.recaudacion.ID_Comercio || this.recaudacion.id_comercio || '';
    }
    return '';
  }

  get comercioNombre(): string  { return this.comercio?.Nombre || this.comercio?.nombre || '—'; }
  get comercioDireccion(): string { return this.comercio?.Direccion || this.comercio?.direccion || '—'; }
  get comercioTelefono(): string  { return this.comercio?.Telefono || this.comercio?.telefono || '—'; }
  get comercioTipo(): string      { return this.comercio?.Tipo || this.comercio?.tipo || '—'; }

  get maquinaNombre(): string {
    return this.maquina?.Nombre_Maquina || this.maquina?.nombre_maquina ||
           this.recaudacion?.Nombre_Maquina || this.recaudacion?.nombre_maquina || '—';
  }

  get montoTotal(): number    { return this.recaudacion?.Monto_Total    || this.recaudacion?.monto_total    || 0; }
  get montoEmpresa(): number  { return this.recaudacion?.Monto_Empresa  || this.recaudacion?.monto_empresa  || 0; }
  get montoComercio(): number { return this.recaudacion?.Monto_Comercio || this.recaudacion?.monto_comercio || 0; }
  get tipoComercio(): string  { return this.recaudacion?.Tipo_Comercio  || this.recaudacion?.tipo_comercio  || ''; }
  get porcentaje(): number    { return this.recaudacion?.Porcentaje_Comercio || this.recaudacion?.porcentaje_comercio || 0; }
  get fecha(): string         { return this.recaudacion?.fecha || ''; }
  get detalle(): string       { return this.recaudacion?.detalle || ''; }
  get idRecaudacion(): string {
    return this.recaudacion?.ID_Recaudacion || this.recaudacion?.id || '';
  }

  // ── Ciclo de vida ─────────────────────────────────────────────────────────

  private cargarDatos(): void {
    const idRec = this.route.snapshot.params['idRecaudacion'];
    if (!idRec) {
      this.error = 'ID de recaudación no válido';
      this.cargandoRecaudacion = false;
      return;
    }

    this.contabilidadService.getRecaudacionById(idRec).subscribe({
      next: (rec) => {
        if (!rec) {
          this.error = 'Recaudación no encontrada';
          this.cargandoRecaudacion = false;
          return;
        }
        this.recaudacion = rec;
        this.cargandoRecaudacion = false;

        const idMaq = (rec as any).ID_Maquina || (rec as any).id_maquina || '';
        if (idMaq) {
          this.cargarMaquinaCompleta(idMaq);
          this.cargarComponentes(idMaq);
        } else {
          this.cargandoMaquina = false;
          this.cargandoComercio = false;
          this.cargandoTecnicos = false;
          this.cargandoComponentes = false;
        }
      },
      error: (err) => {
        this.error = err.message || 'Error al cargar recaudación';
        this.cargandoRecaudacion = false;
      }
    });
  }

  /**
   * Obtiene la máquina por ID desde cualquier fuente disponible.
   * Prioriza: 1) listado de máquinas operativas, 2) listado de máquinas en recaudación, 3) objeto mínimo.
   */
  private cargarMaquinaCompleta(idMaquina: string): void {
    // 1. Intentar obtener la máquina desde el endpoint de máquinas operativas (sin filtro de etapa)
    this.apiService.get('/maquina/estado/Operativa').subscribe({
      next: (resp: any) => {
        const todas = resp?.maquinas || [];
        const encontrada = todas.find((m: any) => (m.ID_Maquina || m.id_maquina) === idMaquina);
        if (encontrada) {
          this.maquina = encontrada;
          this.cargandoMaquina = false;
          const idCom = encontrada.ID_Comercio || encontrada.id_comercio || '';
          if (idCom) this.cargarComercio(idCom);
          else this.cargandoComercio = false;
          this.cargarTecnicos(encontrada);
        } else {
          // 2. No se encontró en operativas, intentar en recaudación
          this.contabilidadService.getMaquinasRecaudacion().subscribe({
            next: (maquinas: any[]) => {
              const encontrada2 = maquinas.find(m => (m.ID_Maquina || m.id_maquina) === idMaquina);
              if (encontrada2) {
                this.maquina = encontrada2;
                this.cargandoMaquina = false;
                const idCom = encontrada2.ID_Comercio || encontrada2.id_comercio || '';
                if (idCom) this.cargarComercio(idCom);
                else this.cargandoComercio = false;
                this.cargarTecnicos(encontrada2);
              } else {
                // 3. Último recurso: crear objeto mínimo
                this.maquina = {
                  ID_Maquina: idMaquina,
                  Nombre_Maquina: (this.recaudacion as any)?.nombre_maquina || (this.recaudacion as any)?.Nombre_Maquina || 'Máquina'
                };
                this.cargandoMaquina = false;
                this.cargandoComercio = false;
                this.cargandoTecnicos = false;
              }
            },
            error: () => {
              this.maquina = { ID_Maquina: idMaquina, Nombre_Maquina: 'Máquina' };
              this.cargandoMaquina = false;
              this.cargandoComercio = false;
              this.cargandoTecnicos = false;
            }
          });
        }
      },
      error: () => {
        // Fallback: intentar en recaudación
        this.contabilidadService.getMaquinasRecaudacion().subscribe({
          next: (maquinas: any[]) => {
            const encontrada = maquinas.find(m => (m.ID_Maquina || m.id_maquina) === idMaquina);
            if (encontrada) {
              this.maquina = encontrada;
              this.cargandoMaquina = false;
              const idCom = encontrada.ID_Comercio || encontrada.id_comercio || '';
              if (idCom) this.cargarComercio(idCom);
              else this.cargandoComercio = false;
              this.cargarTecnicos(encontrada);
            } else {
              this.maquina = { ID_Maquina: idMaquina, Nombre_Maquina: 'Máquina' };
              this.cargandoMaquina = false;
              this.cargandoComercio = false;
              this.cargandoTecnicos = false;
            }
          },
          error: () => {
            this.maquina = { ID_Maquina: idMaquina, Nombre_Maquina: 'Máquina' };
            this.cargandoMaquina = false;
            this.cargandoComercio = false;
            this.cargandoTecnicos = false;
          }
        });
      }
    });
  }

  private cargarComercio(idComercio: string): void {
    if (!idComercio) {
      this.cargandoComercio = false;
      return;
    }
    this.contabilidadService.getComercios().subscribe({
      next: (comercios: any[]) => {
        this.comercio = comercios.find(c => (c.ID_Comercio || c.id_comercio) === idComercio) || null;
        this.cargandoComercio = false;
      },
      error: () => { this.cargandoComercio = false; }
    });
  }
private cargarTecnicos(maquina: any): void {
  const ids = {
    ensamblador: maquina.ID_Tecnico_Ensamblador || maquina.id_tecnico_ensamblador || null,
    comprobador: maquina.ID_Tecnico_Comprobador || maquina.id_tecnico_comprobador || null,
    mantenimiento: maquina.ID_Tecnico_Mantenimiento || maquina.id_tecnico_mantenimiento || null,
  };

  // Obtener listas completas de técnicos por especialidad
  Promise.all([
    this.userService.getTecnicosByEspecialidad('Ensamblador').toPromise(),
    this.userService.getTecnicosByEspecialidad('Comprobador').toPromise(),
    this.userService.getTecnicosByEspecialidad('Mantenimiento').toPromise()
  ]).then(([ensambladores, comprobadores, mantenedores]) => {
    this.tecnicos = {
      ensamblador: ensambladores?.find(t => t.id === ids.ensamblador) || null,
      comprobador: comprobadores?.find(t => t.id === ids.comprobador) || null,
      mantenimiento: mantenedores?.find(t => t.id === ids.mantenimiento) || null
    };
    this.cargandoTecnicos = false;
  }).catch(err => {
    console.error('Error cargando técnicos:', err);
    this.cargandoTecnicos = false;
  });
}
// En levantar-informe.ts - Modifica cargarComponentes
private cargarComponentes(idMaquina: string): void {
    this.cargandoComponentes = true;
    
    // Usar el endpoint de componentes por máquina
    this.apiService.get(`/maquina/componentes/${idMaquina}`).subscribe({
        next: (response: any) => {
            console.log('Respuesta componentes desde endpoint maquina/componentes:', response);
            
            let componentesData = [];
            if (response?.success && response?.componentes) {
                componentesData = response.componentes;
            }
            
            this.componentes = componentesData.map((comp: any) => ({
                ID_Componente: comp.ID_Componente || comp.id,
                nombre: comp.nombre,
                tipo: comp.tipo,
                precio: comp.precio || 0
            }));
            
            console.log('Componentes cargados:', this.componentes.length);
            this.cargandoComponentes = false;
        },
        error: (err) => {
            console.error('Error cargando componentes desde /maquina/componentes:', err);
            this.componentes = [];
            this.cargandoComponentes = false;
        }
    });
}

  ngOnInit(): void { this.cargarDatos(); }

  // ── Cálculos ──────────────────────────────────────────────────────────────
  get totalPagosTecnicos(): number {
    return (this.tecnicos.ensamblador  ? 400 : 0)
         + (this.tecnicos.comprobador  ? 400 : 0)
         + (this.tecnicos.mantenimiento ? 400 : 0);
  }

  get totalComponentes(): number {
    return this.componentes.reduce((s, c) => s + (c.precio || 0), 0);
  }

  // ── Acciones ──────────────────────────────────────────────────────────────
  imprimirInforme(): void { setTimeout(() => window.print(), 300); }
guardarInforme(): void {
  if (!this.recaudacion) return;
  this.guardando = true;
  
  // Obtener el usuario actual completo
  const currentUser = this.authService.getCurrentUser();
  
  // Intentar obtener CI de múltiples fuentes
  let ciUsuario = '';
  
  if (currentUser) {
    // Prioridad 1: CI directa
    ciUsuario = currentUser.ci || '';
    
    // Prioridad 2: Si la CI parece encriptada o es muy larga, obtenerla del backend
    if (!ciUsuario || ciUsuario.length > 20 || ciUsuario.includes('***')) {
      console.warn('CI no válida en currentUser, obteniendo del backend...');
      
      // Obtener el perfil completo del backend (que debería desencriptar la CI)
      this.userService.getProfile(currentUser.id || currentUser.id).subscribe({
        next: (user) => {
          if (user && user.ci) {
            this.guardarInformeConCI(user.ci);
          } else {
            // Fallback: usar ID de usuario como identificador
            this.guardarInformeConCI(currentUser.id || currentUser.id || 'SISTEMA');
          }
        },
        error: () => {
          this.guardarInformeConCI(currentUser.id || currentUser.id || 'SISTEMA');
        }
      });
      return;
    }
  } else {
    // Si no hay usuario, usar ID de sesión
    ciUsuario = sessionStorage.getItem('userId') || 'SISTEMA';
  }
  
  this.guardarInformeConCI(ciUsuario);
}

private guardarInformeConCI(ciUsuario: string): void {
  const informeData = {
    idRecaudacion:     this.idRecaudacion,
    idComercio:        this.idComercio,
    ciUsuario:         ciUsuario,  // Usar el valor obtenido
    nombreMaquina:     this.maquinaNombre,
    nombreComercio:    this.comercioNombre,
    direccionComercio: this.comercioDireccion,
    telefonoComercio:  this.comercioTelefono,
    pagoEnsamblador:   this.tecnicos.ensamblador  ? 400 : 0,
    pagoComprobador:   this.tecnicos.comprobador  ? 400 : 0,
    pagoMantenimiento: this.tecnicos.mantenimiento ? 400 : 0,
    componentes:       this.componentes.map(c => ({ ID_Componente: c.ID_Componente })),
    montoTotal:        this.montoTotal
  };

  this.contabilidadService.guardarInforme(informeData).subscribe({
    next: (response) => {
      if (response.success) {
        this.success = true;
        this.snackBar.open('Informe guardado correctamente', 'Cerrar', { duration: 3000 });
        setTimeout(() => this.router.navigate(['/contabilidad/consultar-recaudaciones']), 2000);
      } else {
        this.snackBar.open(response.message || 'Error al guardar informe', 'Cerrar', { duration: 3000 });
      }
      this.guardando = false;
    },
    error: (err) => {
      this.snackBar.open(err.message || 'Error al guardar informe', 'Cerrar', { duration: 3000 });
      this.guardando = false;
    }
  });
}

  regresar(): void { this.router.navigate(['/contabilidad/consultar-recaudaciones']); }
}