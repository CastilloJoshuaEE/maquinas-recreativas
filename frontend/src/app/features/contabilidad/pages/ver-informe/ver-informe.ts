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
import { ContabilidadService } from '../../services/contabilidad';
import { InformeRecaudacion, Recaudacion } from '@core/models/recaudacion.model';
import { Componente } from '@core/models/componente.model';

@Component({
  selector: 'app-ver-informe',
  standalone: true,
  imports: [CommonModule, MatCardModule, MatButtonModule, MatIconModule, MatProgressSpinnerModule, AdminHeaderComponent],
  templateUrl: './ver-informe.html',
  styleUrls: ['./ver-informe.css']
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
  
  ngOnInit(): void { this.cargarInforme(); }
  
  private cargarInforme(): void {
    const idRecaudacion = this.route.snapshot.params['idRecaudacion'];
    if (!idRecaudacion) { this.error = 'ID de recaudación no válido'; this.loading = false; return; }
    
    this.contabilidadService.getInformeByRecaudacion(idRecaudacion).subscribe({
      next: (informe) => {
        if (!informe) { this.error = 'Informe no encontrado'; this.loading = false; return; }
        this.informe = informe;
        this.cargarRecaudacion(idRecaudacion);
      },
      error: (err) => { this.error = err.message || 'Error al cargar informe'; this.loading = false; }
    });
  }
  
  private cargarRecaudacion(idRecaudacion: string): void {
    this.contabilidadService.getRecaudacionById(idRecaudacion).subscribe({
      next: (recaudacion) => { this.recaudacion = recaudacion; this.cargarComponentes(); },
      error: () => { this.cargarComponentes(); }
    });
  }
  
  private cargarComponentes(): void { this.loading = false; }
  
  get totalComponentes(): number { return this.componentes.reduce((sum, comp) => sum + (comp.precio || 0), 0); }
  
  imprimirInforme(): void { setTimeout(() => window.print(), 300); }
  
  regresar(): void { this.router.navigate(['/contabilidad/consultar-recaudaciones']); }
}