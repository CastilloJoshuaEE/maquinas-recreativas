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
import { AuthService } from '@core/services/auth.service';
import { NotificationService } from '@core/services/notification.service';

/**
 * Header administrativo con menú de perfil y controles
 */
@Component({
  selector: 'app-admin-header',
  standalone: true,
  imports: [
    CommonModule,
    RouterLink,
    MatButtonModule,
    MatIconModule,
    MatMenuModule,
    MatBadgeModule
  ],
  template: `
    <header class="admin-header">
      <div class="header-left">
        <h1 class="titulo-header">Bienvenido</h1>
      </div>
      
      <div class="header-controls">
        <!-- Botones de control adicionales -->
        <ng-content select="[header-buttons]"></ng-content>
      </div>
      
      <div class="profile-section">
        <button 
          mat-button 
          class="profile-button"
          [matMenuTriggerFor]="menu"
          aria-label="Menú de perfil"
        >
          <mat-icon>account_circle</mat-icon>
          <span>{{ currentUser?.usuario_asignado || 'Usuario' }}</span>
          <mat-icon class="profile-arrow">arrow_drop_down</mat-icon>
        </button>
        
        <mat-menu #menu="matMenu">
          <button mat-menu-item (click)="verPerfil()">
            <mat-icon>person</mat-icon>
            <span>Ver Perfil</span>
          </button>
          <button mat-menu-item (click)="editarPerfil()">
            <mat-icon>edit</mat-icon>
            <span>Editar Perfil</span>
          </button>
          <button mat-menu-item (click)="verNotificaciones()">
            <mat-icon [matBadge]="unreadCount" matBadgeColor="warn" matBadgeOverlap="false">notifications</mat-icon>
            <span>Notificaciones</span>
          </button>
          <mat-divider></mat-divider>
          <button mat-menu-item (click)="cerrarSesion()" class="logout-button">
            <mat-icon>exit_to_app</mat-icon>
            <span>Cerrar Sesión</span>
          </button>
        </mat-menu>
      </div>
    </header>
    
    <!-- Panel de notificaciones -->
    <div *ngIf="showNotifications" class="modal-panel">
      <app-notificaciones-list 
        [currentUser]="currentUser"
        (close)="showNotifications = false">
      </app-notificaciones-list>
    </div>
  `,
  styles: [`
    .admin-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 2rem;
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      position: sticky;
      top: 0;
      z-index: 1000;
    }
    
    .titulo-header {
      color: white;
      font-size: 1.5rem;
      margin: 0;
    }
    
    .header-controls {
      display: flex;
      gap: 1rem;
    }
    
    .profile-button {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      color: white;
      background: rgba(255, 255, 255, 0.15);
      border-radius: 12px;
      padding: 0.5rem 1rem;
    }
    
    .profile-button:hover {
      background: rgba(255, 255, 255, 0.25);
    }
    
    .profile-arrow {
      font-size: 1.2rem;
    }
    
    .logout-button {
      color: #dc3545 !important;
    }
    
    .modal-panel {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: white;
      padding: 0;
      border-radius: 12px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
      z-index: 1100;
      max-width: 90%;
      max-height: 90vh;
      overflow-y: auto;
      width: 800px;
    }
  `]
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
  
  constructor() {
    this.loadUnreadCount();
  }
  
  private loadUnreadCount(): void {
    if (this.currentUser?.ID_Usuario) {
      this.notificationService.getUnreadCount(this.currentUser.ID_Usuario).subscribe(
        count => this.unreadCount = count
      );
    }
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
  
  cerrarSesion(): void {
    if (confirm('¿Está seguro de cerrar sesión?')) {
      this.authService.logout().subscribe();
    }
  }
}