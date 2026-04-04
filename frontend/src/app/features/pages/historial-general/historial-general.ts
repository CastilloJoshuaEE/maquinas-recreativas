/**
 * @fileoverview Página de Historial General
 * @description Muestra el historial completo de actividades del sistema
 * @component HistorialGeneralComponent
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header.component';
import { HistorialMaquinaComponent } from '@features/tecnico/pages/ui/historial-maquina/historial-maquina';
import { AuthService } from '@core/services/auth.service';

@Component({
  selector: 'app-historial-general',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    AdminHeaderComponent,
    HistorialMaquinaComponent
  ],
  template: `
    <div class="historial-general-container">
      <app-admin-header></app-admin-header>
      
      <div class="historial-general-content">
        <div class="historial-header-section">
          <h1>
            <mat-icon>history</mat-icon>
            Historial General de Actividades
          </h1>
          <button mat-raised-button color="primary" class="ver-historial-btn" (click)="mostrarHistorial = true">
            <mat-icon>visibility</mat-icon>
            Ver Historial Completo
          </button>
        </div>

        <mat-card class="historial-info-card">
          <mat-card-content>
            <div class="info-icon">
              <mat-icon>info</mat-icon>
            </div>
            <h2>¿Qué puedes ver aquí?</h2>
            <p>
              Desde esta sección puedes ver todas las actividades realizadas en las máquinas recreativas,
              incluyendo montajes, comprobaciones, mantenimientos y cambios de estado.
            </p>
            
            <div class="info-grid">
              <div class="info-item">
                <mat-icon>handyman</mat-icon>
                <h3>Ensamblajes</h3>
                <p>Registros de montaje y ensamblaje de máquinas</p>
              </div>
              <div class="info-item">
                <mat-icon>check_circle</mat-icon>
                <h3>Comprobaciones</h3>
                <p>Controles de calidad y verificaciones</p>
              </div>
              <div class="info-item">
                <mat-icon>local_shipping</mat-icon>
                <h3>Distribuciones</h3>
                <p>Envíos y asignaciones a comercios</p>
              </div>
              <div class="info-item">
                <mat-icon>build</mat-icon>
                <h3>Mantenimientos</h3>
                <p>Reparaciones y mantenimiento correctivo</p>
              </div>
              <div class="info-item">
                <mat-icon>attach_money</mat-icon>
                <h3>Recaudaciones</h3>
                <p>Registros de ingresos por máquina</p>
              </div>
              <div class="info-item">
                <mat-icon>swap_horiz</mat-icon>
                <h3>Cambios de Estado</h3>
                <p>Transiciones entre estados operativos</p>
              </div>
            </div>
            
            <div class="nota" *ngIf="!esAdminOrLogistica">
              <mat-icon>warning</mat-icon>
              <p>Solo los administradores y personal de logística tienen acceso completo al historial.</p>
            </div>
          </mat-card-content>
        </mat-card>
        
        <div class="acceso-denegado" *ngIf="esDenegado">
          <mat-icon>lock</mat-icon>
          <h2>Acceso Denegado</h2>
          <p>No tienes permisos para ver esta página.</p>
          <button mat-raised-button color="primary" (click)="regresar()">
            <mat-icon>arrow_back</mat-icon>
            Volver al inicio
          </button>
        </div>
      </div>
      
      <app-historial-maquina
        *ngIf="mostrarHistorial"
        [nombreMaquina]="'General'"
        (onClose)="mostrarHistorial = false">
      </app-historial-maquina>
    </div>
  `,
  styles: [`
    .historial-general-container {
      min-height: 100vh;
      background: linear-gradient(135deg, #07224c 0%, #124258 50%, #3b4a66 100%);
    }
    
    .historial-general-content {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2rem;
    }
    
    .historial-header-section {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 12px;
      padding: 2rem;
      margin-bottom: 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 1rem;
    }
    
    .historial-header-section h1 {
      color: white;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 1.5rem;
    }
    
    .ver-historial-btn {
      background: linear-gradient(135deg, #4f6bed, #3d55c3);
    }
    
    .historial-info-card {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 12px;
      overflow: hidden;
    }
    
    .info-icon {
      text-align: center;
      margin-bottom: 1rem;
    }
    
    .info-icon mat-icon {
      font-size: 3rem;
      width: auto;
      height: auto;
      color: #4f6bed;
    }
    
    .historial-info-card h2 {
      text-align: center;
      color: #2c3e50;
      margin-bottom: 1rem;
    }
    
    .historial-info-card p {
      text-align: center;
      color: #666;
      margin-bottom: 2rem;
    }
    
    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1.5rem;
      margin: 2rem 0;
    }
    
    .info-item {
      text-align: center;
      padding: 1.5rem;
      background: #f8f9fa;
      border-radius: 12px;
      transition: transform 0.3s ease;
    }
    
    .info-item:hover {
      transform: translateY(-5px);
    }
    
    .info-item mat-icon {
      font-size: 2.5rem;
      width: auto;
      height: auto;
      color: #4f6bed;
      margin-bottom: 0.5rem;
    }
    
    .info-item h3 {
      color: #2c3e50;
      margin-bottom: 0.5rem;
    }
    
    .info-item p {
      color: #777;
      font-size: 0.85rem;
      margin: 0;
    }
    
    .nota {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 1rem;
      background: #fff3cd;
      border-radius: 8px;
      margin-top: 1rem;
    }
    
    .nota mat-icon {
      color: #856404;
    }
    
    .nota p {
      margin: 0;
      color: #856404;
      text-align: left;
    }
    
    .acceso-denegado {
      text-align: center;
      padding: 3rem;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 12px;
    }
    
    .acceso-denegado mat-icon {
      font-size: 4rem;
      width: auto;
      height: auto;
      color: #dc3545;
      margin-bottom: 1rem;
    }
    
    .acceso-denegado h2 {
      color: #dc3545;
      margin-bottom: 0.5rem;
    }
    
    .acceso-denegado p {
      color: #666;
      margin-bottom: 1.5rem;
    }
    
    @media (max-width: 768px) {
      .historial-general-content {
        padding: 1rem;
      }
      
      .historial-header-section {
        flex-direction: column;
        text-align: center;
      }
      
      .info-grid {
        grid-template-columns: 1fr;
      }
    }
  `]
})
export class HistorialGeneralComponent implements OnInit {
  private router = inject(Router);
  private authService = inject(AuthService);
  
  mostrarHistorial = false;
  esDenegado = false;
  esAdminOrLogistica = false;
  
  ngOnInit(): void {
    const currentUser = this.authService.getCurrentUser();
    if (!currentUser) {
      this.esDenegado = true;
      return;
    }
    
    this.esAdminOrLogistica = currentUser.tipo === 'Administrador' || currentUser.tipo === 'Logistica';
    
    if (!this.esAdminOrLogistica) {
      this.esDenegado = true;
    }
  }
  
  regresar(): void {
    const currentUser = this.authService.getCurrentUser();
    if (currentUser) {
      const userType = currentUser.tipo === 'Técnico' ? 'Tecnico' : currentUser.tipo;
      
      switch (userType) {
        case 'Logistica':
          this.router.navigate(['/logistica/dashboard']);
          break;
        case 'Tecnico':
          if (currentUser.Especialidad) {
            this.router.navigate([`/tecnico/${currentUser.Especialidad.toLowerCase()}`]);
          } else {
            this.router.navigate(['/tecnico/ensamblador']);
          }
          break;
        case 'Contabilidad':
          this.router.navigate(['/contabilidad/dashboard']);
          break;
        case 'Administrador':
          this.router.navigate(['/admin/dashboard']);
          break;
        default:
          this.router.navigate(['/auth/login']);
      }
    } else {
      this.router.navigate(['/auth/login']);
    }
  }
}