/**
 * @fileoverview Gestión de Recaudación
 * @description Página principal para gestionar recaudaciones (registrar y consultar)
 * @component GestionRecaudacionComponent
 */

import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header.component';

@Component({
  selector: 'app-gestion-recaudacion',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    AdminHeaderComponent
  ],
  template: `
    <div class="gestion-recaudacion-container">
      <app-admin-header></app-admin-header>
      
      <div class="perfil-contenedor">
        <button mat-raised-button (click)="regresar()" class="btn-regresar">
          <mat-icon>arrow_back</mat-icon>
          Regresar
        </button>
        
        <h2 class="gestion-title">Gestión de Recaudación</h2>
        
        <div class="card-buttons-container">
          <div class="card-button" (click)="irARegistrarRecaudacion()">
            <span class="icono-card">💰</span>
            <h3>Registrar Recaudación</h3>
            <p>Registrar una nueva recaudación de máquina</p>
          </div>
          
          <div class="card-button" (click)="irAConsultarRecaudaciones()">
            <span class="icono-card">📊</span>
            <h3>Consultar Recaudaciones</h3>
            <p>Consultar, editar y eliminar recaudaciones</p>
          </div>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .gestion-recaudacion-container {
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
      width: 280px;
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
export class GestionRecaudacionComponent {
  private router = inject(Router);
  
  regresar(): void {
    this.router.navigate(['/contabilidad/dashboard']);
  }
  
  irARegistrarRecaudacion(): void {
    this.router.navigate(['/contabilidad/registrar-recaudacion']);
  }
  
  irAConsultarRecaudaciones(): void {
    this.router.navigate(['/contabilidad/consultar-recaudaciones']);
  }
}