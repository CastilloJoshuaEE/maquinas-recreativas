/**
 * @fileoverview Dashboard de Contabilidad
 * @description Panel principal del módulo de contabilidad con resúmenes financieros
 * @component DashboardContabilidadComponent
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { Chart, registerables } from 'chart.js';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header';
import { ContabilidadService } from '../../services/contabilidad';
import { ResumenRecaudacion, Recaudacion } from '@core/models/recaudacion.model';

Chart.register(...registerables);

@Component({
  selector: 'app-dashboard-contabilidad',
  standalone: true,
  imports: [
    CommonModule, MatCardModule, MatButtonModule, MatIconModule,
    MatProgressSpinnerModule, AdminHeaderComponent
  ],
  templateUrl: './dashboard-contabilidad.html',
  styleUrls: ['./dashboard-contabilidad.css']
})
export class DashboardContabilidadComponent implements OnInit {
  private router = inject(Router);
  private contabilidadService = inject(ContabilidadService);
  
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
      error: () => { this.loadingResumen = false; }
    });
  }
  
  private cargarTransaccionesRecientes(): void {
    this.contabilidadService.getRecaudaciones({ limit: 5 }).subscribe({
      next: (data) => {
        this.transaccionesRecientes = data.sort((a, b) => new Date(b.fecha).getTime() - new Date(a.fecha).getTime());
        this.loadingTransacciones = false;
      },
      error: () => { this.loadingTransacciones = false; }
    });
  }
  
  private inicializarGrafico(): void {
    if (this.resumen.length === 0) return;
    const ctx = document.getElementById('recaudacionChart') as HTMLCanvasElement;
    if (!ctx) return;
    if (this.chart) this.chart.destroy();
    
    this.chart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: this.resumen.map(r => r.Tipo_Comercio),
        datasets: [{ label: 'Recaudación Total ($)', data: this.resumen.map(r => r.TotalRecaudado), backgroundColor: ['#4f6bed', '#28a745', '#ffc107'], borderColor: '#fff', borderWidth: 1 }]
      },
      options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'top' }, tooltip: { callbacks: { label: (context) => `$${context.raw}` } } } }
    });
  }
  
  irAGestionRecaudacion(): void { this.router.navigate(['/contabilidad/gestion-recaudacion']); }
  irAConsultarRecaudaciones(): void { this.router.navigate(['/contabilidad/consultar-recaudaciones']); }
  irAConsultarInformes(): void { this.router.navigate(['/contabilidad/consultar-informe-distribucion']); }
}