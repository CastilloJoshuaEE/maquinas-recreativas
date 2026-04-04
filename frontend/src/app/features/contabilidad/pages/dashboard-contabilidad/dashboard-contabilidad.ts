/**
 * @fileoverview Dashboard de Contabilidad
 * @description Panel principal del módulo de contabilidad con resúmenes financieros
 * @component DashboardContabilidadComponent
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatGridListModule } from '@angular/material/grid-list';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { Chart, registerables } from 'chart.js';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header.component';
import { ContabilidadService } from '../../services/contabilidad.service';
import { AuthService } from '@core/services/auth.service';
import { ResumenRecaudacion, Recaudacion } from '@core/models/recaudacion.model';

Chart.register(...registerables);

@Component({
  selector: 'app-dashboard-contabilidad',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatGridListModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    AdminHeaderComponent
  ],
  template: `
    <div class="contabilidad-container">
      <app-admin-header></app-admin-header>
      
      <main class="contabilidad-content">
        <section class="welcome-section">
          <h2>Bienvenido al área de contabilidad</h2>
          <p>Aquí podrás gestionar todos los aspectos financieros del sistema</p>
        </section>

        <div class="contabilidad-grid">
          <!-- Resumen Financiero -->
          <mat-card class="contabilidad-card">
            <mat-card-header>
              <mat-card-title>
                <mat-icon>assessment</mat-icon>
                Resumen Financiero
              </mat-card-title>
            </mat-card-header>
            <mat-card-content>
              <div *ngIf="loadingResumen" class="loading-spinner">
                <mat-spinner diameter="40"></mat-spinner>
              </div>
              <div *ngIf="!loadingResumen && resumen.length > 0">
                <div *ngFor="let item of resumen" class="summary-item">
                  <h4>{{ item.Tipo_Comercio }}</h4>
                  <div class="summary-details">
                    <p><strong>Recaudaciones:</strong> {{ item.TotalRecaudaciones }}</p>
                    <p><strong>Total:</strong> ${{ item.TotalRecaudado | number:'1.2-2' }}</p>
                    <p><strong>Empresa:</strong> ${{ item.TotalEmpresa | number:'1.2-2' }}</p>
                    <p><strong>Comercio:</strong> ${{ item.TotalComercio | number:'1.2-2' }}</p>
                  </div>
                </div>
              </div>
              <div *ngIf="!loadingResumen && resumen.length === 0" class="no-data">
                <p>No hay datos disponibles</p>
              </div>
            </mat-card-content>
          </mat-card>

          <!-- Gráfico de Recaudación Mensual -->
          <mat-card class="contabilidad-card">
            <mat-card-header>
              <mat-card-title>
                <mat-icon>show_chart</mat-icon>
                Recaudación Mensual
              </mat-card-title>
            </mat-card-header>
            <mat-card-content>
              <canvas id="recaudacionChart"></canvas>
            </mat-card-content>
          </mat-card>

          <!-- Acciones Rápidas -->
          <mat-card class="contabilidad-card">
            <mat-card-header>
              <mat-card-title>
                <mat-icon>speed</mat-icon>
                Acciones Rápidas
              </mat-card-title>
            </mat-card-header>
            <mat-card-content>
              <div class="action-buttons">
                <button mat-raised-button color="primary" (click)="irAGestionRecaudacion()">
                  <mat-icon>attach_money</mat-icon>
                  Gestionar recaudación
                </button>
                <button mat-raised-button color="accent" (click)="irAConsultarRecaudaciones()">
                  <mat-icon>search</mat-icon>
                  Consultar recaudaciones
                </button>
                <button mat-raised-button (click)="irAConsultarInformes()">
                  <mat-icon>description</mat-icon>
                  Consultar informes
                </button>
              </div>
            </mat-card-content>
          </mat-card>

          <!-- Transacciones Recientes -->
          <mat-card class="contabilidad-card">
            <mat-card-header>
              <mat-card-title>
                <mat-icon>history</mat-icon>
                Transacciones Recientes
              </mat-card-title>
            </mat-card-header>
            <mat-card-content>
              <div *ngIf="loadingTransacciones" class="loading-spinner">
                <mat-spinner diameter="40"></mat-spinner>
              </div>
              <div *ngIf="!loadingTransacciones && transaccionesRecientes.length > 0">
                <div *ngFor="let tx of transaccionesRecientes" class="transaction-item">
                  <div class="transaction-info">
                    <strong>{{ tx.Nombre_Maquina }}</strong>
                    <span class="transaction-amount">${{ tx.Monto_Total | number:'1.2-2' }}</span>
                  </div>
                  <div class="transaction-date">{{ tx.fecha | date:'dd/MM/yyyy HH:mm' }}</div>
                </div>
              </div>
              <div *ngIf="!loadingTransacciones && transaccionesRecientes.length === 0" class="no-data">
                <p>No hay transacciones recientes</p>
              </div>
            </mat-card-content>
          </mat-card>
        </div>
      </main>
    </div>
  `,
  styles: [`
    .contabilidad-container {
      min-height: 100vh;
      background: linear-gradient(135deg, #07224c 0%, #124258 50%, #3b4a66 100%);
    }
    
    .contabilidad-content {
      max-width: 1400px;
      margin: 0 auto;
      padding: 2rem;
    }
    
    .welcome-section {
      text-align: center;
      margin-bottom: 2rem;
      padding: 1rem;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 12px;
      backdrop-filter: blur(10px);
    }
    
    .welcome-section h2 {
      color: white;
      margin-bottom: 0.5rem;
    }
    
    .welcome-section p {
      color: rgba(255, 255, 255, 0.8);
    }
    
    .contabilidad-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
      gap: 1.5rem;
    }
    
    .contabilidad-card {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 12px;
    }
    
    .contabilidad-card mat-card-header {
      padding: 1rem 1rem 0 1rem;
    }
    
    .contabilidad-card mat-card-title {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 1.2rem;
      color: #2c3e50;
    }
    
    .summary-item {
      padding: 0.75rem;
      margin-bottom: 1rem;
      background: #f8f9fa;
      border-radius: 8px;
    }
    
    .summary-item h4 {
      color: #4f6bed;
      margin-bottom: 0.5rem;
    }
    
    .summary-details p {
      margin: 0.25rem 0;
      color: #555;
    }
    
    .action-buttons {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }
    
    .action-buttons button {
      width: 100%;
      padding: 0.75rem;
    }
    
    .transaction-item {
      padding: 0.75rem;
      border-bottom: 1px solid #e0e0e0;
    }
    
    .transaction-item:last-child {
      border-bottom: none;
    }
    
    .transaction-info {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 0.25rem;
    }
    
    .transaction-amount {
      color: #28a745;
      font-weight: bold;
    }
    
    .transaction-date {
      font-size: 0.75rem;
      color: #999;
    }
    
    .loading-spinner {
      display: flex;
      justify-content: center;
      padding: 2rem;
    }
    
    .no-data {
      text-align: center;
      padding: 2rem;
      color: #999;
    }
    
    canvas {
      max-height: 300px;
    }
    
    @media (max-width: 768px) {
      .contabilidad-grid {
        grid-template-columns: 1fr;
      }
      
      .contabilidad-content {
        padding: 1rem;
      }
    }
  `]
})
export class DashboardContabilidadComponent implements OnInit {
  private router = inject(Router);
  private contabilidadService = inject(ContabilidadService);
  private authService = inject(AuthService);
  
  resumen: ResumenRecaudacion[] = [];
  transaccionesRecientes: Recaudacion[] = [];
  loadingResumen = true;
  loadingTransacciones = true;
  private chart: Chart | null = null;
  
  ngOnInit(): void {
    this.cargarResumen();
    this.cargarTransaccionesRecientes();
  }
  
  private cargarResumen(): void {
    this.contabilidadService.getResumenRecaudaciones().subscribe({
      next: (data) => {
        this.resumen = data;
        this.loadingResumen = false;
        this.inicializarGrafico();
      },
      error: () => {
        this.loadingResumen = false;
      }
    });
  }
  
  private cargarTransaccionesRecientes(): void {
    this.contabilidadService.getRecaudaciones({ limit: 5 }).subscribe({
      next: (data) => {
        this.transaccionesRecientes = data.sort((a, b) => 
          new Date(b.fecha).getTime() - new Date(a.fecha).getTime()
        );
        this.loadingTransacciones = false;
      },
      error: () => {
        this.loadingTransacciones = false;
      }
    });
  }
  
  private inicializarGrafico(): void {
    if (this.resumen.length === 0) return;
    
    const ctx = document.getElementById('recaudacionChart') as HTMLCanvasElement;
    if (!ctx) return;
    
    if (this.chart) {
      this.chart.destroy();
    }
    
    const tipos = this.resumen.map(r => r.Tipo_Comercio);
    const totales = this.resumen.map(r => r.TotalRecaudado);
    
    this.chart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: tipos,
        datasets: [{
          label: 'Recaudación Total ($)',
          data: totales,
          backgroundColor: ['#4f6bed', '#28a745', '#ffc107'],
          borderColor: '#fff',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            position: 'top',
          },
          tooltip: {
            callbacks: {
              label: (context) => `$${context.raw}`
            }
          }
        }
      }
    });
  }
  
  irAGestionRecaudacion(): void {
    this.router.navigate(['/contabilidad/gestion-recaudacion']);
  }
  
  irAConsultarRecaudaciones(): void {
    this.router.navigate(['/contabilidad/consultar-recaudaciones']);
  }
  
  irAConsultarInformes(): void {
    this.router.navigate(['/contabilidad/consultar-informe-distribucion']);
  }
}