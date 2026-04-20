/**
 * @fileoverview Componente de Historial de Máquina
 * @description Muestra el historial de actividades de una máquina específica
 * @component HistorialMaquinaComponent
 */

import { Component, Input, Output, EventEmitter, OnInit, OnDestroy, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatPaginatorModule } from '@angular/material/paginator';
import { MatSelectModule } from '@angular/material/select';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatNativeDateModule } from '@angular/material/core';
import { MatInputModule } from '@angular/material/input';  //  AÑADIR
import { MatFormFieldModule } from '@angular/material/form-field'; //  AÑADIR
import { TecnicoService } from '../../../services/tecnico';
import { Subscription } from 'rxjs';

interface HistorialItem {
  ID_Historial: number; accion: string; fecha_hora: string; usuario_nombre: string;
  usuario_apellido: string; tipo_usuario: string; descripcion?: string;
  estado_anterior?: string; estado_nuevo?: string; etapa_anterior?: string;
  etapa_nueva?: string; ip_address?: string; Nombre_Maquina?: string;
}

@Component({
  selector: 'app-historial-maquina',
  standalone: true,
  imports: [
    CommonModule, FormsModule, MatCardModule, MatButtonModule, MatIconModule, 
    MatProgressSpinnerModule, MatPaginatorModule, MatSelectModule, 
    MatDatepickerModule, MatNativeDateModule, 
    MatInputModule,      //  AÑADIR
    MatFormFieldModule   //  AÑADIR
  ],
  templateUrl: './historial-maquina.html',
  styleUrls: ['./historial-maquina.css']
})
export class HistorialMaquinaComponent implements OnInit, OnDestroy {
  @Input() idMaquina: string = '';
  @Input() nombreMaquina: string = 'General';
  @Output() onClose = new EventEmitter<void>();
  
  private tecnicoService = inject(TecnicoService);
  private subscription?: Subscription;
  
  historial: HistorialItem[] = [];
  loading = true;
  error = '';
  paginacion = { pagina_actual: 1, total_paginas: 1, total: 0 };
  filtros = { fecha_inicio: '', fecha_fin: '', tipo_usuario: '', accion: '' };
  
  ngOnInit(): void { this.cargarHistorial(); }
  ngOnDestroy(): void { if (this.subscription) this.subscription.unsubscribe(); }
  
  cargarHistorial(): void {
    this.loading = true;
    this.error = '';
    this.subscription = this.tecnicoService.getHistorialMaquina(this.idMaquina, this.paginacion.pagina_actual, 20).subscribe({
      next: (response) => { 
        this.historial = response.historial; 
        this.paginacion = response.paginacion; 
        this.loading = false; 
      },
      error: (err) => { 
        console.error('Error cargando historial:', err);
        this.error = err.message || 'Error al cargar el historial'; 
        this.loading = false; 
      }
    });
  }
  
  cambiarPagina(pagina: number): void {
    if (pagina < 1 || pagina > this.paginacion.total_paginas) return;
    this.paginacion.pagina_actual = pagina;
    this.cargarHistorial();
  }
  
  limpiarFiltros(): void {
    this.filtros = { fecha_inicio: '', fecha_fin: '', tipo_usuario: '', accion: '' };
    this.cargarHistorial();
  }
  
  getIconoAccion(accion: string): string {
    const iconos: { [key: string]: string } = {
      'Registro': '➕', 'Montaje': '', 'Comprobación': '', 'Distribución': '🚚',
      'Mantenimiento': '⚙️', 'Reparación': '🔨', 'Retirada': '📤', 'Ensamblaje': '🛠️',
      'Actualización': '🔄', 'Cambio de estado': '📝'
    };
    return iconos[accion] || '📋';
  }
  
  getColorAccion(accion: string): string {
    const colores: { [key: string]: string } = {
      'Registro': '#4CAF50', 'Montaje': '#2196F3', 'Comprobación': '#9C27B0',
      'Distribución': '#FF9800', 'Mantenimiento': '#FFC107', 'Reparación': '#F44336',
      'Retirada': '#795548', 'Ensamblaje': '#00BCD4', 'Actualización': '#3F51B5',
      'Cambio de estado': '#607D8B'
    };
    return colores[accion] || '#9E9E9E';
  }
}