/**
 * @fileoverview Componente de Lista de Notificaciones
 * @description Muestra una lista colapsable de notificaciones del usuario
 * @component NotificacionesListComponent
 */

import { Component, Input, Output, EventEmitter, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { NotificationService } from '@core/services/notification';
import { User } from '@core/models/user.model';

@Component({
  selector: 'app-notificaciones-list',
  standalone: true,
  imports: [CommonModule, MatButtonModule, MatIconModule, MatProgressSpinnerModule],
  templateUrl: './notificaciones-list.html',
  styleUrls: ['./notificaciones-list.css']
})
export class NotificacionesListComponent implements OnInit {
  @Input() user: User | null = null;
  @Input() emptyMessage: string = 'No hay notificaciones...';
  
  @Output() onClose = new EventEmitter<void>();
  
  private notificationService = inject(NotificationService);
  private router = inject(Router);
  
  notificaciones: any[] = [];
  noLeidas: number = 0;
  mostrarNotificaciones: boolean = false;
  cargando: boolean = false;
  
  ngOnInit(): void {
    if (this.user?.ID_Usuario) {
      this.cargarNotificaciones();
    }
  }
  
  cargarNotificaciones(): void {
    this.cargando = true;
    this.notificationService.getMaquinaNotifications(this.user!.ID_Usuario).subscribe({
      next: (notifs) => {
        this.notificaciones = notifs;
        this.noLeidas = notifs.filter(n => !n.leida).length;
        this.cargando = false;
      },
      error: () => {
        this.cargando = false;
      }
    });
  }
  
  toggleMostrar(): void {
    this.mostrarNotificaciones = !this.mostrarNotificaciones;
    if (this.mostrarNotificaciones && this.notificaciones.length === 0) {
      this.cargarNotificaciones();
    }
  }
  
  marcarComoLeida(id: string): void {
    this.notificationService.markAsRead(id).subscribe({
      next: () => {
        const notif = this.notificaciones.find(n => n.ID_Notificaciones === id);
        if (notif && !notif.leida) {
          notif.leida = 1;
          this.noLeidas = Math.max(this.noLeidas - 1, 0);
        }
      },
      error: () => {}
    });
  }
  
  verReporte(reporteId: string): void {
    this.router.navigate(['/reportes/chat', reporteId]);
    this.onClose.emit();
  }
  
  cerrar(): void {
    this.onClose.emit();
  }
}