/**
 * @fileoverview Dashboard de Técnico de Mantenimiento
 * @description Panel para técnicos de mantenimiento para gestionar reparaciones
 * @component DashboardMantenimientoComponent
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header.component';
import { TecnicoService } from '../../services/tecnico';
import { AuthService } from '@core/services/auth.service';
import { NotificationService } from '@core/services/notification.service';
import { Maquina } from '@core/models/maquina.model';
import { User } from '@core/models/user.model';

@Component({
  selector: 'app-dashboard-mantenimiento',
  standalone: true,
  imports: [CommonModule, FormsModule, MatCardModule, MatButtonModule, MatIconModule, MatProgressSpinnerModule, AdminHeaderComponent],
  templateUrl: './dashboard-mantenimiento.html',
  styleUrls: ['./dashboard-mantenimiento.css']
})
export class DashboardMantenimientoComponent implements OnInit {
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
  maquinasMantenimiento: Maquina[] = [];
  selectedMaquina: Maquina | null = null;
  mostrarMantenimiento = false;
  cargandoMantenimiento = false;
  mostrarModalMensaje = false;
  mensaje = '';
  enviando = false;
  exitoMantenimiento = true;
  
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
    this.cargandoMantenimiento = true;
    this.tecnicoService.getMaquinasMantenimiento(this.user!.ID_Usuario).subscribe({
      next: (maquinas) => { this.maquinasMantenimiento = maquinas; this.cargandoMantenimiento = false; },
      error: () => { this.cargandoMantenimiento = false; }
    });
  }
  
  seleccionarMaquina(maquina: Maquina): void {
    this.selectedMaquina = this.selectedMaquina?.ID_Maquina === maquina.ID_Maquina ? null : maquina;
  }
  
  abrirModalMensaje(exito: boolean): void {
    if (!this.selectedMaquina) return;
    this.exitoMantenimiento = exito;
    this.mensaje = '';
    this.mostrarModalMensaje = true;
  }
  
  cerrarModalMensaje(): void {
    this.mostrarModalMensaje = false;
    this.mensaje = '';
  }
  
  finalizarMantenimiento(): void {
    if (!this.selectedMaquina || !this.mensaje.trim()) return;
    this.enviando = true;
    this.tecnicoService.finalizarMantenimiento({
      idMaquina: this.selectedMaquina.ID_Maquina, idRemitente: this.user!.ID_Usuario,
      exito: this.exitoMantenimiento, mensaje: this.mensaje
    }).subscribe({
      next: (success) => {
        if (success) {
          const mensajeExito = this.exitoMantenimiento ? 'Mantenimiento finalizado correctamente. Máquina operativa.' : 'Mantenimiento finalizado. Máquina enviada a reensamblar.';
          this.snackBar.open(mensajeExito, 'Cerrar', { duration: 3000 });
          this.cargarMaquinas();
          this.selectedMaquina = null;
          this.cerrarModalMensaje();
        } else { this.snackBar.open('Error al finalizar el mantenimiento', 'Cerrar', { duration: 3000 }); }
        this.enviando = false;
      },
      error: () => { this.snackBar.open('Error al finalizar el mantenimiento', 'Cerrar', { duration: 3000 }); this.enviando = false; }
    });
  }
  
  marcarNotificacionLeida(idNotificacion: string): void {
    this.notificationService.markAsRead(idNotificacion).subscribe({
      next: () => { this.cargarNotificaciones(); },
      error: () => {}
    });
  }
  
  irAInformesDistribucion(): void { this.router.navigate(['/logistica/consultar-informe-distribucion']); }
}