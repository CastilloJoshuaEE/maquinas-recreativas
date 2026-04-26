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
import { MatDialog } from '@angular/material/dialog';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header';
import { MaquinasDashboardComponent } from '@shared/ui/maquinas-dashboard/maquinas-dashboard';
import { HistorialMaquinaViewerComponent } from '@app/shared/ui/historial-maquina-viewer/historial-maquina-viewer';
import { TecnicoService } from '../../services/tecnico';
import { AuthService } from '@core/services/auth';
import { Maquina } from '@core/models/maquina.model';
import { User } from '@core/models/user.model';

@Component({
  selector: 'app-dashboard-ensamblador',
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
  templateUrl: './dashboard-ensamblador.html',
  styleUrls: ['./dashboard-ensamblador.css']
})
export class DashboardEnsambladorComponent implements OnInit {
  private router = inject(Router);
  private tecnicoService = inject(TecnicoService);
  private authService = inject(AuthService);
  private snackBar = inject(MatSnackBar);
  private dialog = inject(MatDialog);

  user: User | null = null;
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
    if (this.user?.id) {
      this.cargarMaquinas();
    }
  }

  private cargarMaquinas(): void {
    this.cargarMaquinasEnsamblando();
    this.cargarMaquinasReensamblando();
  }

  private cargarMaquinasEnsamblando(): void {
    this.cargandoEnsamblando = true;
    this.tecnicoService.getMaquinasEnsamblador(this.user!.id).subscribe({
      next: (maquinas) => {
        this.maquinasEnsamblando = maquinas.filter(m => m.estado === 'Ensamblandose');
        this.cargandoEnsamblando = false;
      },
      error: () => {
        this.cargandoEnsamblando = false;
      }
    });
  }

  private cargarMaquinasReensamblando(): void {
    this.cargandoReensamblando = true;
    this.tecnicoService.getMaquinasEnsamblador(this.user!.id).subscribe({
      next: (maquinas) => {
        this.maquinasReensamblando = maquinas.filter(m => m.estado === 'Reensamblandose');
        this.cargandoReensamblando = false;
      },
      error: () => {
        this.cargandoReensamblando = false;
      }
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

  cerrarModalMensaje(): void {
    this.mostrarModalMensaje = false;
    this.mensaje = '';
  }

  enviarAComprobacion(): void {
    const maquina = this.accionActual === 'comprobacion' ? this.selectedMaquina : this.selectedMaquinaReensamblar;
    if (!maquina || !this.mensaje.trim()) return;
    this.enviando = true;
    
    this.tecnicoService.mandarAComprobacion({
      idMaquina: maquina.ID_Maquina,
      idRemitente: this.user!.id,
      mensaje: this.mensaje
    }).subscribe({
      next: (success) => {
        if (success) {
          this.snackBar.open('Máquina enviada a comprobación correctamente', 'Cerrar', { duration: 3000 });
          this.cargarMaquinas();
          this.selectedMaquina = null;
          this.selectedMaquinaReensamblar = null;
          this.cerrarModalMensaje();
        } else {
          this.snackBar.open('Error al enviar la máquina', 'Cerrar', { duration: 3000 });
        }
        this.enviando = false;
      },
      error: () => {
        this.snackBar.open('Error al enviar la máquina', 'Cerrar', { duration: 3000 });
        this.enviando = false;
      }
    });
  }

  irAGestionComponentes(): void {
    this.router.navigate(['/tecnico/gestion-componentes']);
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