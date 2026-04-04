/**
 * @fileoverview Levantar Informe de Recaudación
 * @description Genera y guarda el informe completo de una recaudación
 * @component LevantarInformeComponent
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header.component';
import { ContabilidadService } from '../../services/contabilidad.service';
import { Recaudacion, Comercio, InformeRecaudacion } from '@core/models/recaudacion.model';
import { Maquina } from '@core/models/maquina.model';
import { Componente } from '@core/models/componente.model';
import { User } from '@core/models/user.model';
import { AuthService } from '@core/services/auth.service';

@Component({
  selector: 'app-levantar-informe',
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
    <div class="informe-container">
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
          <p>Cargando datos...</p>
        </div>

        <div *ngIf="error" class="error-message">
          {{ error }}
        </div>

        <div *ngIf="!loading && recaudacion && maquina && comercio" class="informe-content">
          <!-- Datos del Comercio -->
          <mat-card class="informe-section">
            <mat-card-header>
              <mat-card-title>
                <mat-icon>store</mat-icon>
                Datos del Comercio
              </mat-card-title>
            </mat-card-header>
            <mat-card-content>
              <p><strong>Nombre:</strong> {{ comercio.Nombre }}</p>
              <p><strong>Dirección:</strong> {{ comercio.Direccion }}</p>
              <p><strong>Teléfono:</strong> {{ comercio.Telefono }}</p>
              <p><strong>Tipo:</strong> {{ comercio.Tipo }}</p>
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
              <p><strong>Máquina:</strong> {{ maquina.Nombre_Maquina }}</p>
              <p><strong>Monto Total:</strong> ${{ recaudacion.Monto_Total | number:'1.2-2' }}</p>
              <p><strong>Monto Empresa:</strong> ${{ recaudacion.Monto_Empresa | number:'1.2-2' }}</p>
              <p *ngIf="recaudacion.Tipo_Comercio === 'Mayorista'">
                <strong>Monto Comercio:</strong> ${{ recaudacion.Monto_Comercio | number:'1.2-2' }} 
                ({{ recaudacion.Porcentaje_Comercio }}%)
              </p>
              <p><strong>Fecha:</strong> {{ recaudacion.fecha | date:'dd/MM/yyyy HH:mm' }}</p>
              <p><strong>Detalles:</strong> {{ recaudacion.detalle || 'Ninguno' }}</p>
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
              <div class="tecnico-info" *ngIf="tecnicos.ensamblador">
                <h4>Ensamblador</h4>
                <p><strong>Nombre:</strong> {{ tecnicos.ensamblador.nombre }} {{ tecnicos.ensamblador.apellido }}</p>
                <p><strong>CI:</strong> {{ tecnicos.ensamblador.ci }}</p>
                <p><strong>Pago:</strong> $400.00</p>
              </div>

              <div class="tecnico-info" *ngIf="tecnicos.comprobador">
                <h4>Comprobador</h4>
                <p><strong>Nombre:</strong> {{ tecnicos.comprobador.nombre }} {{ tecnicos.comprobador.apellido }}</p>
                <p><strong>CI:</strong> {{ tecnicos.comprobador.ci }}</p>
                <p><strong>Pago:</strong> $400.00</p>
              </div>

              <div class="tecnico-info" *ngIf="tecnicos.mantenimiento">
                <h4>Técnico de Mantenimiento</h4>
                <p><strong>Nombre:</strong> {{ tecnicos.mantenimiento.nombre }} {{ tecnicos.mantenimiento.apellido }}</p>
                <p><strong>CI:</strong> {{ tecnicos.mantenimiento.ci }}</p>
                <p><strong>Pago:</strong> $400.00</p>
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

          <!-- Totales -->
          <mat-card class="informe-section informe-totales">
            <mat-card-header>
              <mat-card-title>
                <mat-icon>summarize</mat-icon>
                Totales
              </mat-card-title>
            </mat-card-header>
            <mat-card-content>
              <p><strong>Total recaudado:</strong> ${{ recaudacion.Monto_Total | number:'1.2-2' }}</p>
              <p><strong>Total pagos a técnicos:</strong> ${{ totalPagosTecnicos | number:'1.2-2' }}</p>
              <p><strong>Total componentes:</strong> ${{ totalComponentes | number:'1.2-2' }}</p>
              <p class="grand-total">
                <strong>Total neto para la empresa:</strong> 
                ${{ (recaudacion.Monto_Empresa - totalPagosTecnicos - totalComponentes) | number:'1.2-2' }}
              </p>
            </mat-card-content>
          </mat-card>

          <!-- Footer del Informe -->
          <mat-card class="informe-footer">
            <mat-card-content>
              <p><strong>Empresa:</strong> Recrea Sys S.A.</p>
              <p><strong>Descripción:</strong> Una empresa encargada en el ciclo de vida de las máquinas recreativas</p>
              <p><strong>Fecha de emisión:</strong> {{ fechaEmision | date:'dd/MM/yyyy HH:mm' }}</p>
            </mat-card-content>
          </mat-card>

          <!-- Acciones -->
          <div class="informe-actions">
            <button mat-raised-button color="primary" (click)="imprimirInforme()">
              <mat-icon>print</mat-icon>
              Imprimir Informe
            </button>
            <button mat-raised-button color="accent" (click)="guardarInforme()" [disabled]="guardando">
              <mat-spinner diameter="20" *ngIf="guardando"></mat-spinner>
              <span *ngIf="!guardando">Guardar Informe</span>
            </button>
            <button mat-raised-button (click)="regresar()">
              <mat-icon>arrow_back</mat-icon>
              Volver
            </button>
          </div>
        </div>

        <div *ngIf="success" class="success-message">
          <mat-icon>check_circle</mat-icon>
          <p>¡Informe guardado correctamente!</p>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .informe-container {
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
    
    .informe-totales {
      background: linear-gradient(135deg, #4f6bed, #3d55c3);
      color: white;
    }
    
    .informe-totales mat-card-title {
      color: white;
    }
    
    .informe-totales p {
      color: white;
    }
    
    .grand-total {
      font-size: 1.1rem;
      margin-top: 0.5rem;
      padding-top: 0.5rem;
      border-top: 1px solid rgba(255, 255, 255, 0.3);
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
    
    .success-message {
      margin-top: 1rem;
      padding: 1rem;
      background: #d4edda;
      color: #155724;
      border-radius: 8px;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      justify-content: center;
    }
    
    @media print {
      .admin-header,
      .page-header,
      .informe-actions,
      .back-button {
        display: none !important;
      }
      
      .informe-container {
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
export class LevantarInformeComponent implements OnInit {
  private route = inject(ActivatedRoute);
  private router = inject(Router);
  private contabilidadService = inject(ContabilidadService);
  private authService = inject(AuthService);
  private snackBar = inject(MatSnackBar);
  
  recaudacion: Recaudacion | null = null;
  maquina: Maquina | null = null;
  comercio: Comercio | null = null;
  componentes: Componente[] = [];
  tecnicos: { ensamblador: User | null; comprobador: User | null; mantenimiento: User | null } = {
    ensamblador: null,
    comprobador: null,
    mantenimiento: null
  };
  
  loading = true;
  guardando = false;
  success = false;
  error = '';
  fechaEmision = new Date();
  
  ngOnInit(): void {
    this.cargarDatos();
  }
  
  private cargarDatos(): void {
    const idRecaudacion = this.route.snapshot.params['idRecaudacion'];
    if (!idRecaudacion) {
      this.error = 'ID de recaudación no válido';
      this.loading = false;
      return;
    }
    
    this.contabilidadService.getRecaudacionById(idRecaudacion).subscribe({
      next: (recaudacion) => {
        if (!recaudacion) {
          this.error = 'Recaudación no encontrada';
          this.loading = false;
          return;
        }
        
        this.recaudacion = recaudacion;
        this.cargarMaquina(recaudacion.ID_Maquina);
      },
      error: (err) => {
        this.error = err.message || 'Error al cargar recaudación';
        this.loading = false;
      }
    });
  }
  
  private cargarMaquina(idMaquina: string): void {
    this.contabilidadService.getMaquinasRecaudacion().subscribe({
      next: (maquinas) => {
        this.maquina = maquinas.find(m => m.ID_Maquina === idMaquina) || null;
        if (this.maquina) {
          this.cargarComercio(this.maquina.ID_Comercio);
          this.cargarComponentes(idMaquina);
        } else {
          this.error = 'Máquina no encontrada';
          this.loading = false;
        }
      },
      error: () => {
        this.error = 'Error al cargar máquina';
        this.loading = false;
      }
    });
  }
  
  private cargarComercio(idComercio: string): void {
    this.contabilidadService.getComercios().subscribe({
      next: (comercios) => {
        this.comercio = comercios.find(c => c.ID_Comercio === idComercio) || null;
        this.cargarTecnicos();
      },
      error: () => {
        this.error = 'Error al cargar comercio';
        this.loading = false;
      }
    });
  }
  
  private cargarComponentes(idMaquina: string): void {
    // Aquí se cargarían los componentes de la máquina desde el servicio de máquinas
    this.componentes = [];
  }
  
  private cargarTecnicos(): void {
    // Aquí se cargarían los técnicos asociados a la máquina
    this.loading = false;
  }
  
  get totalPagosTecnicos(): number {
    let total = 0;
    if (this.tecnicos.ensamblador) total += 400;
    if (this.tecnicos.comprobador) total += 400;
    if (this.tecnicos.mantenimiento) total += 400;
    return total;
  }
  
  get totalComponentes(): number {
    return this.componentes.reduce((sum, comp) => sum + (comp.precio || 0), 0);
  }
  
  imprimirInforme(): void {
    setTimeout(() => window.print(), 300);
  }
  
  guardarInforme(): void {
    if (!this.recaudacion) return;
    
    this.guardando = true;
    const currentUser = this.authService.getCurrentUser();
    
    const informeData = {
      ID_Recaudacion: this.recaudacion.ID_Recaudacion,
      ID_Comercio: this.comercio?.ID_Comercio,
      CI_Usuario: currentUser?.ci,
      Nombre_Maquina: this.maquina?.Nombre_Maquina,
      Nombre_Comercio: this.comercio?.Nombre,
      Direccion_Comercio: this.comercio?.Direccion,
      Telefono_Comercio: this.comercio?.Telefono,
      Pago_Ensamblador: this.tecnicos.ensamblador ? 400 : 0,
      Pago_Comprobador: this.tecnicos.comprobador ? 400 : 0,
      Pago_Mantenimiento: this.tecnicos.mantenimiento ? 400 : 0,
      componentes: this.componentes.map(c => ({ ID_Componente: c.ID_Componente })),
      Monto_Total: this.recaudacion.Monto_Total
    };
    
    this.contabilidadService.guardarInforme(informeData).subscribe({
      next: (response) => {
        if (response.success) {
          this.success = true;
          this.snackBar.open('Informe guardado correctamente', 'Cerrar', { duration: 3000 });
          setTimeout(() => {
            this.router.navigate(['/contabilidad/consultar-recaudaciones']);
          }, 2000);
        } else {
          this.snackBar.open(response.message || 'Error al guardar informe', 'Cerrar', { duration: 3000 });
        }
        this.guardando = false;
      },
      error: (err) => {
        this.snackBar.open(err.message || 'Error al guardar informe', 'Cerrar', { duration: 3000 });
        this.guardando = false;
      }
    });
  }
  
  regresar(): void {
    this.router.navigate(['/contabilidad/consultar-recaudaciones']);
  }
}