/**
 * @fileoverview Dashboard de Administrador
 * @description Panel principal del administrador con acceso a todas las funcionalidades
 * @component DashboardAdminComponent
 */

import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatGridListModule } from '@angular/material/grid-list';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header.component';
import { GestionReportesComponent } from '@features/reportes/ui/gestion-reportes/gestion-reportes.component';
import { NotificacionesPanelComponent } from '@features/reportes/ui/notificaciones-panel/notificaciones-panel.component';
import { ChatUsuariosComponent } from '@features/reportes/ui/chat-usuarios/chat-usuarios.component';
import { AuthService } from '@core/services/auth.service';

@Component({
  selector: 'app-dashboard-admin',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatGridListModule,
    AdminHeaderComponent,
    GestionReportesComponent,
    NotificacionesPanelComponent,
    ChatUsuariosComponent
  ],
  template: `
    <div class="dashboard-container">
      <app-admin-header></app-admin-header>
      
      <div class="admin-content">
        <div class="admin-cards-container">
          <mat-card class="admin-card" (click)="irAGestionUsuarios()">
            <mat-card-content>
              <div class="card-icon">👥</div>
              <h3>Gestión de Usuarios</h3>
              <p>Administrar usuarios del sistema</p>
            </mat-card-content>
          </mat-card>
          
          <mat-card class="admin-card" (click)="toggleReportes()">
            <mat-card-content>
              <div class="card-icon">📋</div>
              <h3>Gestionar Reportes</h3>
              <p>Ver y gestionar reportes</p>
            </mat-card-content>
          </mat-card>
          
          <mat-card class="admin-card" (click)="toggleNotificaciones()">
            <mat-card-content>
              <div class="card-icon">🔔</div>
              <h3>Notificaciones</h3>
              <p>Ver notificaciones del sistema</p>
            </mat-card-content>
          </mat-card>
          
          <mat-card class="admin-card" (click)="toggleChat()">
            <mat-card-content>
              <div class="card-icon">💬</div>
              <h3>Chat de Usuarios</h3>
              <p>Comunicación con usuarios</p>
            </mat-card-content>
          </mat-card>
        </div>
        
        <!-- Paneles modales -->
        <div *ngIf="showReportes" class="modal-panel">
          <app-gestion-reportes [currentUser]="currentUser" (close)="showReportes = false"></app-gestion-reportes>
        </div>
        
        <div *ngIf="showNotificaciones" class="modal-panel">
          <app-notificaciones-panel [currentUser]="currentUser" (close)="showNotificaciones = false"></app-notificaciones-panel>
        </div>
        
        <div *ngIf="showChat" class="modal-panel">
          <app-chat-usuarios [currentUser]="currentUser" [asPanel]="true" (close)="showChat = false"></app-chat-usuarios>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .dashboard-container {
      min-height: 100vh;
      background: linear-gradient(135deg, #07224c 0%, #124258 50%, #3b4a66 100%);
    }
    
    .admin-content {
      padding: 2rem;
    }
    
    .admin-cards-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1.5rem;
      max-width: 1200px;
      margin: 0 auto;
    }
    
    .admin-card {
      cursor: pointer;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      text-align: center;
      background: linear-gradient(135deg, #05d3cc, #063c60);
      color: white;
    }
    
    .admin-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
    }
    
    .card-icon {
      font-size: 4rem;
      margin-bottom: 1rem;
    }
    
    .admin-card h3 {
      color: white;
      margin-bottom: 0.5rem;
    }
    
    .admin-card p {
      color: rgba(255, 255, 255, 0.8);
    }
    
    .modal-panel {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: white;
      border-radius: 12px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
      z-index: 1100;
      max-width: 90%;
      max-height: 90vh;
      overflow-y: auto;
      width: 800px;
    }
    
    @media (max-width: 768px) {
      .admin-cards-container {
        grid-template-columns: 1fr;
      }
      
      .admin-content {
        padding: 1rem;
      }
    }
  `]
})
export class DashboardAdminComponent {
  private router = inject(Router);
  private authService = inject(AuthService);
  
  currentUser = this.authService.getCurrentUser();
  showReportes = false;
  showNotificaciones = false;
  showChat = false;
  
  irAGestionUsuarios(): void {
    this.router.navigate(['/admin/gestion-usuarios']);
  }
  
  toggleReportes(): void {
    this.showReportes = !this.showReportes;
    this.showNotificaciones = false;
    this.showChat = false;
  }
  
  toggleNotificaciones(): void {
    this.showNotificaciones = !this.showNotificaciones;
    this.showReportes = false;
    this.showChat = false;
  }
  
  toggleChat(): void {
    this.showChat = !this.showChat;
    this.showReportes = false;
    this.showNotificaciones = false;
  }
}