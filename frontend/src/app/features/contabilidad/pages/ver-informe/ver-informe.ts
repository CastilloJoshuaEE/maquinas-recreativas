/**
 * @fileoverview Ver Informe de Recaudación
 * @description Visualiza un informe de recaudación ya generado
 * @component VerInformeComponent
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header.component';
import { ContabilidadService } from '../../services/contabilidad.service';
import { InformeRecaudacion, Recaudacion } from '@core/models/recaudacion.model';
import { Componente } from '@core/models/componente.model';

@Component({
  selector: 'app-ver-informe',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    AdminHeaderComponent
  ],
  template: `
    <div class="ver-informe-container">
      <app-admin-header></app-admin-header>
      
      <div class="content-wrapper">
        <div class="page-header">
          <button mat-icon-button (click)="regresar()" class="back-button">
            <mat-icon>arrow_back</mat-icon>
          </button>
          <h2>Informe de Recaudación</h2>
        </div>

        <div *ngIf="loading" class="loading-container">
          <mat-spinner diameter="40"></mat-spinner>
          <p>Cargando informe...</p>
        </div>

        <div *ngIf="error" class="error-message">
          {{ error }}
        </div>

        <div *ngIf="!loading && informe" class="informe-content">
          <!-- Datos del Comercio -->
          <mat-card class="informe-section">
            <mat-card-header>
              <mat-card-title>
                <mat-icon>store</mat-icon>
                Datos del Comercio
              </mat-card-title>
            </mat-card-header>
            <mat-card-content>
              <p><strong>Nombre:</strong> {{ informe.Nombre_Comercio }}</p>
              <p><strong>Dirección:</strong> {{ informe.Direccion_Comercio }}</p>
              <p><strong>Teléfono:</strong> {{ informe.Telefono_Comercio }}</p>
            </mat-card-content>
          </mat-card>

          <!-- Datos de la Recaudación -->
          <mat-card class="informe-section">
            <mat-card-header>
              <mat-card-title>
                <mat-icon>attach_money</mat-icon>
                Datos de la Recaudación
              </mat-card-title>
            </mat-card-header>
            <mat-card-content>
              <p><strong>Máquina:</strong> {{ informe.Nombre_Maquina }}</p>
              <p><strong>Monto Total:</strong> ${{ (recaudacion?.Monto_Total || informe.Monto_Total) | number:'1.2-2' }}</p>
              <p><strong>Monto Empresa:</strong> ${{ recaudacion?.Monto_Empresa || 'N/A' | number:'1.2-2' }}</p>
              <p *ngIf="recaudacion?.Tipo_Comercio === 'Mayorista'">
                <strong>Monto Comercio:</strong> ${{ recaudacion?.Monto_Comercio }} 
                ({{ recaudacion?.Porcentaje_Comercio }}%)
              </p>
              <p><strong>Fecha:</strong> {{ recaudacion?.fecha | date:'dd/MM/yyyy HH:mm' }}</p>
              <p><strong>Detalles:</strong> {{ recaudacion?.detalle || 'Ninguno' }}</p>
            </mat-card-content>
          </mat-card>

          <!-- Técnicos Involucrados -->
          <mat-card class="informe-section">
            <mat-card-header>
              <mat-card-title>
                <mat-icon>engineering</mat-icon>
                Técnicos Involucrados
              </mat-card-title>
            </mat-card-header>
            <mat-card-content>
              <div class="tecnico-info" *ngIf="informe.Pago_Ensamblador > 0">
                <h4>Ensamblador</h4>
                <p><strong>Pago:</strong> ${{ informe.Pago_Ensamblador | number:'1.2-2' }}</p>
              </div>

              <div class="tecnico-info" *ngIf="informe.Pago_Comprobador > 0">
                <h4>Comprobador</h4>
                <p><strong>Pago:</strong> ${{ informe.Pago_Comprobador | number:'1.2-2' }}</p>
              </div>

              <div class="tecnico-info" *ngIf="informe.Pago_Mantenimiento && informe.Pago_Mantenimiento > 0">
                <h4>Técnico de Mantenimiento</h4>
                <p><strong>Pago:</strong> ${{ informe.Pago_Mantenimiento | number:'1.2-2' }}</p>
              </div>
            </mat-card-content>
          </mat-card>

          <!-- Componentes Utilizados -->
          <mat-card class="informe-section">
            <mat-card-header>
              <mat-card-title>
                <mat-icon>memory</mat-icon>
                Componentes Utilizados
              </mat-card-title>
            </mat-card-header>
            <mat-card-content>
              <div *ngIf="componentes.length > 0; else sinComponentes">
                <table class="componentes-table">
                  <thead>
                    <tr>
                      <th>Componente</th>
                      <th>Tipo</th>
                      <th>Precio</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr *ngFor="let comp of componentes">
                      <td>{{ comp.nombre }}</td>
                      <td>{{ comp.tipo }}</td>
                      <td>${{ comp.precio | number:'1.2-2' }}</td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr>
                      <td colspan="2"><strong>Total componentes:</strong></td>
                      <td><strong>${{ totalComponentes | number:'1.2-2' }}</strong></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
              <ng-template #sinComponentes>
                <p>No se utilizaron componentes en esta máquina</p>
              </ng-template>
            </mat-card-content>
          </mat-card>

          <!-- Footer del Informe -->
          <mat-card class="informe-footer">
            <mat-card-content>
              <p><strong>Empresa:</strong> {{ informe.empresa_nombre || 'Recrea Sys S.A.' }}</p>
              <p><strong>Descripción:</strong> {{ informe.empresa_descripcion || 'Una empresa encargada en el ciclo de vida de las máquinas recreativas' }}</p>
              <p><strong>Fecha de emisión:</strong> {{ informe.fecha_emision | date:'dd/MM/yyyy HH:mm' }}</p>
            </mat-card-content>
          </mat-card>

          <!-- Acciones -->
          <div class="informe-actions">
            <button mat-raised-button color="primary" (click)="imprimirInforme()">
              <mat-icon>print</mat-icon>
              Imprimir Informe
            </button>
            <button mat-raised-button (click)="regresar()">
              <mat-icon>arrow_back</mat-icon>
              Volver
            </button>
          </div>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .ver-informe-container {
      min-height: 100vh;
      background: linear-gradient(135deg, #07224c 0%, #124258 50%, #3b4a66 100%);
    }
    
    .content-wrapper {
      max-width: 1000px;
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
    
    .informe-section {
      margin-bottom: 1.5rem;
      background: white;
    }
    
    .informe-section mat-card-title {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 1.2rem;
      color: #2c3e50;
    }
    
    .tecnico-info {
      margin-bottom: 1rem;
      padding: 0.5rem;
      background: #f8f9fa;
      border-radius: 8px;
    }
    
    .tecnico-info h4 {
      color: #4f6bed;
      margin-bottom: 0.5rem;
    }
    
    .componentes-table {
      width: 100%;
      border-collapse: collapse;
    }
    
    .componentes-table th,
    .componentes-table td {
      padding: 0.5rem;
      text-align: left;
      border-bottom: 1px solid #e0e0e0;
    }
    
    .componentes-table th {
      background: #f5f5f5;
      font-weight: 600;
    }
    
    .componentes-table tfoot td {
      font-weight: bold;
      border-top: 2px solid #e0e0e0;
    }
    
    .informe-footer {
      background: #f5f5f5;
      text-align: center;
    }
    
    .informe-actions {
      display: flex;
      gap: 1rem;
      justify-content: center;
      margin-top: 2rem;
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
    }
    
    @media print {
      .admin-header,
      .page-header,
      .informe-actions,
      .back-button {
        display: none !important;
      }
      
      .ver-informe-container {
        background: white;
      }
      
      .informe-section {
        break-inside: avoid;
        box-shadow: none;
      }
    }
    
    @media (max-width: 768px) {
      .content-wrapper {
        padding: 1rem;
      }
      
      .componentes-table {
        font-size: 0.8rem;
      }
    }
  `]
})
export class VerInformeComponent implements OnInit {
  private route = inject(ActivatedRoute);
  private router = inject(Router);
  private contabilidadService = inject(ContabilidadService);
  
  informe: InformeRecaudacion | null = null;
  recaudacion: Recaudacion | null = null;
  componentes: Componente[] = [];
  loading = true;
  error = '';
  
  ngOnInit(): void {
    this.cargarInforme();
  }
  
  private cargarInforme(): void {
    const idRecaudacion = this.route.snapshot.params['idRecaudacion'];
    if (!idRecaudacion) {
      this.error = 'ID de recaudación no válido';
      this.loading = false;
      return;
    }
    
    this.contabilidadService.getInformeByRecaudacion(idRecaudacion).subscribe({
      next: (informe) => {
        if (!informe) {
          this.error = 'Informe no encontrado';
          this.loading = false;
          return;
        }
        
        this.informe = informe;
        this.cargarRecaudacion(idRecaudacion);
      },
      error: (err) => {
        this.error = err.message || 'Error al cargar informe';
        this.loading = false;
      }
    });
  }
  
  private cargarRecaudacion(idRecaudacion: string): void {
    this.contabilidadService.getRecaudacionById(idRecaudacion).subscribe({
      next: (recaudacion) => {
        this.recaudacion = recaudacion;
        this.cargarComponentes();
      },
      error: () => {
        this.cargarComponentes();
      }
    });
  }
  
  private cargarComponentes(): void {
    // Aquí se cargarían los componentes del informe si están disponibles
    this.loading = false;
  }
  
  get totalComponentes(): number {
    return this.componentes.reduce((sum, comp) => sum + (comp.precio || 0), 0);
  }
  
  imprimirInforme(): void {
    setTimeout(() => window.print(), 300);
  }
  
  regresar(): void {
    this.router.navigate(['/contabilidad/consultar-recaudaciones']);
  }
}