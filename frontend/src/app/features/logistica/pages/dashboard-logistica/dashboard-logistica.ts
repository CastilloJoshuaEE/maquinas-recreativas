/**
 * @fileoverview Dashboard de Logística
 * @description Panel principal del módulo de logística con gestión de máquinas
 * @component DashboardLogisticaComponent
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header';
import { LogisticaService } from '../../services/logistica';
import { AuthService } from '@core/services/auth';
import { NotificationService } from '@core/services/notification';
import { Maquina } from '@core/models/maquina.model';
import { User } from '@core/models/user.model';
import { ComercioFormComponent } from '../../ui/comercio-form/comercio-form';
import { MaquinaFormComponent } from '../../ui/maquina-form/maquina-form';
@Component({
  selector: 'app-dashboard-logistica',
  standalone: true,
  imports: [
    CommonModule, FormsModule, MatCardModule, MatButtonModule, MatIconModule,
    MatProgressSpinnerModule, AdminHeaderComponent, ComercioFormComponent, MaquinaFormComponent
  ],
  templateUrl: './dashboard-logistica.html',
  styleUrls: ['./dashboard-logistica.css']
})
export class DashboardLogisticaComponent implements OnInit {
  private router = inject(Router);
  private logisticaService = inject(LogisticaService);
  private authService = inject(AuthService);
  private notificationService = inject(NotificationService);
  private snackBar = inject(MatSnackBar);
  
  user: User | null = null;
  notificaciones: any[] = [];
  notificacionesNoLeidas = 0;
  cargandoNotificaciones = false;
  mostrarNotificaciones = false;
  
  maquinasDistribucion: Maquina[] = [];
  maquinasOperativas: Maquina[] = [];
  maquinasRetiradas: Maquina[] = [];
  selectedMaquina: Maquina | null = null;
  
  mostrarDistribucion = false;
  mostrarOperativas = false;
  mostrarRetiradas = false;
  
  cargandoDistribucion = false;
  cargandoOperativas = false;
  cargandoRetiradas = false;
  
  mostrarMensajeMantenimiento = false;
  mensajeMantenimiento = '';
  enviandoMantenimiento = false;
  errorMantenimiento = '';
  
  mostrarComercioForm = false;
  mostrarMaquinaForm = false;
  
  ngOnInit(): void {
    this.user = this.authService.getCurrentUser();
    if (this.user?.id) {
      this.cargarNotificaciones();
      this.cargarMaquinas();
    }
  }
  
  private cargarNotificaciones(): void {
    this.cargandoNotificaciones = true;
    this.notificationService.getMaquinaNotifications(this.user!.id).subscribe({
      next: (notificaciones) => {
        this.notificaciones = notificaciones;
        this.notificacionesNoLeidas = notificaciones.filter(n => !n.leida).length;
        this.cargandoNotificaciones = false;
      },
      error: () => { this.cargandoNotificaciones = false; }
    });
  }
  
  private cargarMaquinas(): void {
    this.cargarMaquinasDistribucion();
    this.cargarMaquinasOperativas();
    this.cargarMaquinasRetiradas();
  }
  
  private cargarMaquinasDistribucion(): void {
    this.cargandoDistribucion = true;
    this.logisticaService.getMaquinasDistribucion().subscribe({
      next: (maquinas) => { this.maquinasDistribucion = maquinas; this.cargandoDistribucion = false; },
      error: () => { this.cargandoDistribucion = false; }
    });
  }
  
  private cargarMaquinasOperativas(): void {
    this.cargandoOperativas = true;
    this.logisticaService.getMaquinasOperativas().subscribe({
      next: (maquinas) => { this.maquinasOperativas = maquinas; this.cargandoOperativas = false; },
      error: () => { this.cargandoOperativas = false; }
    });
  }
  
  private cargarMaquinasRetiradas(): void {
    this.cargandoRetiradas = true;
    this.logisticaService.getMaquinasRetiradas().subscribe({
      next: (maquinas) => { this.maquinasRetiradas = maquinas; this.cargandoRetiradas = false; },
      error: () => { this.cargandoRetiradas = false; }
    });
  }
  
  seleccionarMaquina(maquina: Maquina): void {
    this.selectedMaquina = this.selectedMaquina?.ID_Maquina === maquina.ID_Maquina ? null : maquina;
  }
  
  esMaquinaDistribucion(): boolean {
    return this.selectedMaquina ? this.maquinasDistribucion.some(m => m.ID_Maquina === this.selectedMaquina!.ID_Maquina) : false;
  }
  
  esMaquinaOperativa(): boolean {
    return this.selectedMaquina ? this.maquinasOperativas.some(m => m.ID_Maquina === this.selectedMaquina!.ID_Maquina) : false;
  }
  
  ponerOperativa(): void {
    if (!this.selectedMaquina) return;
    if (!confirm(`¿Está seguro de poner operativa la máquina ${this.selectedMaquina.Nombre_Maquina}?`)) return;
    
    this.logisticaService.ponerMaquinaOperativa(this.selectedMaquina.ID_Maquina).subscribe({
      next: (success) => {
        if (success) {
          this.snackBar.open('Máquina puesta operativa correctamente', 'Cerrar', { duration: 3000 });
          this.cargarMaquinas();
          this.selectedMaquina = null;
        } else {
          this.snackBar.open('Error al poner operativa la máquina', 'Cerrar', { duration: 3000 });
        }
      },
      error: () => { this.snackBar.open('Error al poner operativa la máquina', 'Cerrar', { duration: 3000 }); }
    });
  }
  
  abrirModalMantenimiento(): void {
    if (!this.selectedMaquina) return;
    this.mensajeMantenimiento = '';
    this.errorMantenimiento = '';
    this.mostrarMensajeMantenimiento = true;
  }
  
  cerrarModalMantenimiento(): void {
    this.mostrarMensajeMantenimiento = false;
    this.mensajeMantenimiento = '';
    this.errorMantenimiento = '';
  }
  
  enviarMantenimiento(): void {
    if (!this.selectedMaquina || !this.mensajeMantenimiento.trim()) return;
    
    this.enviandoMantenimiento = true;
    this.errorMantenimiento = '';
    
    this.logisticaService.solicitarMantenimiento({
      idMaquina: this.selectedMaquina.ID_Maquina,
      mensaje: this.mensajeMantenimiento,
      idLogistica: this.user!.id
    }).subscribe({
      next: (success) => {
        if (success) {
          this.snackBar.open('Solicitud de mantenimiento enviada correctamente', 'Cerrar', { duration: 3000 });
          this.cargarMaquinas();
          this.selectedMaquina = null;
          this.cerrarModalMantenimiento();
        } else {
          this.errorMantenimiento = 'No hay técnicos de mantenimiento disponibles';
        }
        this.enviandoMantenimiento = false;
      },
      error: () => {
        this.errorMantenimiento = 'No hay técnicos de mantenimiento disponibles';
        this.enviandoMantenimiento = false;
      }
    });
  }
  
  marcarNotificacionLeida(idNotificacion: string): void {
    this.notificationService.markAsRead(idNotificacion).subscribe({
      next: () => { this.cargarNotificaciones(); },
      error: () => {}
    });
  }
  
  abrirFormularioComercio(): void { this.mostrarComercioForm = true; }
  cerrarFormularioComercio(): void { this.mostrarComercioForm = false; }
  onComercioRegistrado(): void { this.cerrarFormularioComercio(); this.snackBar.open('Comercio registrado correctamente', 'Cerrar', { duration: 3000 }); }
  
  abrirFormularioMaquina(): void { this.mostrarMaquinaForm = true; }
  cerrarFormularioMaquina(): void { this.mostrarMaquinaForm = false; }
  onMaquinaRegistrada(): void { this.cerrarFormularioMaquina(); this.cargarMaquinas(); this.snackBar.open('Máquina registrada correctamente', 'Cerrar', { duration: 3000 }); }
  
  irAInformesDistribucion(): void { this.router.navigate(['/logistica/consultar-informe-distribucion']); }
}