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
import { MatDialog } from '@angular/material/dialog';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header';
import { MaquinasDashboardComponent } from '@shared/ui/maquinas-dashboard/maquinas-dashboard';
import { HistorialMaquinaViewerComponent } from '@app/shared/ui/historial-maquina-viewer/historial-maquina-viewer';
import { TecnicoService } from '../../services/tecnico';
import { AuthService } from '@core/services/auth';
import { Maquina } from '@core/models/maquina.model';
import { User } from '@core/models/user.model';

@Component({
  selector: 'app-dashboard-mantenimiento',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    AdminHeaderComponent,
    MaquinasDashboardComponent
  ],
  templateUrl: './dashboard-mantenimiento.html',
  styleUrls: ['./dashboard-mantenimiento.css']
})
export class DashboardMantenimientoComponent implements OnInit {
  private tecnicoService = inject(TecnicoService);
  private authService = inject(AuthService);
  private snackBar = inject(MatSnackBar);
  private dialog = inject(MatDialog);

  user: User | null = null;
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
    if (this.user?.id) {
      this.cargarMaquinas();
    }
  }

  private cargarMaquinas(): void {
    this.cargandoMantenimiento = true;
    this.tecnicoService.getMaquinasMantenimiento(this.user!.id).subscribe({
      next: (maquinas) => {
        this.maquinasMantenimiento = maquinas;
        this.cargandoMantenimiento = false;
      },
      error: () => {
        this.cargandoMantenimiento = false;
      }
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
      idMaquina: this.selectedMaquina.ID_Maquina,
      idRemitente: this.user!.id,
      exito: this.exitoMantenimiento,
      mensaje: this.mensaje
    }).subscribe({
      next: (success) => {
        if (success) {
          const mensajeExito = this.exitoMantenimiento 
            ? 'Mantenimiento finalizado correctamente. Máquina operativa.' 
            : 'Mantenimiento finalizado. Máquina enviada a reensamblar.';
          this.snackBar.open(mensajeExito, 'Cerrar', { duration: 3000 });
          this.cargarMaquinas();
          this.selectedMaquina = null;
          this.cerrarModalMensaje();
        } else {
          this.snackBar.open('Error al finalizar el mantenimiento', 'Cerrar', { duration: 3000 });
        }
        this.enviando = false;
      },
      error: () => {
        this.snackBar.open('Error al finalizar el mantenimiento', 'Cerrar', { duration: 3000 });
        this.enviando = false;
      }
    });
  }

  verHistorialMaquina(maquina: Maquina): void {
    this.dialog.open(HistorialMaquinaViewerComponent, {
      width: '800px',
      data: {
        idMaquina: maquina.ID_Maquina,
        nombreMaquina: maquina.Nombre_Maquina
      }
    });
  }
}