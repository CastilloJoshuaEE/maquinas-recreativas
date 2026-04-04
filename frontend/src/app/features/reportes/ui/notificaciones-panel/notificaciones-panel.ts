/**
 * @fileoverview Panel de Notificaciones
 * @description Componente para mostrar y gestionar notificaciones del usuario
 * @component NotificacionesPanelComponent
 */

import { Component, Input, Output, EventEmitter, OnInit, OnDestroy, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { ReportesService } from '../../services/reportes.service';
import { User } from '@core/models/user.model';
import { Subscription } from 'rxjs';

interface Notificacion {
  ID_Notificaciones: string;
  mensaje: string;
  fecha_hora: string;
  leida: number;
  ID_Reporte?: string;
  reporte_descripcion?: string;
  emisor_nombre?: string;
  emisor_apellido?: string;
  Tipo?: string;
  Nombre_Maquina?: string;
  NombreComercio?: string;
  DireccionComercio?: string;
}

@Component({
  selector: 'app-notificaciones-panel',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule
  ],
  template: `
    <div class="notificaciones-panel">
      <div class="panel-header">
        <h2>
          <mat-icon>notifications</mat-icon>
          Notificaciones
          <span class="unread-count" *ngIf="unreadCount > 0">{{ unreadCount }}</span>
        </h2>
        <button mat-button *ngIf="unreadCount > 0" (click)="marcarTodasLeidas()">
          Marcar todas como leídas
        </button>
      </div>
      
      <div *ngIf="cargando" class="loading-container">
        <mat-spinner diameter="40"></mat-spinner>
        <p>Cargando notificaciones...</p>
      </div>
      
      <div *ngIf="!cargando && error" class="error-message">
        {{ error }}
        <button mat-button (click)="cargarNotificaciones()">Reintentar</button>
      </div>
      
      <div *ngIf="!cargando && !error && notificaciones.length === 0" class="no-notificaciones">
        <mat-icon>notifications_off</mat-icon>
        <p>No tienes notificaciones</p>
      </div>
      
      <div *ngIf="!cargando && !error && notificaciones.length > 0" class="notificaciones-list">
        <div *ngFor="let notif of notificaciones" 
             class="notificacion-item" 
             [class.no-leida]="!notif.leida"
             (click)="marcarComoLeida(notif)">
          <div class="notificacion-icon">
            <mat-icon [ngClass]="getIconoClass(notif.Tipo)">{{ getIcono(notif.Tipo) }}</mat-icon>
          </div>
          <div class="notificacion-content">
            <div class="notificacion-mensaje">
              <strong *ngIf="notif.emisor_nombre">
                {{ notif.emisor_nombre }} {{ notif.emisor_apellido }}:
              </strong>
              {{ notif.mensaje }}
            </div>
            <div class="notificacion-detalles" *ngIf="notif.Nombre_Maquina">
              <span class="detalle">
                <mat-icon>videogame_asset</mat-icon>
                {{ notif.Nombre_Maquina }}
              </span>
              <span class="detalle" *ngIf="notif.NombreComercio">
                <mat-icon>store</mat-icon>
                {{ notif.NombreComercio }}
              </span>
            </div>
            <div class="notificacion-footer">
              <span class="fecha">{{ notif.fecha_hora | date:'dd/MM/yyyy HH:mm' }}</span>
              <button mat-button *ngIf="notif.ID_Reporte" (click)="verReporte(notif.ID_Reporte); $event.stopPropagation()">
                Ver chat relacionado
              </button>
            </div>
          </div>
          <button class="marcar-leida" *ngIf="!notif.leida" (click)="marcarComoLeida(notif); $event.stopPropagation()">
            <mat-icon>done</mat-icon>
          </button>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .notificaciones-panel {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      max-height: 500px;
      display: flex;
      flex-direction: column;
    }
    
    .panel-header {
      padding: 1rem 1.5rem;
      background: linear-gradient(135deg, #4f6bed, #3d55c3);
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 1rem;
    }
    
    .panel-header h2 {
      margin: 0;
      color: white;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 1.2rem;
    }
    
    .unread-count {
      background: #ff4757;
      color: white;
      border-radius: 20px;
      padding: 2px 8px;
      font-size: 0.75rem;
      font-weight: bold;
    }
    
    .loading-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }
    
    .error-message {
      padding: 1rem;
      background: #f8d7da;
      color: #721c24;
      text-align: center;
    }
    
    .no-notificaciones {
      text-align: center;
      padding: 2rem;
      color: #999;
    }
    
    .no-notificaciones mat-icon {
      font-size: 3rem;
      width: auto;
      height: auto;
      margin-bottom: 0.5rem;
    }
    
    .notificaciones-list {
      overflow-y: auto;
      flex: 1;
    }
    
    .notificacion-item {
      display: flex;
      padding: 1rem;
      border-bottom: 1px solid #e0e0e0;
      cursor: pointer;
      transition: background 0.2s;
      position: relative;
    }
    
    .notificacion-item:hover {
      background: #f8f9fa;
    }
    
    .notificacion-item.no-leida {
      background: #e3f2fd;
    }
    
    .notificacion-item.no-leida:hover {
      background: #d1e7fd;
    }
    
    .notificacion-icon {
      margin-right: 1rem;
    }
    
    .notificacion-icon mat-icon {
      font-size: 1.5rem;
      width: auto;
      height: auto;
    }
    
    .notificacion-content {
      flex: 1;
    }
    
    .notificacion-mensaje {
      margin-bottom: 0.5rem;
      color: #333;
    }
    
    .notificacion-detalles {
      display: flex;
      gap: 1rem;
      margin-bottom: 0.5rem;
      font-size: 0.8rem;
      color: #666;
    }
    
    .detalle {
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
    }
    
    .detalle mat-icon {
      font-size: 0.9rem;
      width: auto;
      height: auto;
    }
    
    .notificacion-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.7rem;
      color: #999;
    }
    
    .marcar-leida {
      background: none;
      border: none;
      cursor: pointer;
      color: #999;
      padding: 0 0.5rem;
    }
    
    .marcar-leida:hover {
      color: #4f6bed;
    }
    
    @media (max-width: 768px) {
      .panel-header {
        flex-direction: column;
        align-items: stretch;
      }
      
      .notificacion-item {
        flex-direction: column;
      }
      
      .notificacion-icon {
        margin-bottom: 0.5rem;
      }
      
      .marcar-leida {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
      }
    }
  `]
})
export class NotificacionesPanelComponent implements OnInit, OnDestroy {
  @Input() currentUser: User | null = null;
  @Output() onClose = new EventEmitter<void>();
  
  private router = inject(Router);
  private reportesService = inject(ReportesService);
  private snackBar = inject(MatSnackBar);
  
  notificaciones: Notificacion[] = [];
  unreadCount = 0;
  cargando = false;
  error = '';
  
  private subscriptions: Subscription[] = [];
  private refreshInterval: any;
  
  ngOnInit(): void {
    this.cargarNotificaciones();
    
    // Refrescar cada 30 segundos
    this.refreshInterval = setInterval(() => {
      this.cargarNotificaciones();
    }, 30000);
  }
  
  ngOnDestroy(): void {
    if (this.refreshInterval) {
      clearInterval(this.refreshInterval);
    }
    this.subscriptions.forEach(sub => sub.unsubscribe());
  }
  
  cargarNotificaciones(): void {
    if (!this.currentUser?.ID_Usuario) return;
    
    this.cargando = true;
    this.error = '';
    
    this.reportesService.getNotificaciones(this.currentUser.ID_Usuario).subscribe({
      next: (notificaciones) => {
        this.notificaciones = notificaciones;
        this.unreadCount = notificaciones.filter(n => !n.leida).length;
        this.cargando = false;
      },
      error: (err) => {
        this.error = err.message || 'Error al cargar notificaciones';
        this.cargando = false;
      }
    });
  }
  
  marcarComoLeida(notificacion: Notificacion): void {
    if (notificacion.leida) return;
    
    this.reportesService.marcarNotificacionLeida(notificacion.ID_Notificaciones).subscribe({
      next: (success) => {
        if (success) {
          notificacion.leida = 1;
          this.unreadCount = Math.max(this.unreadCount - 1, 0);
        }
      },
      error: () => {
        this.snackBar.error('Error al marcar notificación', 'Cerrar');
      }
    });
  }
  
  marcarTodasLeidas(): void {
    this.reportesService.marcarTodasNotificacionesLeidas().subscribe({
      next: (success) => {
        if (success) {
          this.notificaciones.forEach(n => n.leida = 1);
          this.unreadCount = 0;
          this.snackBar.success('Todas las notificaciones marcadas como leídas', 'Éxito');
        } else {
          this.snackBar.error('Error al marcar notificaciones', 'Cerrar');
        }
      },
      error: () => {
        this.snackBar.error('Error al marcar notificaciones', 'Cerrar');
      }
    });
  }
  
  verReporte(reporteId: string): void {
    this.router.navigate(['/reportes/chat', reporteId]);
  }
  
  getIcono(tipo: string): string {
    const iconos: { [key: string]: string } = {
      'Reporte': 'report_problem',
      'Mensaje': 'chat',
      'Mantenimiento': 'build',
      'Distribucion': 'local_shipping',
      'Recaudacion': 'attach_money'
    };
    return iconos[tipo || ''] || 'notifications';
  }
  
  getIconoClass(tipo: string): string {
    const clases: { [key: string]: string } = {
      'Reporte': 'icon-reporte',
      'Mensaje': 'icon-mensaje',
      'Mantenimiento': 'icon-mantenimiento',
      'Distribucion': 'icon-distribucion',
      'Recaudacion': 'icon-recaudacion'
    };
    return clases[tipo || ''] || '';
  }
}