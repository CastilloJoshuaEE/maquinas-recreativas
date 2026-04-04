/**
 * @fileoverview Página de No Autorizado (403)
 * @description Muestra un mensaje cuando un usuario intenta acceder a una página sin permisos
 * @component NoAutorizadoComponent
 */

import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-no-autorizado',
  standalone: true,
  imports: [
    CommonModule,
    RouterLink,
    MatCardModule,
    MatButtonModule,
    MatIconModule
  ],
  template: `
    <div class="no-autorizado-container">
      <mat-card class="error-card">
        <mat-card-content>
          <div class="error-icon">
            <mat-icon>lock</mat-icon>
          </div>
          <h1>403 - No Autorizado</h1>
          <p>No tienes permisos para acceder a esta página.</p>
          <p class="mensaje-ayuda">
            Si crees que esto es un error, por favor contacta al administrador del sistema.
          </p>
          <div class="botones-accion">
            <a mat-raised-button color="primary" routerLink="/auth/login">
              <mat-icon>home</mat-icon>
              Volver al inicio
            </a>
            <button mat-stroked-button (click)="goBack()">
              <mat-icon>arrow_back</mat-icon>
              Página anterior
            </button>
          </div>
        </mat-card-content>
      </mat-card>
    </div>
  `,
  styles: [`
    .no-autorizado-container {
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      background: linear-gradient(135deg, #07224c 0%, #124258 50%, #3b4a66 100%);
      padding: 2rem;
    }
    
    .error-card {
      max-width: 500px;
      width: 100%;
      text-align: center;
      background: white;
      border-radius: 20px;
      animation: fadeInUp 0.5s ease;
    }
    
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .error-icon {
      margin-bottom: 1.5rem;
    }
    
    .error-icon mat-icon {
      font-size: 4rem;
      width: auto;
      height: auto;
      color: #dc3545;
    }
    
    h1 {
      color: #dc3545;
      margin-bottom: 1rem;
      font-size: 2rem;
    }
    
    p {
      color: #555;
      margin-bottom: 0.5rem;
    }
    
    .mensaje-ayuda {
      font-size: 0.85rem;
      color: #999;
      margin-top: 1rem;
    }
    
    .botones-accion {
      display: flex;
      gap: 1rem;
      justify-content: center;
      margin-top: 2rem;
    }
    
    @media (max-width: 768px) {
      .error-card {
        margin: 1rem;
      }
      
      .botones-accion {
        flex-direction: column;
      }
      
      .botones-accion a,
      .botones-accion button {
        width: 100%;
      }
    }
  `]
})
export class NoAutorizadoComponent {
  goBack(): void {
    window.history.back();
  }
}