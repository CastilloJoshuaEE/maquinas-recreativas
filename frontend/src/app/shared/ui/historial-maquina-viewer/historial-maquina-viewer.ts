import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatDialogRef, MatDialogModule, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { MaquinasSharedService, HistorialEvento } from '@core/services/maquina';

// Definir la interfaz de los datos
interface HistorialDialogData {
  idMaquina: string;
  nombreMaquina: string;
}

@Component({
  selector: 'app-historial-maquina-viewer',
  standalone: true,
  imports: [
    CommonModule,
    MatDialogModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule
  ],
  templateUrl: './historial-maquina-viewer.html',
  styleUrls: ['./historial-maquina-viewer.css']
})
export class HistorialMaquinaViewerComponent implements OnInit {
  private maquinasService = inject(MaquinasSharedService);
  private snackBar = inject(MatSnackBar);
  private dialogRef = inject(MatDialogRef<HistorialMaquinaViewerComponent>);
  private data = inject<HistorialDialogData>(MAT_DIALOG_DATA);  // ← Usar MAT_DIALOG_DATA

  // Obtener los valores de data
  idMaquina = this.data.idMaquina;
  nombreMaquina = this.data.nombreMaquina;

  historial: HistorialEvento[] = [];
  cargando = false;
  pagina = 1;
  porPagina = 50;
  total = 0;
  totalPaginas = 0;

  ngOnInit(): void {
    console.log('HistorialViewer - ID:', this.idMaquina);
    console.log('HistorialViewer - Nombre:', this.nombreMaquina);
    
    if (!this.idMaquina) {
      console.error('No se recibió ID de máquina');
      this.snackBar.open('Error: No se pudo cargar el historial', 'Cerrar', { duration: 3000 });
      this.cerrar();
      return;
    }
    
    this.cargarHistorial();
  }

  cargarHistorial(): void {
    if (!this.idMaquina) return;
    
    this.cargando = true;
    console.log(`Cargando historial para máquina ${this.idMaquina}, página ${this.pagina}`);
    
    this.maquinasService.getHistorialMaquina(this.idMaquina, this.pagina, this.porPagina).subscribe({
      next: (response) => {
        console.log('Historial recibido:', response);
        this.historial = response.historial;
        this.total = response.paginacion.total;
        this.totalPaginas = response.paginacion.total_paginas;
        this.cargando = false;
      },
      error: (error) => {
        console.error('Error cargando historial:', error);
        this.snackBar.open('Error al cargar el historial', 'Cerrar', { duration: 3000 });
        this.cargando = false;
      }
    });
  }

  paginaAnterior(): void {
    if (this.pagina > 1) {
      this.pagina--;
      this.cargarHistorial();
    }
  }

  paginaSiguiente(): void {
    if (this.pagina < this.totalPaginas) {
      this.pagina++;
      this.cargarHistorial();
    }
  }

  getIconoAccion(accion: string): string {
    const iconos: Record<string, string> = {
      'Creación': 'add_circle',
      'Envío a comprobación': 'fact_check',
      'Envío a reensamblar': 'build',
      'Envío a distribución': 'local_shipping',
      'Puesta operativa': 'check_circle',
      'Mantenimiento': 'handyman',
      'Mantenimiento finalizado': 'done_all',
      'Actualización': 'edit',
      'Retiro': 'delete'
    };
    return iconos[accion] || 'history';
  }

  getColorAccion(accion: string): string {
    const colores: Record<string, string> = {
      'Creación': 'color-creation',
      'Envío a comprobación': 'color-comprobacion',
      'Envío a reensamblar': 'color-reensamblar',
      'Envío a distribución': 'color-distribucion',
      'Puesta operativa': 'color-operativa',
      'Mantenimiento': 'color-mantenimiento',
      'Mantenimiento finalizado': 'color-finalizado'
    };
    return colores[accion] || 'color-default';
  }

  cerrar(): void {
    this.dialogRef.close();
  }
}