/**
 * @fileoverview Dashboard de Técnico Ensamblador
 * @description Panel para técnicos ensambladores para gestionar máquinas en ensamblaje
 * @component DashboardEnsambladorComponent
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header';
import { TecnicoService } from '../../services/tecnico';
import { AuthService } from '@core/services/auth';
import { NotificationService } from '@core/services/notification';
import { Maquina } from '@core/models/maquina.model';
import { User } from '@core/models/user.model';

@Component({
  selector: 'app-dashboard-ensamblador',
  standalone: true,
  imports: [CommonModule, FormsModule, MatCardModule, MatButtonModule, MatIconModule, MatProgressSpinnerModule, AdminHeaderComponent],
  templateUrl: './dashboard-ensamblador.html',
  styleUrls: ['./dashboard-ensamblador.css']
})
export class DashboardEnsambladorComponent implements OnInit {
  private router = inject(Router);
  private tecnicoService = inject(TecnicoService);
  private authService = inject(AuthService);
  private notificationService = inject(NotificationService);
  private snackBar = inject(MatSnackBar);
  
  user: User | null = null;
  notificaciones: any[] = [];
  notificacionesNoLeidas = 0;
  cargandoNotificaciones = false;
  mostrarNotificaciones = false;
  maquinasEnsamblando: Maquina[] = [];
  maquinasReensamblando: Maquina[] = [];
  selectedMaquina: Maquina | null = null;
  selectedMaquinaReensamblar: Maquina | null = null;
  mostrarEnsamblando = false;
  mostrarReensamblando = false;
  cargandoEnsamblando = false;
  cargandoReensamblando = false;
  mostrarModalMensaje = false;
  mensaje = '';
  enviando = false;
  accionActual: 'comprobacion' | 'reensamblar' = 'comprobacion';
  
  ngOnInit(): void {
    this.user = this.authService.getCurrentUser();
    if (this.user?.ID_Usuario) {
      this.cargarNotificaciones();
      this.cargarMaquinas();
    }
  }
  
  private cargarNotificaciones(): void {
    this.cargandoNotificaciones = true;
    this.notificationService.getMaquinaNotifications(this.user!.ID_Usuario).subscribe({
      next: (notificaciones) => {
        this.notificaciones = notificaciones;
        this.notificacionesNoLeidas = notificaciones.filter(n => !n.leida).length;
        this.cargandoNotificaciones = false;
      },
      error: () => { this.cargandoNotificaciones = false; }
    });
  }
  
  private cargarMaquinas(): void {
    this.cargarMaquinasEnsamblando();
    this.cargarMaquinasReensamblando();
  }
  
  private cargarMaquinasEnsamblando(): void {
    this.cargandoEnsamblando = true;
    this.tecnicoService.getMaquinasEnsamblador(this.user!.ID_Usuario).subscribe({
      next: (maquinas) => { this.maquinasEnsamblando = maquinas.filter(m => m.estado === 'Ensamblandose'); this.cargandoEnsamblando = false; },
      error: () => { this.cargandoEnsamblando = false; }
    });
  }
  
  private cargarMaquinasReensamblando(): void {
    this.cargandoReensamblando = true;
    this.tecnicoService.getMaquinasEnsamblador(this.user!.ID_Usuario).subscribe({
      next: (maquinas) => { this.maquinasReensamblando = maquinas.filter(m => m.estado === 'Reensamblandose'); this.cargandoReensamblando = false; },
      error: () => { this.cargandoReensamblando = false; }
    });
  }
  
  seleccionarMaquina(maquina: Maquina): void {
    this.selectedMaquina = this.selectedMaquina?.ID_Maquina === maquina.ID_Maquina ? null : maquina;
    this.selectedMaquinaReensamblar = null;
  }
  
  seleccionarMaquinaReensamblar(maquina: Maquina): void {
    this.selectedMaquinaReensamblar = this.selectedMaquinaReensamblar?.ID_Maquina === maquina.ID_Maquina ? null : maquina;
    this.selectedMaquina = null;
  }
  
  abrirModalMensaje(accion: 'comprobacion' | 'reensamblar'): void {
    this.accionActual = accion;
    this.mensaje = '';
    this.mostrarModalMensaje = true;
  }
  
  abrirModalMensajeReensamblar(): void { this.abrirModalMensaje('reensamblar'); }
  
  cerrarModalMensaje(): void {
    this.mostrarModalMensaje = false;
    this.mensaje = '';
  }
  
  enviarAComprobacion(): void {
    const maquina = this.accionActual === 'comprobacion' ? this.selectedMaquina : this.selectedMaquinaReensamblar;
    if (!maquina || !this.mensaje.trim()) return;
    this.enviando = true;
    this.tecnicoService.mandarAComprobacion({ idMaquina: maquina.ID_Maquina, idRemitente: this.user!.ID_Usuario, mensaje: this.mensaje }).subscribe({
      next: (success) => {
        if (success) { this.snackBar.open('Máquina enviada a comprobación correctamente', 'Cerrar', { duration: 3000 }); this.cargarMaquinas(); this.selectedMaquina = null; this.selectedMaquinaReensamblar = null; this.cerrarModalMensaje(); }
        else { this.snackBar.open('Error al enviar la máquina', 'Cerrar', { duration: 3000 }); }
        this.enviando = false;
      },
      error: () => { this.snackBar.open('Error al enviar la máquina', 'Cerrar', { duration: 3000 }); this.enviando = false; }
    });
  }
  
  marcarNotificacionLeida(idNotificacion: string): void {
    this.notificationService.markAsRead(idNotificacion).subscribe({
      next: () => { this.cargarNotificaciones(); },
      error: () => {}
    });
  }
  
  irAGestionComponentes(): void { this.router.navigate(['/tecnico/gestion-componentes']); }
}