/**
 * @fileoverview Consulta de Recaudaciones
 * @description Página para consultar, filtrar, editar y eliminar recaudaciones
 * @component ConsultarRecaudacionesComponent
 */

import { Component, OnInit, ViewChild, AfterViewInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule,FormBuilder, FormGroup, ReactiveFormsModule } from '@angular/forms';
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
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header';
import { ContabilidadService } from '../../services/contabilidad';
import { Recaudacion } from '@core/models/recaudacion.model';
import { Maquina } from '@core/models/maquina.model';
@Component({
  selector: 'app-consultar-recaudaciones',
  standalone: true,
  imports: [
    CommonModule,FormsModule,MatSelectModule, ReactiveFormsModule, MatTableModule, MatPaginatorModule, MatSortModule,
    MatFormFieldModule, MatInputModule, MatSelectModule, MatDatepickerModule, MatNativeDateModule,
    MatButtonModule, MatIconModule, MatProgressSpinnerModule, AdminHeaderComponent
  ],
  templateUrl: './consultar-recaudaciones.html',
  styleUrls: ['./consultar-recaudaciones.css']
})
export class ConsultarRecaudacionesComponent implements OnInit, AfterViewInit {
  private fb = inject(FormBuilder);
  private router = inject(Router);
  private contabilidadService = inject(ContabilidadService);
  private snackBar = inject(MatSnackBar);
  
  filtrosForm!: FormGroup;
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
      next: (data) => { this.maquinas = data; },
      error: () => { 
        this.snackBar.open('Error al cargar máquinas', 'Cerrar', { duration: 3000 });
      }
    });
  }
  
  cargarRecaudaciones(): void {
    this.loading = true;
    this.error = '';
    const params: any = { limit: this.pageSize, offset: this.currentPage * this.pageSize };
    const filtros = this.filtrosForm.value;
    if (filtros.fecha_inicio) params.fecha_inicio = filtros.fecha_inicio;
    if (filtros.fecha_fin) params.fecha_fin = filtros.fecha_fin;
    if (filtros.ID_Maquina) params.ID_Maquina = filtros.ID_Maquina;
    if (filtros.Tipo_Comercio) params.Tipo_Comercio = filtros.Tipo_Comercio;
    
    this.contabilidadService.getRecaudaciones(params).subscribe({
      next: (data) => {
        this.dataSource.data = data.sort((a, b) => new Date(b.fecha).getTime() - new Date(a.fecha).getTime());
        this.totalItems = this.dataSource.data.length;
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
    if (this.paginator) this.paginator.firstPage();
    this.cargarRecaudaciones();
  }
  
  limpiarFiltros(): void {
    this.filtrosForm.reset({ fecha_inicio: '', fecha_fin: '', ID_Maquina: '', Tipo_Comercio: '' });
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
    if (!confirm(`¿Está seguro de eliminar esta recaudación?\nMáquina: ${recaudacion.Nombre_Maquina}\nMonto: $${recaudacion.Monto_Total}`)) return;
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