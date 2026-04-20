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
import { MatDividerModule } from '@angular/material/divider';
import { AuthService } from '@core/services/auth';
import { NotificationService } from '@core/services/notification';
import { HasRoleDirective } from '@shared/directives/has-role.directive';
import { NotificacionesListComponent } from '@shared/ui/notificaciones-list/notificaciones-list';
import { ChatComponent } from '@shared/ui/chat/chat';

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
    NotificacionesListComponent,
    ChatComponent
  ],
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
  showChatModal = false;
  
  constructor() { 
    this.loadUnreadCount(); 
  }
  
  private loadUnreadCount(): void {
    if (this.currentUser?.id) {
      this.notificationService.getUnreadCount(this.currentUser.id).subscribe(count => this.unreadCount = count);
    }
  }
  
  // Navegar a la página de reportes
  irAReportes(): void {
    this.router.navigate(['/reportes/gestion-reportes']);
  }
  
  // Abrir modal de chat
  abrirChat(): void {
    this.showChatModal = true;
    this.showChat.emit(true);
  }
  
  cerrarChatModal(): void {
    this.showChatModal = false;
    this.showChat.emit(false);
  }
  
  verPerfil(): void { 
    this.router.navigate(['/usuario/perfil']); 
  }
  
  editarPerfil(): void { 
    this.router.navigate(['/usuario/actualizar-perfil']); 
  }
  
  verNotificaciones(): void { 
    this.showNotifications = true; 
  }
  
  cerrarNotificaciones(): void {
    this.showNotifications = false;
  }
  
  cerrarSesion(): void {
    if (confirm('¿Está seguro de cerrar sesión?')) { 
      this.authService.logout().subscribe(); 
    }
  }
}