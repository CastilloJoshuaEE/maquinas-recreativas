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
import { ReportesService } from '../../services/reportes';
import { User } from '@core/models/user.model';

interface Notificacion {
  ID_Notificaciones: string; mensaje: string; fecha_hora: string; leida: number;
  ID_Reporte?: string; reporte_descripcion?: string; emisor_nombre?: string;
  emisor_apellido?: string; Tipo?: string; Nombre_Maquina?: string; NombreComercio?: string;
}

@Component({
  selector: 'app-notificaciones-panel',
  standalone: true,
  imports: [CommonModule, MatCardModule, MatButtonModule, MatIconModule, MatProgressSpinnerModule],
  templateUrl: './notificaciones-panel.html',
  styleUrls: ['./notificaciones-panel.css']
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
  private refreshInterval: any;
  
  ngOnInit(): void { this.cargarNotificaciones(); this.refreshInterval = setInterval(() => this.cargarNotificaciones(), 30000); }
  ngOnDestroy(): void { if (this.refreshInterval) clearInterval(this.refreshInterval); }
  
  cargarNotificaciones(): void {
    if (!this.currentUser?.ID_Usuario) return;
    this.cargando = true;
    this.error = '';
    this.reportesService.getNotificaciones(this.currentUser.ID_Usuario).subscribe({
      next: (notificaciones) => { this.notificaciones = notificaciones; this.unreadCount = notificaciones.filter(n => !n.leida).length; this.cargando = false; },
      error: (err) => { this.error = err.message || 'Error al cargar notificaciones'; this.cargando = false; }
    });
  }
  
  marcarComoLeida(notificacion: Notificacion): void {
    if (notificacion.leida) return;
    this.reportesService.marcarNotificacionLeida(notificacion.ID_Notificaciones).subscribe({
      next: (success) => { if (success) { notificacion.leida = 1; this.unreadCount = Math.max(this.unreadCount - 1, 0); } },
      error: () => { this.snackBar.error('Error al marcar notificación', 'Cerrar'); }
    });
  }
  
  marcarTodasLeidas(): void {
    this.reportesService.marcarTodasNotificacionesLeidas().subscribe({
      next: (success) => {
        if (success) { this.notificaciones.forEach(n => n.leida = 1); this.unreadCount = 0; this.snackBar.success('Todas las notificaciones marcadas como leídas', 'Éxito'); }
        else this.snackBar.error('Error al marcar notificaciones', 'Cerrar');
      },
      error: () => { this.snackBar.error('Error al marcar notificaciones', 'Cerrar'); }
    });
  }
  
  verReporte(reporteId: string): void { this.router.navigate(['/reportes/chat', reporteId]); }
  
  getIcono(tipo: string): string {
    const iconos: { [key: string]: string } = { 'Reporte': 'report_problem', 'Mensaje': 'chat', 'Mantenimiento': 'build', 'Distribucion': 'local_shipping', 'Recaudacion': 'attach_money' };
    return iconos[tipo || ''] || 'notifications';
  }
  
  getIconoClass(tipo: string): string {
    const clases: { [key: string]: string } = { 'Reporte': 'icon-reporte', 'Mensaje': 'icon-mensaje', 'Mantenimiento': 'icon-mantenimiento', 'Distribucion': 'icon-distribucion', 'Recaudacion': 'icon-recaudacion' };
    return clases[tipo || ''] || '';
  }
}