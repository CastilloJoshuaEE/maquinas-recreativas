/**
 * @fileoverview Dashboard de Técnico Comprobador
 * @description Panel para técnicos comprobadores con checklist de calidad
 * @component DashboardComprobadorComponent
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header';
import { TecnicoService } from '../../services/tecnico';
import { AuthService } from '@core/services/auth';
import { NotificationService } from '@core/services/notification';
import { Maquina } from '@core/models/maquina.model';
import { User } from '@core/models/user.model';
import { HistorialMaquinaComponent } from '../ui/historial-maquina/historial-maquina';
import { NotificacionMaquinaService } from '@core/services/notification-maquina';

@Component({
  selector: 'app-dashboard-comprobador',
  standalone: true,
  imports: [
    CommonModule, FormsModule, MatCardModule, MatButtonModule, MatIconModule,
    MatCheckboxModule, MatProgressSpinnerModule, AdminHeaderComponent, HistorialMaquinaComponent
  ],
  templateUrl: './dashboard-comprobador.html',
  styleUrls: ['./dashboard-comprobador.css']
})
export class DashboardComprobadorComponent implements OnInit {
  private router = inject(Router);
  private tecnicoService = inject(TecnicoService);
  private authService = inject(AuthService);
  private notificationService = inject(NotificationService);
  private snackBar = inject(MatSnackBar);
  private notificacionMaquinaService = inject(NotificacionMaquinaService);

  user: User | null = null;
  notificaciones: any[] = [];
  notificacionesNoLeidas = 0;
  cargandoNotificaciones = false;
  mostrarNotificaciones = false;
  maquinasComprobando: Maquina[] = [];
  selectedMaquina: Maquina | null = null;
  mostrarComprobando = false;
  cargandoComprobando = false;
  checklist = { placaFuncional: false, carcasaBuenEstado: false, experienciaJuegoAcorde: false };
  mostrarModalMensaje = false;
  mensaje = '';
  enviando = false;
  accionActual: 'distribucion' | 'reensamblar' = 'distribucion';
  mostrarHistorial = false;
  historialMaquinaId = '';
  historialMaquinaNombre = '';
  
  ngOnInit(): void {
    this.user = this.authService.getCurrentUser();
    if (this.user?.id) {
      this.cargarNotificaciones();
      this.cargarMaquinas();
    }
  }
  private cargarNotificaciones(): void {
  this.cargandoNotificaciones = true;
  this.notificacionMaquinaService.getNotificacionesMaquina(this.user!.id).subscribe({
    next: (notificaciones) => {
      this.notificaciones = notificaciones;
      this.notificacionesNoLeidas = notificaciones.filter(n => n.Estado !== 'Leido').length;
      this.cargandoNotificaciones = false;
    },
    error: (err) => { 
      console.error('Error cargando notificaciones:', err);
      this.cargandoNotificaciones = false; 
    }
  });
}
  
  private cargarMaquinas(): void {
    this.cargandoComprobando = true;
    this.tecnicoService.getMaquinasComprobador(this.user!.id).subscribe({
      next: (maquinas) => { this.maquinasComprobando = maquinas; this.cargandoComprobando = false; },
      error: () => { this.cargandoComprobando = false; }
    });
  }
  
  seleccionarMaquina(maquina: Maquina): void {
    this.selectedMaquina = this.selectedMaquina?.ID_Maquina === maquina.ID_Maquina ? null : maquina;
    this.resetChecklist();
  }
  
  private resetChecklist(): void {
    this.checklist = { placaFuncional: false, carcasaBuenEstado: false, experienciaJuegoAcorde: false };
  }
  
  allChecksPassed(): boolean {
    return this.checklist.placaFuncional && this.checklist.carcasaBuenEstado && this.checklist.experienciaJuegoAcorde;
  }
  
  abrirModalMensaje(accion: 'distribucion' | 'reensamblar'): void {
    if (!this.selectedMaquina) return;
    this.accionActual = accion;
    this.mensaje = '';
    this.mostrarModalMensaje = true;
  }
  
  cerrarModalMensaje(): void {
    this.mostrarModalMensaje = false;
    this.mensaje = '';
  }
  
  enviarAccion(): void {
    if (!this.selectedMaquina || !this.mensaje.trim()) return;
    this.enviando = true;
    
    if (this.accionActual === 'distribucion') {
      this.tecnicoService.mandarADistribucion({ idMaquina: this.selectedMaquina.ID_Maquina, idRemitente: this.user!.id, mensaje: this.mensaje }).subscribe({
        next: (success) => {
          if (success) { this.snackBar.open('Máquina enviada a distribución correctamente', 'Cerrar', { duration: 3000 }); this.cargarMaquinas(); this.selectedMaquina = null; this.cerrarModalMensaje(); }
          else { this.snackBar.open('Error al enviar la máquina', 'Cerrar', { duration: 3000 }); }
          this.enviando = false;
        },
        error: () => { this.snackBar.open('Error al enviar la máquina', 'Cerrar', { duration: 3000 }); this.enviando = false; }
      });
    } else {
      this.tecnicoService.mandarAReensamblar({ idMaquina: this.selectedMaquina.ID_Maquina, idRemitente: this.user!.id, mensaje: this.mensaje }).subscribe({
        next: (success) => {
          if (success) { this.snackBar.open('Máquina enviada a reensamblar correctamente', 'Cerrar', { duration: 3000 }); this.cargarMaquinas(); this.selectedMaquina = null; this.cerrarModalMensaje(); }
          else { this.snackBar.open('Error al enviar la máquina', 'Cerrar', { duration: 3000 }); }
          this.enviando = false;
        },
        error: () => { this.snackBar.open('Error al enviar la máquina', 'Cerrar', { duration: 3000 }); this.enviando = false; }
      });
    }
  }
  
  verHistorial(maquina: Maquina): void {
    this.historialMaquinaId = maquina.ID_Maquina;
    this.historialMaquinaNombre = maquina.Nombre_Maquina;
    this.mostrarHistorial = true;
  }
  
  cerrarHistorial(): void {
    this.mostrarHistorial = false;
    this.historialMaquinaId = '';
    this.historialMaquinaNombre = '';
  }
marcarNotificacionLeida(idNotificacion: string): void {
  this.notificacionMaquinaService.marcarComoLeida(idNotificacion).subscribe({
    next: (success) => { if (success) this.cargarNotificaciones(); },
    error: (err) => console.error('Error marcando como leída:', err)
  });
}
}