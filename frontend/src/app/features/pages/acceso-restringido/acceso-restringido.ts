/**
 * @fileoverview Página de Acceso Restringido
 * @description Muestra un mensaje para usuarios con cuenta inhabilitada o pendiente
 * @component AccesoRestringidoComponent
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, ActivatedRoute } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { GestionReportesComponent } from '@features/reportes/pages/gestion-reportes/gestion-reportes';

@Component({
  selector: 'app-acceso-restringido',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    GestionReportesComponent
  ],
  template: `
    <div class="acceso-restringido-container">
      <div *ngIf="!showReportForm" class="restringido-card">
        <mat-card>
          <mat-card-content>
            <div class="icono">
              <mat-icon>warning</mat-icon>
            </div>
            <h1>Acceso Restringido</h1>
            
            <div class="user-info" *ngIf="userData">
              <p><strong>Usuario:</strong> {{ userData.nombre }} {{ userData.apellido }}</p>
              <p><strong>Cédula:</strong> {{ userData.ci }}</p>
              <p><strong>Estado:</strong> 
                <span class="estado-badge" [ngClass]="{
                  'estado-inhabilitado': userData.estado === 'Inhabilitado',
                  'estado-pendiente': userData.estado === 'Pendiente de asignacion'
                }">
                  {{ userData.estado }}
                </span>
              </p>
              <p class="motivo">{{ mensajeMotivo }}</p>
            </div>
            
            <div class="botones-accion">
              <button mat-raised-button (click)="volverLogin()">
                <mat-icon>arrow_back</mat-icon>
                Volver al Login
              </button>
              <button mat-raised-button color="primary" (click)="contactarAdmin()" *ngIf="mostrarContacto">
                <mat-icon>contact_support</mat-icon>
                Contactar con Administrador
              </button>
            </div>
          </mat-card-content>
        </mat-card>
      </div>
      
      <div *ngIf="showReportForm" class="reporte-container">
        <app-gestion-reportes></app-gestion-reportes>
      </div>
    </div>
  `,
  styles: [`
    .acceso-restringido-container {
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      background: linear-gradient(135deg, #07224c 0%, #124258 50%, #3b4a66 100%);
      padding: 2rem;
    }
    
    .restringido-card {
      max-width: 500px;
      width: 100%;
    }
    
    mat-card {
      background: white;
      border-radius: 20px;
      text-align: center;
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
    
    .icono mat-icon {
      font-size: 4rem;
      width: auto;
      height: auto;
      color: #ffc107;
      margin-bottom: 1rem;
    }
    
    h1 {
      color: #856404;
      margin-bottom: 1.5rem;
      font-size: 1.8rem;
    }
    
    .user-info {
      text-align: left;
      background: #f8f9fa;
      padding: 1rem;
      border-radius: 12px;
      margin: 1rem 0;
    }
    
    .user-info p {
      margin: 0.5rem 0;
      color: #555;
    }
    
    .user-info strong {
      color: #333;
    }
    
    .estado-badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
    }
    
    .estado-inhabilitado {
      background: #f8d7da;
      color: #721c24;
    }
    
    .estado-pendiente {
      background: #fff3cd;
      color: #856404;
    }
    
    .motivo {
      margin-top: 0.75rem;
      padding-top: 0.75rem;
      border-top: 1px solid #e0e0e0;
      color: #666;
      font-style: italic;
    }
    
    .botones-accion {
      display: flex;
      gap: 1rem;
      justify-content: center;
      margin-top: 1.5rem;
    }
    
    .reporte-container {
      width: 100%;
      max-width: 800px;
    }
    
    @media (max-width: 768px) {
      .acceso-restringido-container {
        padding: 1rem;
      }
      
      .botones-accion {
        flex-direction: column;
      }
      
      .botones-accion button {
        width: 100%;
      }
    }
  `]
})
export class AccesoRestringidoComponent implements OnInit {
  private router = inject(Router);
  private route = inject(ActivatedRoute);
  
  userData: any = null;
  mensajeMotivo = '';
  mostrarContacto = true;
  showReportForm = false;
  
  ngOnInit(): void {
    const navigation = this.router.getCurrentNavigation();
    const state = navigation?.extras.state as any;
    
    if (state) {
      this.userData = state.userData;
      if (state.isDisabledUser) {
        this.mensajeMotivo = 'Su cuenta ha sido inhabilitada. Por favor contacte al administrador para reactivarla.';
      } else if (state.userData?.estado === 'Pendiente de asignacion') {
        this.mensajeMotivo = 'Su cuenta está pendiente de asignación. Por favor espere a que un administrador active su cuenta.';
        this.mostrarContacto = true;
      }
    } else {
      // Intentar obtener datos del localStorage
      const storedUser = localStorage.getItem('user');
      if (storedUser) {
        this.userData = JSON.parse(storedUser);
        if (this.userData.estado === 'Inhabilitado') {
          this.mensajeMotivo = 'Su cuenta ha sido inhabilitada. Por favor contacte al administrador para reactivarla.';
        } else if (this.userData.estado === 'Pendiente de asignacion') {
          this.mensajeMotivo = 'Su cuenta está pendiente de asignación. Por favor espere a que un administrador active su cuenta.';
        }
      } else {
        this.volverLogin();
      }
    }
  }
  
  volverLogin(): void {
    this.router.navigate(['/auth/login']);
  }
  
  contactarAdmin(): void {
    this.showReportForm = true;
  }
}