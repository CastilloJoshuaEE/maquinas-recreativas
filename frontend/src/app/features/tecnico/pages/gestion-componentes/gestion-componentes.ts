/**
 * @fileoverview Gestión de Componentes para Técnicos
 * @description Permite a los técnicos ver y gestionar componentes disponibles y en uso
 * @component GestionComponentesComponent
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatTableModule } from '@angular/material/table';
import { MatPaginatorModule, PageEvent } from '@angular/material/paginator';
import { MatSelectModule } from '@angular/material/select';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header.component';
import { TecnicoService } from '../../services/tecnico.service';
import { AuthService } from '@core/services/auth.service';
import { Componente } from '@core/models/componente.model';
import { Maquina } from '@core/models/maquina.model';
import { User } from '@core/models/user.model';

@Component({
  selector: 'app-gestion-componentes',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatTableModule,
    MatPaginatorModule,
    MatSelectModule,
    MatProgressSpinnerModule,
    AdminHeaderComponent
  ],
  template: `
    <div class="gestion-componentes-container">
      <app-admin-header></app-admin-header>
      
      <div class="content-wrapper">
        <div class="page-header">
          <button mat-icon-button (click)="regresar()" class="back-button">
            <mat-icon>arrow_back</mat-icon>
          </button>
          <h2>Gestión de Componentes</h2>
        </div>

        <!-- Información de Máquina Seleccionada -->
        <div class="selected-machine-info" *ngIf="selectedMachine">
          <h3>Máquina: {{ selectedMachine.Nombre_Maquina }}</h3>
          <p>Comercio: {{ selectedMachine.NombreComercio }}</p>
        </div>

        <!-- Componentes Disponibles -->
        <div class="componentes-section">
          <h3>Componentes Disponibles</h3>
          
          <div class="filtros-componentes">
            <mat-form-field appearance="outline">
              <mat-label>Tipo de Componente</mat-label>
              <mat-select [(ngModel)]="filtroTipo" (selectionChange)="cargarComponentesDisponibles()">
                <mat-option value="">Todos</mat-option>
                <mat-option value="Placa">Placa</mat-option>
                <mat-option value="Carcasa">Carcasa</mat-option>
                <mat-option value="Electronico">Electrónico</mat-option>
                <mat-option value="Mecanico">Mecánico</mat-option>
              </mat-select>
            </mat-form-field>
          </div>

          <div *ngIf="cargandoDisponibles" class="loading-container">
            <mat-spinner diameter="40"></mat-spinner>
          </div>

          <div *ngIf="!cargandoDisponibles && componentesDisponibles.length === 0" class="no-data">
            <p>No hay componentes disponibles</p>
          </div>

          <div *ngIf="!cargandoDisponibles && componentesDisponibles.length > 0">
            <table mat-table [dataSource]="componentesDisponibles" class="mat-elevation-z8">
              
              <ng-container matColumnDef="ID_Componente">
                <th mat-header-cell *matHeaderCellDef> ID </th>
                <td mat-cell *matCellDef="let comp"> {{comp.ID_Componente | slice:0:8}}... </td>
              </ng-container>

              <ng-container matColumnDef="nombre">
                <th mat-header-cell *matHeaderCellDef> Nombre </th>
                <td mat-cell *matCellDef="let comp"> {{comp.nombre}} </td>
              </ng-container>

              <ng-container matColumnDef="tipo">
                <th mat-header-cell *matHeaderCellDef> Tipo </th>
                <td mat-cell *matCellDef="let comp"> {{comp.tipo}} </td>
              </ng-container>

              <ng-container matColumnDef="precio">
                <th mat-header-cell *matHeaderCellDef> Precio </th>
                <td mat-cell *matCellDef="let comp"> ${{comp.precio | number:'1.2-2'}} </td>
              </ng-container>

              <ng-container matColumnDef="acciones">
                <th mat-header-cell *matHeaderCellDef> Acciones </th>
                <td mat-cell *matCellDef="let comp">
                  <button mat-raised-button color="primary" 
                          (click)="usarComponente(comp)"
                          [disabled]="!selectedMachine || componenteEnUso(comp)">
                    Usar
                  </button>
                </td>
              </ng-container>

              <tr mat-header-row *matHeaderRowDef="displayedColumnsDisponibles"></table>
              <tr mat-row *matRowDef="let row; columns: displayedColumnsDisponibles;"></table>
            </table>

            <mat-paginator
              [length]="totalDisponibles"
              [pageSize]="pageSize"
              [pageSizeOptions]="[5, 10, 20, 50]"
              (page)="onPageChangeDisponibles($event)">
            </mat-paginator>
          </div>
        </div>

        <!-- Componentes en Uso -->
        <div class="componentes-en-uso-section">
          <h3>Componentes en Uso</h3>
          
          <div *ngIf="cargandoEnUso" class="loading-container">
            <mat-spinner diameter="40"></mat-spinner>
          </div>

          <div *ngIf="!cargandoEnUso && componentesEnUso.length === 0" class="no-data">
            <p>No hay componentes en uso actualmente</p>
          </div>

          <div *ngIf="!cargandoEnUso && componentesEnUso.length > 0">
            <table mat-table [dataSource]="componentesEnUso" class="mat-elevation-z8">
              
              <ng-container matColumnDef="ID_Componente">
                <th mat-header-cell *matHeaderCellDef> ID </th>
                <td mat-cell *matCellDef="let comp"> {{comp.ID_Componente | slice:0:8}}... </td>
              </ng-container>

              <ng-container matColumnDef="nombre">
                <th mat-header-cell *matHeaderCellDef> Nombre </th>
                <td mat-cell *matCellDef="let comp"> {{comp.nombre}} </td>
              </ng-container>

              <ng-container matColumnDef="tipo">
                <th mat-header-cell *matHeaderCellDef> Tipo </th>
                <td mat-cell *matCellDef="let comp"> {{comp.tipo}} </td>
              </ng-container>

              <ng-container matColumnDef="Nombre_Maquina">
                <th mat-header-cell *matHeaderCellDef> Máquina </th>
                <td mat-cell *matCellDef="let comp"> {{comp.Nombre_Maquina || 'N/A'}} </td>
              </ng-container>

              <ng-container matColumnDef="acciones">
                <th mat-header-cell *matHeaderCellDef> Acciones </th>
                <td mat-cell *matCellDef="let comp">
                  <button mat-raised-button color="warn" 
                          (click)="liberarComponente(comp)">
                    Liberar
                  </button>
                </td>
              </ng-container>

              <tr mat-header-row *matHeaderRowDef="displayedColumnsEnUso"></tr>
              <tr mat-row *matRowDef="let row; columns: displayedColumnsEnUso;"></tr>
            </table>
          </div>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .gestion-componentes-container {
      min-height: 100vh;
      background: linear-gradient(135deg, #07224c 0%, #124258 50%, #3b4a66 100%);
    }
    
    .content-wrapper {
      max-width: 1200px;
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
    
    .selected-machine-info {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 12px;
      padding: 1rem;
      margin-bottom: 1.5rem;
      color: white;
    }
    
    .selected-machine-info h3 {
      color: white;
      margin-bottom: 0.5rem;
    }
    
    .componentes-section,
    .componentes-en-uso-section {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
    }
    
    .componentes-section h3,
    .componentes-en-uso-section h3 {
      color: white;
      margin-bottom: 1rem;
    }
    
    .filtros-componentes {
      margin-bottom: 1rem;
    }
    
    .filtros-componentes mat-form-field {
      width: 200px;
    }
    
    table {
      width: 100%;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 8px;
      overflow: hidden;
    }
    
    .loading-container {
      display: flex;
      justify-content: center;
      padding: 2rem;
    }
    
    .no-data {
      text-align: center;
      padding: 2rem;
      color: rgba(255, 255, 255, 0.7);
    }
    
    @media (max-width: 768px) {
      .content-wrapper {
        padding: 1rem;
      }
      
      .filtros-componentes mat-form-field {
        width: 100%;
      }
    }
  `]
})
export class GestionComponentesComponent implements OnInit {
  private router = inject(Router);
  private tecnicoService = inject(TecnicoService);
  private authService = inject(AuthService);
  private snackBar = inject(MatSnackBar);
  
  user: User | null = null;
  selectedMachine: Maquina | null = null;
  
  // Componentes Disponibles
  componentesDisponibles: Componente[] = [];
  displayedColumnsDisponibles: string[] = ['ID_Componente', 'nombre', 'tipo', 'precio', 'acciones'];
  cargandoDisponibles = false;
  totalDisponibles = 0;
  pageSize = 10;
  currentPage = 0;
  filtroTipo = '';
  
  // Componentes en Uso
  componentesEnUso: Componente[] = [];
  displayedColumnsEnUso: string[] = ['ID_Componente', 'nombre', 'tipo', 'Nombre_Maquina', 'acciones'];
  cargandoEnUso = false;
  
  ngOnInit(): void {
    this.user = this.authService.getCurrentUser();
    this.cargarMquinaSeleccionada();
    if (this.user?.ID_Usuario) {
      this.cargarComponentesDisponibles();
      this.cargarComponentesEnUso();
    }
  }
  
  private cargarMquinaSeleccionada(): void {
    const storedMachine = localStorage.getItem('selectedMachine');
    if (storedMachine) {
      this.selectedMachine = JSON.parse(storedMachine);
    } else {
      this.snackBar.warning('No hay máquina seleccionada. Seleccione una máquina primero.', 'Cerrar');
    }
  }
  
  cargarComponentesDisponibles(): void {
    this.cargandoDisponibles = true;
    const tipoParam = this.filtroTipo || undefined;
    
    this.tecnicoService.getComponentesDisponibles(tipoParam, this.currentPage + 1, this.pageSize).subscribe({
      next: (response) => {
        this.componentesDisponibles = response.componentes;
        this.totalDisponibles = response.total;
        this.cargandoDisponibles = false;
      },
      error: () => {
        this.cargandoDisponibles = false;
        this.snackBar.error('Error al cargar componentes disponibles', 'Cerrar');
      }
    });
  }
  
  cargarComponentesEnUso(): void {
    this.cargandoEnUso = true;
    this.tecnicoService.getComponentesEnUso(this.user!.ID_Usuario, this.selectedMachine?.ID_Maquina).subscribe({
      next: (componentes) => {
        this.componentesEnUso = componentes;
        this.cargandoEnUso = false;
      },
      error: () => {
        this.cargandoEnUso = false;
        this.snackBar.error('Error al cargar componentes en uso', 'Cerrar');
      }
    });
  }
  
  componenteEnUso(componente: Componente): boolean {
    return this.componentesEnUso.some(c => c.ID_Componente === componente.ID_Componente);
  }
  
  usarComponente(componente: Componente): void {
    if (!this.selectedMachine) {
      this.snackBar.warning('Por favor, seleccione una máquina primero', 'Cerrar');
      return;
    }
    
    if (!confirm(`¿Está seguro de usar el componente ${componente.nombre} en la máquina ${this.selectedMachine.Nombre_Maquina}?`)) {
      return;
    }
    
    this.tecnicoService.usarComponente({
      ID_Componente: componente.ID_Componente,
      ID_Usuario: this.user!.ID_Usuario,
      ID_Maquina: this.selectedMachine.ID_Maquina
    }).subscribe({
      next: (success) => {
        if (success) {
          this.snackBar.success('Componente usado correctamente', 'Éxito');
          this.cargarComponentesDisponibles();
          this.cargarComponentesEnUso();
        } else {
          this.snackBar.error('Error al usar el componente', 'Cerrar');
        }
      },
      error: () => {
        this.snackBar.error('Error al usar el componente', 'Cerrar');
      }
    });
  }
  
  liberarComponente(componente: Componente): void {
    if (!confirm(`¿Está seguro de liberar el componente ${componente.nombre}?`)) {
      return;
    }
    
    this.tecnicoService.liberarComponente({
      ID_Componente: componente.ID_Componente,
      ID_Usuario: this.user!.ID_Usuario
    }).subscribe({
      next: (success) => {
        if (success) {
          this.snackBar.success('Componente liberado correctamente', 'Éxito');
          this.cargarComponentesDisponibles();
          this.cargarComponentesEnUso();
        } else {
          this.snackBar.error('Error al liberar el componente', 'Cerrar');
        }
      },
      error: () => {
        this.snackBar.error('Error al liberar el componente', 'Cerrar');
      }
    });
  }
  
  onPageChangeDisponibles(event: PageEvent): void {
    this.currentPage = event.pageIndex;
    this.pageSize = event.pageSize;
    this.cargarComponentesDisponibles();
  }
  
  regresar(): void {
    const user = this.authService.getCurrentUser();
    if (user?.Especialidad) {
      this.router.navigate([`/tecnico/${user.Especialidad.toLowerCase()}`]);
    } else {
      this.router.navigate(['/tecnico/ensamblador']);
    }
  }
}