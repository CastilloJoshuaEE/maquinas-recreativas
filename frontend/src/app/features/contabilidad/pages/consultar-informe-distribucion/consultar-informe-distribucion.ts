/**
 * @fileoverview Consultar Informes de Distribución
 * @description Permite consultar y filtrar informes de distribución de máquinas
 * @component ConsultarInformeDistribucionComponent
 */

import { Component, OnInit, ViewChild, AfterViewInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { MatTableModule, MatTableDataSource } from '@angular/material/table';
import { MatPaginatorModule, MatPaginator, PageEvent } from '@angular/material/paginator';
import { MatSortModule, MatSort } from '@angular/material/sort';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatNativeDateModule } from '@angular/material/core';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header.component';
import { ContabilidadService } from '../../services/contabilidad.service';
import { Maquina, Comercio } from '@core/models/recaudacion.model';

interface InformeDistribucion {
  ID_Distribucion: string;
  ID_Maquina: string;
  Nombre_Maquina: string;
  ID_Tecnico: string;
  Nombre_Tecnico: string;
  ID_Comercio: string;
  Nombre_Comercio: string;
  fecha_alta: string;
  fecha_baja: string | null;
  estado: string;
}

@Component({
  selector: 'app-consultar-informe-distribucion',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatTableModule,
    MatPaginatorModule,
    MatSortModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatDatepickerModule,
    MatNativeDateModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    AdminHeaderComponent
  ],
  template: `
    <div class="consultar-distribucion-container">
      <app-admin-header></app-admin-header>
      
      <div class="content-wrapper">
        <div class="page-header">
          <button mat-icon-button (click)="regresar()" class="back-button">
            <mat-icon>arrow_back</mat-icon>
          </button>
          <h2>Consultar Informes de Distribución</h2>
        </div>

        <!-- Filtros -->
        <div class="filtros-container">
          <form [formGroup]="filtrosForm" class="filtros-form">
            <mat-form-field appearance="outline">
              <mat-label>Fecha Inicio</mat-label>
              <input matInput [matDatepicker]="pickerInicio" formControlName="fecha_inicio">
              <mat-datepicker-toggle matSuffix [for]="pickerInicio"></mat-datepicker-toggle>
              <mat-datepicker #pickerInicio></mat-datepicker>
              <mat-icon matPrefix>calendar_today</mat-icon>
            </mat-form-field>

            <mat-form-field appearance="outline">
              <mat-label>Fecha Fin</mat-label>
              <input matInput [matDatepicker]="pickerFin" formControlName="fecha_fin">
              <mat-datepicker-toggle matSuffix [for]="pickerFin"></mat-datepicker-toggle>
              <mat-datepicker #pickerFin></mat-datepicker>
              <mat-icon matPrefix>calendar_today</mat-icon>
            </mat-form-field>

            <mat-form-field appearance="outline">
              <mat-label>Máquina</mat-label>
              <mat-select formControlName="ID_Maquina">
                <mat-option value="">Todas</mat-option>
                <mat-option *ngFor="let maquina of maquinas" [value]="maquina.ID_Maquina">
                  {{ maquina.Nombre_Maquina }}
                </mat-option>
              </mat-select>
              <mat-icon matPrefix>videogame_asset</mat-icon>
            </mat-form-field>

            <mat-form-field appearance="outline">
              <mat-label>Comercio</mat-label>
              <mat-select formControlName="ID_Comercio">
                <mat-option value="">Todos los comercios</mat-option>
                <mat-option *ngFor="let comercio of comercios" [value]="comercio.ID_Comercio">
                  {{ comercio.Nombre }}
                </mat-option>
              </mat-select>
              <mat-icon matPrefix>store</mat-icon>
            </mat-form-field>

            <mat-form-field appearance="outline">
              <mat-label>Estado</mat-label>
              <mat-select formControlName="estado">
                <mat-option value="">Todos</mat-option>
                <mat-option value="Operativa">Operativa</mat-option>
                <mat-option value="Retirada">Retirada</mat-option>
                <mat-option value="No operativa">No operativa</mat-option>
              </mat-select>
              <mat-icon matPrefix>info</mat-icon>
            </mat-form-field>

            <div class="filter-actions">
              <button mat-raised-button color="primary" (click)="buscarInformes()" [disabled]="loading">
                <mat-icon>search</mat-icon>
                Buscar
              </button>
              <button mat-raised-button (click)="limpiarFiltros()" [disabled]="loading">
                <mat-icon>clear</mat-icon>
                Limpiar
              </button>
            </div>
          </form>
        </div>

        <!-- Tabla de resultados -->
        <div class="tabla-container">
          <div *ngIf="loading" class="loading-container">
            <mat-spinner diameter="40"></mat-spinner>
            <p>Cargando informes...</p>
          </div>

          <div *ngIf="!loading && error" class="error-message">
            {{ error }}
          </div>

          <div *ngIf="!loading && !error">
            <div class="table-header">
              <h3>Resultados ({{ dataSource.data.length }})</h3>
            </div>

            <div class="mat-elevation-z8">
              <table mat-table [dataSource]="dataSource" matSort>
                
                <!-- ID Distribución Column -->
                <ng-container matColumnDef="ID_Distribucion">
                  <th mat-header-cell *matHeaderCellDef mat-sort-header> ID </th>
                  <td mat-cell *matCellDef="let row"> {{row.ID_Distribucion | slice:0:8}}... </td>
                </ng-container>

                <!-- Máquina Column -->
                <ng-container matColumnDef="Nombre_Maquina">
                  <th mat-header-cell *matHeaderCellDef mat-sort-header> Máquina </th>
                  <td mat-cell *matCellDef="let row"> {{row.Nombre_Maquina}} </td>
                </ng-container>

                <!-- Técnico Column -->
                <ng-container matColumnDef="Nombre_Tecnico">
                  <th mat-header-cell *matHeaderCellDef mat-sort-header> Técnico Comprobador </th>
                  <td mat-cell *matCellDef="let row"> {{row.Nombre_Tecnico || 'No asignado'}} </td>
                </ng-container>

                <!-- Comercio Column -->
                <ng-container matColumnDef="Nombre_Comercio">
                  <th mat-header-cell *matHeaderCellDef mat-sort-header> Comercio </th>
                  <td mat-cell *matCellDef="let row"> {{row.Nombre_Comercio || 'Desconocido'}} </td>
                </ng-container>

                <!-- Fecha Alta Column -->
                <ng-container matColumnDef="fecha_alta">
                  <th mat-header-cell *matHeaderCellDef mat-sort-header> Fecha Alta </th>
                  <td mat-cell *matCellDef="let row"> {{row.fecha_alta | date:'dd/MM/yyyy HH:mm'}} </td>
                </ng-container>

                <!-- Fecha Baja Column -->
                <ng-container matColumnDef="fecha_baja">
                  <th mat-header-cell *matHeaderCellDef mat-sort-header> Fecha Baja </th>
                  <td mat-cell *matCellDef="let row"> 
                    <span *ngIf="row.fecha_baja; else noBaja">
                      {{row.fecha_baja | date:'dd/MM/yyyy HH:mm'}}
                    </span>
                    <ng-template #noBaja>
                      <span class="text-muted">No se ha dado de baja</span>
                    </ng-template>
                  </td>
                </ng-container>

                <!-- Estado Column -->
                <ng-container matColumnDef="estado">
                  <th mat-header-cell *matHeaderCellDef mat-sort-header> Estado </th>
                  <td mat-cell *matCellDef="let row">
                    <span class="estado-badge" [ngClass]="{
                      'estado-operativa': row.estado === 'Operativa',
                      'estado-retirada': row.estado === 'Retirada',
                      'estado-no-operativa': row.estado === 'No operativa'
                    }">
                      {{ row.estado || 'Distribuyendose' }}
                    </span>
                  </td>
                </ng-container>

                <tr mat-header-row *matHeaderRowDef="displayedColumns"><tr>
                <tr mat-row *matRowDef="let row; columns: displayedColumns;"></tr>
              </table>

              <mat-paginator
                [length]="totalItems"
                [pageSize]="pageSize"
                [pageSizeOptions]="[5, 10, 20, 50, 100]"
                (page)="onPageChange($event)"
                showFirstLastButtons>
              </mat-paginator>
            </div>

            <div *ngIf="dataSource.data.length === 0" class="no-results">
              <mat-icon>info</mat-icon>
              <p>No se encontraron informes de distribución</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .consultar-distribucion-container {
      min-height: 100vh;
      background: linear-gradient(135deg, #07224c 0%, #124258 50%, #3b4a66 100%);
    }
    
    .content-wrapper {
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
    
    .filtros-container {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 2rem;
    }
    
    .filtros-form {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      align-items: center;
    }
    
    .filter-actions {
      display: flex;
      gap: 0.5rem;
    }
    
    .tabla-container {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }
    
    .table-header {
      padding: 1rem;
      border-bottom: 1px solid #e0e0e0;
    }
    
    .table-header h3 {
      margin: 0;
      color: #2c3e50;
    }
    
    table {
      width: 100%;
    }
    
    .estado-badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
    }
    
    .estado-operativa {
      background: #d4edda;
      color: #155724;
    }
    
    .estado-retirada {
      background: #f8d7da;
      color: #721c24;
    }
    
    .estado-no-operativa {
      background: #fff3cd;
      color: #856404;
    }
    
    .text-muted {
      color: #999;
      font-style: italic;
    }
    
    .loading-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 3rem;
    }
    
    .error-message {
      padding: 1rem;
      margin: 1rem;
      background: #f8d7da;
      color: #721c24;
      border-radius: 8px;
    }
    
    .no-results {
      text-align: center;
      padding: 3rem;
      color: #666;
    }
    
    .no-results mat-icon {
      font-size: 3rem;
      width: auto;
      height: auto;
      margin-bottom: 1rem;
    }
    
    @media (max-width: 768px) {
      .content-wrapper {
        padding: 1rem;
      }
      
      .filtros-form {
        grid-template-columns: 1fr;
      }
      
      .filter-actions {
        justify-content: flex-end;
      }
    }
  `]
})
export class ConsultarInformeDistribucionComponent implements OnInit, AfterViewInit {
  private fb = inject(FormBuilder);
  private router = inject(Router);
  private contabilidadService = inject(ContabilidadService);
  private snackBar = inject(MatSnackBar);
  
  filtrosForm: FormGroup;
  dataSource = new MatTableDataSource<InformeDistribucion>([]);
  displayedColumns: string[] = ['ID_Distribucion', 'Nombre_Maquina', 'Nombre_Tecnico', 'Nombre_Comercio', 'fecha_alta', 'fecha_baja', 'estado'];
  
  maquinas: Maquina[] = [];
  comercios: Comercio[] = [];
  loading = false;
  error = '';
  totalItems = 0;
  pageSize = 10;
  currentPage = 0;
  
  @ViewChild(MatPaginator) paginator!: MatPaginator;
  @ViewChild(MatSort) sort!: MatSort;
  
  ngOnInit(): void {
    this.initForm();
    this.cargarMaquinas();
    this.cargarComercios();
    this.cargarInformes();
  }
  
  ngAfterViewInit(): void {
    this.dataSource.paginator = this.paginator;
    this.dataSource.sort = this.sort;
  }
  
  private initForm(): void {
    this.filtrosForm = this.fb.group({
      fecha_inicio: [''],
      fecha_fin: [''],
      ID_Maquina: [''],
      ID_Comercio: [''],
      estado: ['']
    });
  }
  
  private cargarMaquinas(): void {
    this.contabilidadService.getMaquinasRecaudacion().subscribe({
      next: (data) => {
        this.maquinas = data;
      },
      error: () => {
        this.snackBar.error('Error al cargar máquinas', 'Cerrar');
      }
    });
  }
  
  private cargarComercios(): void {
    this.contabilidadService.getComercios().subscribe({
      next: (data) => {
        this.comercios = data;
      },
      error: () => {
        this.snackBar.error('Error al cargar comercios', 'Cerrar');
      }
    });
  }
  
  cargarInformes(): void {
    this.loading = true;
    this.error = '';
    
    const params: any = {};
    
    const filtros = this.filtrosForm.value;
    if (filtros.fecha_inicio) params.fecha_inicio = filtros.fecha_inicio;
    if (filtros.fecha_fin) params.fecha_fin = filtros.fecha_fin;
    if (filtros.ID_Maquina) params.ID_Maquina = filtros.ID_Maquina;
    if (filtros.ID_Comercio) params.ID_Comercio = filtros.ID_Comercio;
    if (filtros.estado) params.estado = filtros.estado;
    
    this.contabilidadService.getInformesDistribucion(params).subscribe({
      next: (data) => {
        this.dataSource.data = data;
        this.totalItems = data.length;
        this.loading = false;
      },
      error: (err) => {
        this.error = err.message || 'Error al cargar informes';
        this.loading = false;
      }
    });
  }
  
  buscarInformes(): void {
    this.currentPage = 0;
    if (this.paginator) {
      this.paginator.firstPage();
    }
    this.cargarInformes();
  }
  
  limpiarFiltros(): void {
    this.filtrosForm.reset({
      fecha_inicio: '',
      fecha_fin: '',
      ID_Maquina: '',
      ID_Comercio: '',
      estado: ''
    });
    this.buscarInformes();
  }
  
  onPageChange(event: PageEvent): void {
    this.currentPage = event.pageIndex;
    this.pageSize = event.pageSize;
    this.cargarInformes();
  }
  
  regresar(): void {
    this.router.navigate(['/contabilidad/dashboard']);
  }
}