/**
 * @fileoverview Dashboard de Técnico Comprobador
 * @description Panel para técnicos comprobadores con checklist de calidad
 * @component DashboardComprobadorComponent
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatCheckboxModule } from '@angular/material/checkbox';
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
import { Router } from '@angular/router';

@Component({
  selector: 'app-dashboard-comprobador',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatCheckboxModule,
    MatProgressSpinnerModule,
    AdminHeaderComponent, MaquinasDashboardComponent
  ],
  templateUrl: './dashboard-comprobador.html',
  styleUrls: ['./dashboard-comprobador.css']
})
export class DashboardComprobadorComponent implements OnInit {
  private tecnicoService = inject(TecnicoService);
  private authService = inject(AuthService);
  private snackBar = inject(MatSnackBar);
  private dialog = inject(MatDialog);
  private router = inject(Router);

  user: User | null = null;
  maquinasComprobando: Maquina[] = [];
  selectedMaquina: Maquina | null = null;
  mostrarComprobando = false;
  cargandoComprobando = false;
  
  checklist = {
    placaFuncional: false,
    carcasaBuenEstado: false,
    experienciaJuegoAcorde: false
  };
  
  mostrarModalMensaje = false;
  mensaje = '';
  enviando = false;
  accionActual: 'distribucion' | 'reensamblar' = 'distribucion';

  ngOnInit(): void {
    this.user = this.authService.getCurrentUser();
    if (this.user?.id) {
      this.cargarMaquinas();
    }
  }

  private cargarMaquinas(): void {
    this.cargandoComprobando = true;
    this.tecnicoService.getMaquinasComprobador(this.user!.id).subscribe({
      next: (maquinas) => {
        this.maquinasComprobando = maquinas;
        this.cargandoComprobando = false;
      },
      error: () => {
        this.cargandoComprobando = false;
      }
    });
  }

  seleccionarMaquina(maquina: Maquina): void {
    this.selectedMaquina = this.selectedMaquina?.ID_Maquina === maquina.ID_Maquina ? null : maquina;
    this.resetChecklist();
  }

  private resetChecklist(): void {
    this.checklist = {
      placaFuncional: false,
      carcasaBuenEstado: false,
      experienciaJuegoAcorde: false
    };
  }

  allChecksPassed(): boolean {
    return this.checklist.placaFuncional && 
           this.checklist.carcasaBuenEstado && 
           this.checklist.experienciaJuegoAcorde;
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
      this.tecnicoService.mandarADistribucion({
        idMaquina: this.selectedMaquina.ID_Maquina,
        idRemitente: this.user!.id,
        mensaje: this.mensaje
      }).subscribe({
        next: (success) => {
          if (success) {
            this.snackBar.open('Máquina enviada a distribución correctamente', 'Cerrar', { duration: 3000 });
            this.cargarMaquinas();
            this.selectedMaquina = null;
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
    } else {
      this.tecnicoService.mandarAReensamblar({
        idMaquina: this.selectedMaquina.ID_Maquina,
        idRemitente: this.user!.id,
        mensaje: this.mensaje
      }).subscribe({
        next: (success) => {
          if (success) {
            this.snackBar.open('Máquina enviada a reensamblar correctamente', 'Cerrar', { duration: 3000 });
            this.cargarMaquinas();
            this.selectedMaquina = null;
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
  
  irAGestionComponentes(maquina: Maquina | null = null): void {
      const machineToUse = maquina || this.selectedMaquina;
      
      if (!machineToUse) {
          this.snackBar.open('Primero seleccione una máquina', 'Cerrar', { duration: 3000 });
          return;
      }
      
      // Verificar que la máquina tenga ID y nombre
      if (!machineToUse.ID_Maquina) {
          this.snackBar.open('La máquina seleccionada no es válida', 'Cerrar', { duration: 3000 });
          return;
      }
      
      // Asegurar que el nombre no sea null/undefined
      const machineToSave = {
          ...machineToUse,
          Nombre_Maquina: machineToUse.Nombre_Maquina || 'Máquina sin nombre'
      };
      
      console.log('Guardando máquina:', machineToSave);
      
      localStorage.setItem('selectedMachine', JSON.stringify(machineToSave));
      this.router.navigate(['/tecnico/gestion-componentes']);
  }
}