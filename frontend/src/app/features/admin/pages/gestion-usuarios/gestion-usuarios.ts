/**
 * @fileoverview Gestión de Usuarios
 * @description Página principal para la gestión de usuarios del sistema
 * @component GestionUsuariosComponent
 */

import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header.component';
import { UserService } from '@core/services/user.service';

@Component({
  selector: 'app-gestion-usuarios',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatButtonModule,
    AdminHeaderComponent
  ],
  template: `
    <div class="gestion-usuarios-container">
      <app-admin-header></app-admin-header>
      
      <div class="perfil-contenedor">
        <button mat-raised-button (click)="regresar()" class="btn-regresar">
          Regresar
        </button>
        
        <h2 class="gestion-title">Gestión de usuarios</h2>
        
        <div class="card-buttons-container">
          <div class="card-button" (click)="irARegistrarUsuario()">
            <span class="icono-card">👤</span>
            <h3>Registrar Usuario</h3>
            <p>Crear un nuevo usuario en el sistema</p>
          </div>
          
          <div class="card-button" (click)="irAConsultarUsuarios()">
            <span class="icono-card">🗂️</span>
            <h3>Consultar Usuarios</h3>
            <p>Consultar, actualizar y eliminar usuarios existentes</p>
          </div>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .gestion-usuarios-container {
      min-height: 100vh;
      background: linear-gradient(135deg, #07224c 0%, #124258 50%, #3b4a66 100%);
    }
    
    .perfil-contenedor {
      padding: 2rem;
      text-align: center;
    }
    
    .btn-regresar {
      margin-bottom: 2rem;
      background: rgba(255, 255, 255, 0.15);
      color: white;
    }
    
    .gestion-title {
      color: white;
      font-size: 2.5rem;
      margin-bottom: 3rem;
    }
    
    .card-buttons-container {
      display: flex;
      justify-content: center;
      gap: 2rem;
      flex-wrap: wrap;
      max-width: 800px;
      margin: 0 auto;
    }
    
    .card-button {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(10px);
      border-radius: 12px;
      padding: 2rem;
      text-align: center;
      width: 250px;
      cursor: pointer;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      color: white;
    }
    
    .card-button:hover {
      transform: translateY(-5px);
      background: rgba(255, 255, 255, 0.25);
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }
    
    .icono-card {
      font-size: 3rem;
      display: block;
      margin-bottom: 1rem;
    }
    
    .card-button h3 {
      color: white;
      margin-bottom: 0.5rem;
    }
    
    .card-button p {
      color: rgba(255, 255, 255, 0.8);
      font-size: 0.9rem;
    }
    
    @media (max-width: 768px) {
      .card-buttons-container {
        flex-direction: column;
        align-items: center;
      }
      
      .gestion-title {
        font-size: 1.8rem;
      }
    }
  `]
})
export class GestionUsuariosComponent {
  private router = inject(Router);
  private userService = inject(UserService);
  
  constructor() {
    this.registrarActividad();
  }
  
  private registrarActividad(): void {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    if (user.ID_Usuario) {
      this.userService.registrarActividad(user.ID_Usuario, 'El usuario estuvo en la gestión de usuarios').subscribe();
    }
  }
  
  regresar(): void {
    this.router.navigate(['/admin/dashboard']);
  }
  
  irARegistrarUsuario(): void {
    this.router.navigate(['/admin/registrar-usuario']);
  }
  
  irAConsultarUsuarios(): void {
    this.router.navigate(['/admin/consultar-usuarios']);
  }
}