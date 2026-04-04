/**
 * @fileoverview Consulta de Recaudaciones
 * @description Página para consultar, filtrar, editar y eliminar recaudaciones
 * @component ConsultarRecaudacionesComponent
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
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header.component';
import { ContabilidadService } from '../../services/contabilidad.service';
import { Recaudacion, Maquina } from '@core/models/recaudacion.model';

@Component({
  selector: 'app-consultar-recaudaciones',
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
    MatDialogModule,
    AdminHeaderComponent
  ],
  template: `
    <div class="consultar-container">
      <app-admin-header></app-admin-header>
      
      <div class="content-wrapper">
        <div class="page-header">
          <button mat-icon-button (click)="regresar()" class="back-button">
            <mat-icon>arrow_back</mat-icon>
          </button>
          <h2>Consultar Recaudaciones</h2>
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
              <mat-label>Tipo Comercio</mat-label>
              <mat-select formControlName="Tipo_Comercio">
                <mat-option value="">Todos</mat-option>
                <mat-option value="Minorista">Minorista</mat-option>
                <mat-option value="Mayorista">Mayorista</mat-option>
              </mat-select>
              <mat-icon matPrefix>store</mat-icon>
            </mat-form-field>

            <div class="filter-actions">
              <button mat-raised-button color="primary" (click)="buscarRecaudaciones()" [disabled]="loading">
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
            <p>Cargando recaudaciones...</p>
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
                
                <!-- Comercio Column -->
                <ng-container matColumnDef="Nombre_Comercio">
                  <th mat-header-cell *matHeaderCellDef mat-sort-header> Comercio </th>
                  <td mat-cell *matCellDef="let row"> {{row.Nombre_Comercio}} </td>
                </ng-container>

                <!-- Máquina Column -->
                <ng-container matColumnDef="Nombre_Maquina">
                  <th mat-header-cell *matHeaderCellDef mat-sort-header> Máquina </th>
                  <td mat-cell *matCellDef="let row"> {{row.Nombre_Maquina}} </td>
                </ng-container>

                <!-- Tipo Column -->
                <ng-container matColumnDef="Tipo_Comercio">
                  <th mat-header-cell *matHeaderCellDef mat-sort-header> Tipo </th>
                  <td mat-cell *matCellDef="let row">
                    <span class="tipo-badge" [ngClass]="{'minorista': row.Tipo_Comercio === 'Minorista', 'mayorista': row.Tipo_Comercio === 'Mayorista'}">
                      {{row.Tipo_Comercio}}
                    </span>
                  </td>
                </ng-container>

                <!-- Monto Total Column -->
                <ng-container matColumnDef="Monto_Total">
                  <th mat-header-cell *matHeaderCellDef mat-sort-header> Monto Total </th>
                  <td mat-cell *matCellDef="let row" class="monto-total"> ${{row.Monto_Total | number:'1.2-2'}} </td>
                </ng-container>

                <!-- Monto Empresa Column -->
                <ng-container matColumnDef="Monto_Empresa">
                  <th mat-header-cell *matHeaderCellDef mat-sort-header> Monto Empresa </th>
                  <td mat-cell *matCellDef="let row" class="monto-empresa"> ${{row.Monto_Empresa | number:'1.2-2'}} </td>
                </ng-container>

                <!-- Fecha Column -->
                <ng-container matColumnDef="fecha">
                  <th mat-header-cell *matHeaderCellDef mat-sort-header> Fecha </th>
                  <td mat-cell *matCellDef="let row"> {{row.fecha | date:'dd/MM/yyyy HH:mm'}} </td>
                </ng-container>

                <!-- Detalles Column -->
                <ng-container matColumnDef="detalle">
                  <th mat-header-cell *matHeaderCellDef> Detalles </th>
                  <td mat-cell *matCellDef="let row"> {{row.detalle || '-'}} </td>
                </ng-container>

                <!-- Acciones Column -->
                <ng-container matColumnDef="acciones">
                  <th mat-header-cell *matHeaderCellDef> Acciones </th>
                  <td mat-cell *matCellDef="let row">
                    <button mat-icon-button color="primary" (click)="verInforme(row)" matTooltip="Ver Informe" *ngIf="row.ID_Informe">
                      <mat-icon>description</mat-icon>
                    </button>
                    <button mat-icon-button color="accent" (click)="levantarInforme(row)" matTooltip="Levantar Informe" *ngIf="!row.ID_Informe">
                      <mat-icon>post_add</mat-icon>
                    </button>
                    <button mat-icon-button color="primary" (click)="editarRecaudacion(row)" matTooltip="Editar">
                      <mat-icon>edit</mat-icon>
                    </button>
                    <button mat-icon-button color="warn" (click)="eliminarRecaudacion(row)" matTooltip="Eliminar">
                      <mat-icon>delete</mat-icon>
                    </button>
                  </td>
                </ng-container>

                <tr mat-header-row *matHeaderRowDef="displayedColumns"></tr>
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
              <p>No se encontraron recaudaciones</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .consultar-container {
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
    
    .tipo-badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
    }
    
    .tipo-badge.minorista {
      background: #d4edda;
      color: #155724;
    }
    
    .tipo-badge.mayorista {
      background: #cce5ff;
      color: #004085;
    }
    
    .monto-total {
      font-weight: bold;
      color: #28a745;
    }
    
    .monto-empresa {
      color: #4f6bed;
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
export class ConsultarRecaudacionesComponent implements OnInit, AfterViewInit {
  private fb = inject(FormBuilder);
  private router = inject(Router);
  private contabilidadService = inject(ContabilidadService);
  private snackBar = inject(MatSnackBar);
  private dialog = inject(MatDialog);
  
  filtrosForm: FormGroup;
  dataSource = new MatTableDataSource<Recaudacion>([]);
  displayedColumns: string[] = ['Nombre_Comercio', 'Nombre_Maquina', 'Tipo_Comercio', 'Monto_Total', 'Monto_Empresa', 'fecha', 'detalle', 'acciones'];
  
  maquinas: Maquina[] = [];
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
    this.cargarRecaudaciones();
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
      Tipo_Comercio: ['']
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
  
  cargarRecaudaciones(): void {
    this.loading = true;
    this.error = '';
    
    const params: any = {
      limit: this.pageSize,
      offset: this.currentPage * this.pageSize
    };
    
    const filtros = this.filtrosForm.value;
    if (filtros.fecha_inicio) params.fecha_inicio = filtros.fecha_inicio;
    if (filtros.fecha_fin) params.fecha_fin = filtros.fecha_fin;
    if (filtros.ID_Maquina) params.ID_Maquina = filtros.ID_Maquina;
    if (filtros.Tipo_Comercio) params.Tipo_Comercio = filtros.Tipo_Comercio;
    
    this.contabilidadService.getRecaudaciones(params).subscribe({
      next: (data) => {
        const recaudacionesOrdenadas = data.sort((a, b) => 
          new Date(b.fecha).getTime() - new Date(a.fecha).getTime()
        );
        this.dataSource.data = recaudacionesOrdenadas;
        this.totalItems = recaudacionesOrdenadas.length;
        this.loading = false;
      },
      error: (err) => {
        this.error = err.message || 'Error al cargar recaudaciones';
        this.loading = false;
      }
    });
  }
  
  buscarRecaudaciones(): void {
    this.currentPage = 0;
    if (this.paginator) {
      this.paginator.firstPage();
    }
    this.cargarRecaudaciones();
  }
  
  limpiarFiltros(): void {
    this.filtrosForm.reset({
      fecha_inicio: '',
      fecha_fin: '',
      ID_Maquina: '',
      Tipo_Comercio: ''
    });
    this.buscarRecaudaciones();
  }
  
  onPageChange(event: PageEvent): void {
    this.currentPage = event.pageIndex;
    this.pageSize = event.pageSize;
    this.cargarRecaudaciones();
  }
  
  regresar(): void {
    this.router.navigate(['/contabilidad/gestion-recaudacion']);
  }
  
  verInforme(recaudacion: Recaudacion): void {
    this.router.navigate([`/contabilidad/ver-informe/${recaudacion.ID_Recaudacion}`]);
  }
  
  levantarInforme(recaudacion: Recaudacion): void {
    this.router.navigate([`/contabilidad/levantar-informe/${recaudacion.ID_Recaudacion}`]);
  }
  
  editarRecaudacion(recaudacion: Recaudacion): void {
    this.router.navigate([`/contabilidad/actualizar-recaudacion/${recaudacion.ID_Recaudacion}`]);
  }
  
  eliminarRecaudacion(recaudacion: Recaudacion): void {
    const confirmDelete = confirm(`¿Está seguro de eliminar esta recaudación?\nMáquina: ${recaudacion.Nombre_Maquina}\nMonto: $${recaudacion.Monto_Total}`);
    
    if (!confirmDelete) return;
    
    this.loading = true;
    this.contabilidadService.eliminarRecaudacion(recaudacion.ID_Recaudacion).subscribe({
      next: (response) => {
        if (response.success) {
          this.snackBar.open('Recaudación eliminada correctamente', 'Cerrar', { duration: 3000 });
          this.cargarRecaudaciones();
        } else {
          this.snackBar.open(response.message || 'Error al eliminar recaudación', 'Cerrar', { duration: 3000 });
        }
        this.loading = false;
      },
      error: (err) => {
        this.snackBar.open(err.message || 'Error al eliminar recaudación', 'Cerrar', { duration: 3000 });
        this.loading = false;
      }
    });
  }
}