/**
 * @fileoverview Componente Header de Administración
 * @description Header común para paneles de administración
 * @component AdminHeaderComponent
 *
 *
 */

import { Component, Input, Output, EventEmitter, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatMenuModule } from '@angular/material/menu';
import { MatBadgeModule } from '@angular/material/badge';
import { MatDividerModule } from '@angular/material/divider';
import { AuthService } from '@core/services/auth';
import { NotificationService } from '@core/services/notification';
import { NotificacionMaquinaService } from '@core/services/notification-maquina';
import { NotificacionesPanelComponent } from '@features/reportes/ui/notificaciones-panel/notificaciones-panel';
import { ChatComponent } from '@shared/ui/chat/chat';
import { forkJoin } from 'rxjs';
import { HasRoleDirective } from '@shared/directives/has-role.directive';
@Component({
  selector: 'app-admin-header',
  standalone: true,
  imports: [
    CommonModule,
    MatDividerModule,
    MatButtonModule,
    MatIconModule,
    MatMenuModule,
    MatBadgeModule,
    HasRoleDirective,
    NotificacionesPanelComponent,
    ChatComponent
  ],
  templateUrl: './admin-header.html',
  styleUrls: ['./admin-header.css']
})
export class AdminHeaderComponent implements OnInit {
  private authService                = inject(AuthService);
  private notificationService        = inject(NotificationService);
  private notificacionMaquinaService = inject(NotificacionMaquinaService);
  private router                     = inject(Router);

  @Input() showReportesButton     = true;
  @Input() showChatButton         = true;
  @Input() showNotificacionesButton = true;
  @Output() showReportes = new EventEmitter<boolean>();
  @Output() showChat     = new EventEmitter<boolean>();

  currentUser       = this.authService.getCurrentUser();
  unreadCount       = 0;
  showNotifications = false;
  showChatModal     = false;

  ngOnInit(): void {
    this.loadUnreadCount();
  }

  /**
   * Carga el total de notificaciones no leídas (reportes + máquinas).
   * Se suman ambos conteos para el badge del botón.
   */
  private loadUnreadCount(): void {
    if (!this.currentUser?.id) return;

    forkJoin({
      reportes: this.notificationService.getUnreadCount(this.currentUser.id),
      maquinas: this.notificacionMaquinaService.getNotificacionesMaquina(this.currentUser.id)
    }).subscribe({
      next: ({ reportes, maquinas }) => {
        const noLeidasMaquinas = maquinas.filter((n: any) => n.Estado !== 'Leido').length;
        this.unreadCount = (reportes || 0) + noLeidasMaquinas;
      },
      error: () => {
        // Si falla, intentar solo con reportes
        this.notificationService.getUnreadCount(this.currentUser!.id)
          .subscribe(count => this.unreadCount = count);
      }
    });
  }

  irAReportes(): void {
    this.router.navigate(['/reportes/gestion-reportes']);
  }

  abrirChat(): void {
    this.showChatModal = true;
    this.showChat.emit(true);
  }

  cerrarChatModal(): void {
    this.showChatModal = false;
    this.showChat.emit(false);
  }

  verPerfil(): void    { this.router.navigate(['/usuario/perfil']); }
  editarPerfil(): void { this.router.navigate(['/usuario/actualizar-perfil']); }

  verNotificaciones(): void   { this.showNotifications = true; }
  cerrarNotificaciones(): void {
    this.showNotifications = false;
    // Recargar el badge al cerrar para reflejar los cambios
    this.loadUnreadCount();
  }

  cerrarSesion(): void {
    if (confirm('¿Está seguro de cerrar sesión?')) {
      this.authService.logout().subscribe();
    }
  }
}