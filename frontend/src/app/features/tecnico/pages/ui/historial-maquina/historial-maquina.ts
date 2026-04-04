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
import { MatPaginatorModule, PageEvent } from '@angular/material/paginator';
import { MatSelectModule } from '@angular/material/select';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatNativeDateModule } from '@angular/material/core';
import { TecnicoService } from '../../../services/tecnico.service';
import { Subscription } from 'rxjs';

interface HistorialItem {
  ID_Historial: number;
  accion: string;
  fecha_hora: string;
  usuario_nombre: string;
  usuario_apellido: string;
  tipo_usuario: string;
  descripcion?: string;
  estado_anterior?: string;
  estado_nuevo?: string;
  etapa_anterior?: string;
  etapa_nueva?: string;
  ip_address?: string;
  Nombre_Maquina?: string;
}

@Component({
  selector: 'app-historial-maquina',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    MatPaginatorModule,
    MatSelectModule,
    MatDatepickerModule,
    MatNativeDateModule
  ],
  template: `
    <div class="historial-modal-overlay" (click)="onClose.emit()">
      <div class="historial-modal" (click)="$event.stopPropagation()">
        <div class="historial-header">
          <h2>Historial de: {{ nombreMaquina }}</h2>
          <button class="close-btn" (click)="onClose.emit()">×</button>
        </div>

        <!-- Filtros -->
        <div class="historial-filtros">
          <mat-form-field appearance="outline">
            <mat-label>Fecha Inicio</mat-label>
            <input matInput [matDatepicker]="pickerInicio" [(ngModel)]="filtros.fecha_inicio">
            <mat-datepicker-toggle matSuffix [for]="pickerInicio"></mat-datepicker-toggle>
            <mat-datepicker #pickerInicio></mat-datepicker>
          </mat-form-field>

          <mat-form-field appearance="outline">
            <mat-label>Fecha Fin</mat-label>
            <input matInput [matDatepicker]="pickerFin" [(ngModel)]="filtros.fecha_fin">
            <mat-datepicker-toggle matSuffix [for]="pickerFin"></mat-datepicker-toggle>
            <mat-datepicker #pickerFin></mat-datepicker>
          </mat-form-field>

          <mat-form-field appearance="outline">
            <mat-label>Tipo Usuario</mat-label>
            <mat-select [(ngModel)]="filtros.tipo_usuario">
              <mat-option value="">Todos</mat-option>
              <mat-option value="Tecnico">Técnicos</mat-option>
              <mat-option value="Logistica">Logística</mat-option>
              <mat-option value="Administrador">Administradores</mat-option>
              <mat-option value="Contabilidad">Contabilidad</mat-option>
            </mat-select>
          </mat-form-field>

          <mat-form-field appearance="outline">
            <mat-label>Acción</mat-label>
            <input matInput [(ngModel)]="filtros.accion" placeholder="Buscar acción...">
          </mat-form-field>

          <button mat-raised-button (click)="limpiarFiltros()">
            <mat-icon>clear</mat-icon>
            Limpiar
          </button>
        </div>

        <!-- Contenido -->
        <div *ngIf="loading" class="historial-loading">
          <mat-spinner diameter="40"></mat-spinner>
          <p>Cargando historial...</p>
        </div>

        <div *ngIf="error" class="historial-error">
          {{ error }}
        </div>

        <div *ngIf="!loading && !error" class="historial-timeline">
          <div *ngIf="historial.length === 0" class="sin-registros">
            No hay registros de actividad
          </div>

          <div *ngFor="let item of historial" class="timeline-item">
            <div class="timeline-icon" [style.backgroundColor]="getColorAccion(item.accion)">
              {{ getIconoAccion(item.accion) }}
            </div>
            <div class="timeline-content">
              <div class="timeline-header">
                <span class="timeline-accion">{{ item.accion }}</span>
                <span class="timeline-fecha">{{ item.fecha_hora | date:'dd/MM/yyyy HH:mm:ss' }}</span>
              </div>
              
              <div class="timeline-usuario">
                <strong>👤 Usuario:</strong> {{ item.usuario_nombre }} {{ item.usuario_apellido }} ({{ item.tipo_usuario }})
              </div>
              
              <div class="timeline-descripcion" *ngIf="item.descripcion">
                <strong>📝 Descripción:</strong> {{ item.descripcion }}
              </div>
              
              <div class="timeline-cambio-estado" *ngIf="item.estado_anterior || item.estado_nuevo">
                <strong>🔄 Cambio de estado:</strong>
                <span class="estado-anterior" *ngIf="item.estado_anterior">{{ item.estado_anterior }}</span>
                <span *ngIf="item.estado_anterior && item.estado_nuevo"> → </span>
                <span class="estado-nuevo" *ngIf="item.estado_nuevo">{{ item.estado_nuevo }}</span>
              </div>
              
              <div class="timeline-cambio-etapa" *ngIf="item.etapa_anterior && item.etapa_nueva">
                <strong>📊 Cambio de etapa:</strong>
                <span class="etapa-anterior">{{ item.etapa_anterior }}</span>
                <span> → </span>
                <span class="etapa-nueva">{{ item.etapa_nueva }}</span>
              </div>
              
              <div class="timeline-ip">
                <small>🌐 IP: {{ item.ip_address || 'Desconocida' }}</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Paginación -->
        <div class="historial-paginacion" *ngIf="paginacion.total_paginas > 1">
          <button (click)="cambiarPagina(paginacion.pagina_actual - 1)" 
                  [disabled]="paginacion.pagina_actual === 1">
            Anterior
          </button>
          <span>
            Página {{ paginacion.pagina_actual }} de {{ paginacion.total_paginas }}
            (Total: {{ paginacion.total }} registros)
          </span>
          <button (click)="cambiarPagina(paginacion.pagina_actual + 1)" 
                  [disabled]="paginacion.pagina_actual === paginacion.total_paginas">
            Siguiente
          </button>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .historial-modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgba(0, 0, 0, 0.7);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1000;
      padding: 20px;
    }
    
    .historial-modal {
      background: white;
      border-radius: 12px;
      width: 90%;
      max-width: 1000px;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
      animation: slideIn 0.3s ease;
    }
    
    @keyframes slideIn {
      from {
        transform: translateY(-30px);
        opacity: 0;
      }
      to {
        transform: translateY(0);
        opacity: 1;
      }
    }
    
    .historial-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 20px;
      border-radius: 12px 12px 0 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 10;
    }
    
    .historial-header h2 {
      margin: 0;
      font-size: 1.5rem;
      color: white;
    }
    
    .close-btn {
      background: rgba(255, 255, 255, 0.2);
      border: none;
      color: white;
      font-size: 28px;
      cursor: pointer;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.2s;
    }
    
    .close-btn:hover {
      background: rgba(255, 255, 255, 0.3);
    }
    
    .historial-filtros {
      padding: 20px;
      background: #f8f9fa;
      border-bottom: 1px solid #dee2e6;
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      position: sticky;
      top: 76px;
      z-index: 5;
    }
    
    .historial-filtros mat-form-field {
      flex: 1;
      min-width: 150px;
    }
    
    .historial-timeline {
      padding: 20px;
      position: relative;
    }
    
    .historial-timeline::before {
      content: '';
      position: absolute;
      left: 50px;
      top: 0;
      bottom: 0;
      width: 2px;
      background: #e9ecef;
    }
    
    .timeline-item {
      display: flex;
      margin-bottom: 30px;
      position: relative;
    }
    
    .timeline-icon {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 20px;
      margin-right: 20px;
      z-index: 2;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      flex-shrink: 0;
    }
    
    .timeline-content {
      flex: 1;
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 8px;
      padding: 15px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
      transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .timeline-content:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    
    .timeline-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
      padding-bottom: 10px;
      border-bottom: 1px solid #e9ecef;
    }
    
    .timeline-accion {
      font-weight: bold;
      font-size: 1.1rem;
      color: #495057;
      padding: 4px 12px;
      background: #f8f9fa;
      border-radius: 20px;
    }
    
    .timeline-fecha {
      color: #6c757d;
      font-size: 0.9rem;
    }
    
    .timeline-usuario,
    .timeline-descripcion,
    .timeline-cambio-estado,
    .timeline-cambio-etapa {
      margin: 8px 0;
      color: #495057;
      line-height: 1.5;
    }
    
    .timeline-cambio-estado,
    .timeline-cambio-etapa {
      background: #f8f9fa;
      padding: 8px;
      border-radius: 6px;
      border-left: 3px solid #667eea;
    }
    
    .estado-anterior {
      color: #dc3545;
      text-decoration: line-through;
      margin: 0 5px;
      background: #fee;
      padding: 2px 8px;
      border-radius: 4px;
    }
    
    .estado-nuevo {
      color: #28a745;
      font-weight: bold;
      margin: 0 5px;
      background: #e8f5e9;
      padding: 2px 8px;
      border-radius: 4px;
    }
    
    .etapa-anterior,
    .etapa-nueva {
      display: inline-block;
      margin: 0 5px;
      padding: 2px 8px;
      border-radius: 4px;
      font-weight: 500;
    }
    
    .etapa-anterior {
      background: #fff3cd;
      color: #856404;
    }
    
    .etapa-nueva {
      background: #d4edda;
      color: #155724;
    }
    
    .timeline-ip {
      margin-top: 8px;
      text-align: right;
      color: #6c757d;
    }
    
    .historial-loading {
      text-align: center;
      padding: 40px;
      color: #6c757d;
      font-size: 1.1rem;
    }
    
    .historial-error {
      text-align: center;
      padding: 20px;
      color: #dc3545;
      background: #f8d7da;
      border: 1px solid #f5c6cb;
      border-radius: 6px;
      margin: 20px;
    }
    
    .sin-registros {
      text-align: center;
      padding: 40px;
      color: #6c757d;
      font-style: italic;
      background: #f8f9fa;
      border-radius: 8px;
    }
    
    .historial-paginacion {
      padding: 20px;
      border-top: 1px solid #dee2e6;
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 20px;
      background: #f8f9fa;
      border-radius: 0 0 12px 12px;
      position: sticky;
      bottom: 0;
    }
    
    .historial-paginacion button {
      padding: 8px 20px;
      background: #667eea;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      transition: background 0.2s;
    }
    
    .historial-paginacion button:hover:not(:disabled) {
      background: #5a67d8;
    }
    
    .historial-paginacion button:disabled {
      background: #cbd5e0;
      cursor: not-allowed;
    }
    
    @media (max-width: 768px) {
      .historial-modal {
        width: 95%;
        max-height: 95vh;
      }
      
      .historial-filtros {
        flex-direction: column;
      }
      
      .historial-filtros mat-form-field {
        width: 100%;
      }
      
      .timeline-icon {
        width: 40px;
        height: 40px;
        font-size: 16px;
      }
      
      .historial-timeline::before {
        left: 40px;
      }
      
      .timeline-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
      }
      
      .historial-paginacion {
        flex-direction: column;
        gap: 10px;
      }
    }
  `]
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
  
  paginacion = {
    pagina_actual: 1,
    total_paginas: 1,
    total: 0
  };
  
  filtros = {
    fecha_inicio: '',
    fecha_fin: '',
    tipo_usuario: '',
    accion: ''
  };
  
  ngOnInit(): void {
    this.cargarHistorial();
  }
  
  ngOnDestroy(): void {
    if (this.subscription) {
      this.subscription.unsubscribe();
    }
  }
  
  cargarHistorial(): void {
    this.loading = true;
    this.error = '';
    
    this.subscription = this.tecnicoService.getHistorialMaquina(
      this.idMaquina, 
      this.paginacion.pagina_actual, 
      20
    ).subscribe({
      next: (response) => {
        this.historial = response.historial;
        this.paginacion = response.paginacion;
        this.loading = false;
      },
      error: (err) => {
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
    this.filtros = {
      fecha_inicio: '',
      fecha_fin: '',
      tipo_usuario: '',
      accion: ''
    };
    this.cargarHistorial();
  }
  
  getIconoAccion(accion: string): string {
    const iconos: { [key: string]: string } = {
      'Registro': '➕',
      'Montaje': '🔧',
      'Comprobación': '✅',
      'Distribución': '🚚',
      'Mantenimiento': '⚙️',
      'Reparación': '🔨',
      'Retirada': '📤',
      'Ensamblaje': '🛠️',
      'Actualización': '🔄',
      'Cambio de estado': '📝'
    };
    return iconos[accion] || '📋';
  }
  
  getColorAccion(accion: string): string {
    const colores: { [key: string]: string } = {
      'Registro': '#4CAF50',
      'Montaje': '#2196F3',
      'Comprobación': '#9C27B0',
      'Distribución': '#FF9800',
      'Mantenimiento': '#FFC107',
      'Reparación': '#F44336',
      'Retirada': '#795548',
      'Ensamblaje': '#00BCD4',
      'Actualización': '#3F51B5',
      'Cambio de estado': '#607D8B'
    };
    return colores[accion] || '#9E9E9E';
  }
}