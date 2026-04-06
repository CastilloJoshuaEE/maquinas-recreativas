/**
 * @fileoverview Componente Header de Administración
 * @description Header común para paneles de administración
 * @component AdminHeaderComponent
 */

import { Component, Input, Output, EventEmitter, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterLink } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatMenuModule } from '@angular/material/menu';
import { MatBadgeModule } from '@angular/material/badge';
import { AuthService } from '@core/services/auth';
import { NotificationService } from '@core/services/notification';

@Component({
  selector: 'app-admin-header',
  standalone: true,
  imports: [CommonModule, RouterLink, MatButtonModule, MatIconModule, MatMenuModule, MatBadgeModule],
  templateUrl: './admin-header.html',
  styleUrls: ['./admin-header.css']
})
export class AdminHeaderComponent {
  private authService = inject(AuthService);
  private notificationService = inject(NotificationService);
  private router = inject(Router);
  
  @Input() showReportesButton = true;
  @Input() showChatButton = true;
  @Input() showNotificacionesButton = true;
  @Output() showReportes = new EventEmitter<boolean>();
  @Output() showChat = new EventEmitter<boolean>();
  
  currentUser = this.authService.getCurrentUser();
  unreadCount = 0;
  showNotifications = false;
  
  constructor() { this.loadUnreadCount(); }
  
  private loadUnreadCount(): void {
    if (this.currentUser?.ID_Usuario) {
      this.notificationService.getUnreadCount(this.currentUser.ID_Usuario).subscribe(count => this.unreadCount = count);
    }
  }
  
  verPerfil(): void { this.router.navigate(['/usuario/perfil']); }
  editarPerfil(): void { this.router.navigate(['/usuario/actualizar-perfil']); }
  verNotificaciones(): void { this.showNotifications = true; }
  
  cerrarSesion(): void {
    if (confirm('¿Está seguro de cerrar sesión?')) { this.authService.logout().subscribe(); }
  }
}