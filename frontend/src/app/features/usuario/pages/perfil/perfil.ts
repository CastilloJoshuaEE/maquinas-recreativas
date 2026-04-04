/**
 * @fileoverview Componente de Perfil de Usuario
 * @description Muestra la información del perfil del usuario actual
 * @component PerfilComponent
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterLink } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header.component';
import { AuthService } from '@core/services/auth.service';
import { UserService } from '@core/services/user.service';
import { User } from '@core/models/user.model';

@Component({
  selector: 'app-perfil',
  standalone: true,
  imports: [
    CommonModule,
    RouterLink,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    AdminHeaderComponent
  ],
  template: `
    <div class="perfil-container">
      <app-admin-header></app-admin-header>
      
      <div class="content-wrapper">
        <div class="page-header">
          <button mat-icon-button (click)="regresar()" class="back-button">
            <mat-icon>arrow_back</mat-icon>
          </button>
          <h2>Mi Perfil</h2>
        </div>

        <div *ngIf="loading" class="loading-container">
          <mat-spinner diameter="40"></mat-spinner>
          <p>Cargando perfil...</p>
        </div>

        <div *ngIf="error" class="error-message">
          {{ error }}
          <button mat-button (click)="cargarPerfil()">Reintentar</button>
        </div>

        <mat-card *ngIf="!loading && !error && usuario" class="perfil-card">
          <mat-card-header>
            <mat-card-title>
              <mat-icon>account_circle</mat-icon>
              Datos Personales
            </mat-card-title>
          </mat-card-header>
          
          <mat-card-content>
            <div class="perfil-info">
              <div class="info-row">
                <span class="info-label">Cédula:</span>
                <span class="info-value">{{ usuario.ci }}</span>
              </div>
              
              <div class="info-row">
                <span class="info-label">Nombre:</span>
                <span class="info-value">{{ usuario.nombre }} {{ usuario.apellido }}</span>
              </div>
              
              <div class="info-row">
                <span class="info-label">Correo electrónico:</span>
                <span class="info-value">{{ usuario.email }}</span>
              </div>
              
              <div class="info-row">
                <span class="info-label">Usuario asignado:</span>
                <span class="info-value">{{ usuario.usuario_asignado }}</span>
              </div>
              
              <div class="info-row">
                <span class="info-label">Función del sistema:</span>
                <span class="info-value">
                  <span class="tipo-badge" [ngClass]="{
                    'tipo-admin': usuario.tipo === 'Administrador',
                    'tipo-contabilidad': usuario.tipo === 'Contabilidad',
                    'tipo-logistica': usuario.tipo === 'Logistica',
                    'tipo-tecnico': usuario.tipo === 'Tecnico'
                  }">
                    {{ usuario.tipo }}
                  </span>
                </span>
              </div>
              
              <div class="info-row" *ngIf="usuario.Especialidad">
                <span class="info-label">Especialidad:</span>
                <span class="info-value">{{ usuario.Especialidad }}</span>
              </div>
              
              <div class="info-row">
                <span class="info-label">Estado:</span>
                <span class="info-value">
                  <span class="estado-badge" [ngClass]="{
                    'estado-activo': usuario.estado === 'Activo',
                    'estado-inhabilitado': usuario.estado === 'Inhabilitado',
                    'estado-pendiente': usuario.estado === 'Pendiente de asignacion'
                  }">
                    {{ usuario.estado }}
                  </span>
                </span>
              </div>
            </div>
          </mat-card-content>
          
          <mat-card-actions>
            <button mat-raised-button color="primary" (click)="editarPerfil()">
              <mat-icon>edit</mat-icon>
              Editar Perfil
            </button>
            <button mat-raised-button color="warn" (click)="cerrarSesion()">
              <mat-icon>exit_to_app</mat-icon>
              Cerrar Sesión
            </button>
          </mat-card-actions>
        </mat-card>
      </div>
    </div>
  `,
  styles: [`
    .perfil-container {
      min-height: 100vh;
      background: linear-gradient(135deg, #07224c 0%, #124258 50%, #3b4a66 100%);
    }
    
    .content-wrapper {
      max-width: 600px;
      margin: 0 auto;
      padding: 2rem;
    }
    
    .page-header {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 2rem;
    }
    
    .page-header h2 {
      color: white;
      margin: 0;
    }
    
    .back-button {
      color: white;
      background: rgba(255, 255, 255, 0.15);
    }
    
    .perfil-card {
      background: white;
      border-radius: 12px;
      overflow: hidden;
    }
    
    .perfil-card mat-card-title {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 1.2rem;
      color: #2c3e50;
    }
    
    .perfil-info {
      padding: 0.5rem 0;
    }
    
    .info-row {
      display: flex;
      padding: 0.75rem 0;
      border-bottom: 1px solid #f0f0f0;
    }
    
    .info-row:last-child {
      border-bottom: none;
    }
    
    .info-label {
      width: 180px;
      font-weight: 600;
      color: #555;
    }
    
    .info-value {
      flex: 1;
      color: #333;
    }
    
    .tipo-badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
    }
    
    .tipo-admin {
      background: #cce5ff;
      color: #004085;
    }
    
    .tipo-contabilidad {
      background: #d4edda;
      color: #155724;
    }
    
    .tipo-logistica {
      background: #d1ecf1;
      color: #0c5460;
    }
    
    .tipo-tecnico {
      background: #e2d5f1;
      color: #4a1d6d;
    }
    
    .estado-badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
    }
    
    .estado-activo {
      background: #d4edda;
      color: #155724;
    }
    
    .estado-inhabilitado {
      background: #f8d7da;
      color: #721c24;
    }
    
    .estado-pendiente {
      background: #fff3cd;
      color: #856404;
    }
    
    mat-card-actions {
      padding: 1rem;
      display: flex;
      gap: 1rem;
      border-top: 1px solid #e0e0e0;
    }
    
    .loading-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 3rem;
      background: white;
      border-radius: 12px;
    }
    
    .error-message {
      padding: 1rem;
      background: #f8d7da;
      color: #721c24;
      border-radius: 8px;
      margin-bottom: 1rem;
      text-align: center;
    }
    
    @media (max-width: 768px) {
      .content-wrapper {
        padding: 1rem;
      }
      
      .info-row {
        flex-direction: column;
        gap: 0.25rem;
      }
      
      .info-label {
        width: 100%;
      }
      
      mat-card-actions {
        flex-direction: column;
      }
      
      mat-card-actions button {
        width: 100%;
      }
    }
  `]
})
export class PerfilComponent implements OnInit {
  private router = inject(Router);
  private authService = inject(AuthService);
  private userService = inject(UserService);
  private snackBar = inject(MatSnackBar);
  
  usuario: User | null = null;
  loading = true;
  error = '';
  
  ngOnInit(): void {
    this.cargarPerfil();
  }
  
  cargarPerfil(): void {
    this.loading = true;
    this.error = '';
    
    const currentUser = this.authService.getCurrentUser();
    if (!currentUser || !currentUser.ID_Usuario) {
      this.error = 'Usuario no autenticado';
      this.loading = false;
      return;
    }
    
    this.userService.getProfile(currentUser.ID_Usuario).subscribe({
      next: (usuario) => {
        if (usuario) {
          this.usuario = usuario;
          this.registrarActividad();
        } else {
          this.error = 'No se encontró el perfil del usuario';
        }
        this.loading = false;
      },
      error: (err) => {
        this.error = err.message || 'Error al cargar el perfil';
        this.loading = false;
      }
    });
  }
  
  private registrarActividad(): void {
    const currentUser = this.authService.getCurrentUser();
    if (currentUser?.ID_Usuario) {
      this.userService.registrarActividad(
        currentUser.ID_Usuario,
        'El usuario visualizó su perfil'
      ).subscribe();
    }
  }
  
  editarPerfil(): void {
    this.router.navigate(['/usuario/actualizar-perfil']);
  }
  
  cerrarSesion(): void {
    if (confirm('¿Está seguro de cerrar sesión?')) {
      this.authService.logout().subscribe({
        next: () => {
          this.snackBar.open('Sesión cerrada correctamente', 'Cerrar', { duration: 3000 });
        },
        error: () => {
          this.snackBar.open('Error al cerrar sesión', 'Cerrar', { duration: 3000 });
        }
      });
    }
  }
  
  regresar(): void {
    const user = this.authService.getCurrentUser();
    if (user) {
      const userType = user.tipo === 'Técnico' ? 'Tecnico' : user.tipo;
      
      switch (userType) {
        case 'Logistica':
          this.router.navigate(['/logistica/dashboard']);
          break;
        case 'Tecnico':
          if (user.Especialidad) {
            this.router.navigate([`/tecnico/${user.Especialidad.toLowerCase()}`]);
          } else {
            this.router.navigate(['/tecnico/dashboard']);
          }
          break;
        case 'Contabilidad':
          this.router.navigate(['/contabilidad/dashboard']);
          break;
        case 'Administrador':
          this.router.navigate(['/admin/dashboard']);
          break;
        default:
          this.router.navigate(['/']);
      }
    } else {
      this.router.navigate(['/']);
    }
  }
}