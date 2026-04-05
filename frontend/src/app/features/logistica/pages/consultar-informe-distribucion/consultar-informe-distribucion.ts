/**
 * @fileoverview Consultar Informes de Distribución (Logística)
 * @description Permite consultar y filtrar informes de distribución de máquinas
 * @component ConsultarInformeDistribucionLogisticaComponent
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
import { LogisticaService } from '../../services/logistica';
import { Maquina } from '@core/models/maquina.model';
import { Comercio } from '@core/models/recaudacion.model';

interface InformeDistribucion {
  ID_Distribucion: string; ID_Maquina: string; Nombre_Maquina: string;
  ID_Tecnico: string; Nombre_Tecnico: string; ID_Comercio: string;
  Nombre_Comercio: string; fecha_alta: string; fecha_baja: string | null; estado: string;
}

@Component({
  selector: 'app-consultar-informe-distribucion-logistica',
  standalone: true,
  imports: [
    CommonModule, ReactiveFormsModule, MatTableModule, MatPaginatorModule, MatSortModule,
    MatFormFieldModule, MatInputModule, MatSelectModule, MatDatepickerModule, MatNativeDateModule,
    MatButtonModule, MatIconModule, MatProgressSpinnerModule, AdminHeaderComponent
  ],
  templateUrl: './consultar-informe-distribucion.html',
  styleUrls: ['./consultar-informe-distribucion.css']
})
export class ConsultarInformeDistribucionLogisticaComponent implements OnInit, AfterViewInit {
  private fb = inject(FormBuilder);
  private router = inject(Router);
  private logisticaService = inject(LogisticaService);
  private snackBar = inject(MatSnackBar);
  
  filtrosForm!: FormGroup;
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
      fecha_inicio: [''], fecha_fin: [''], ID_Maquina: [''], ID_Comercio: [''], estado: ['']
    });
  }
  
  private cargarMaquinas(): void {
    this.logisticaService.getMaquinasPorEstado('Operativa').subscribe({
      next: (data) => { this.maquinas = data; },
      error: () => { this.snackBar.error('Error al cargar máquinas', 'Cerrar'); }
    });
  }
  
  private cargarComercios(): void {
    this.logisticaService.getComercios().subscribe({
      next: (data) => { this.comercios = data; },
      error: () => { this.snackBar.error('Error al cargar comercios', 'Cerrar'); }
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
    
    this.logisticaService.getInformesDistribucion(params).subscribe({
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
    if (this.paginator) this.paginator.firstPage();
    this.cargarInformes();
  }
  
  limpiarFiltros(): void {
    this.filtrosForm.reset({ fecha_inicio: '', fecha_fin: '', ID_Maquina: '', ID_Comercio: '', estado: '' });
    this.buscarInformes();
  }
  
  onPageChange(event: PageEvent): void {
    this.currentPage = event.pageIndex;
    this.pageSize = event.pageSize;
    this.cargarInformes();
  }
  
  exportarExcel(): void {
    const data = this.dataSource.data;
    const headers = ['ID', 'Máquina', 'Técnico', 'Comercio', 'Fecha Alta', 'Fecha Baja', 'Estado'];
    const csvData = data.map(row => [
      row.ID_Distribucion, row.Nombre_Maquina, row.Nombre_Tecnico || 'No asignado',
      row.Nombre_Comercio || 'Desconocido', new Date(row.fecha_alta).toLocaleString(),
      row.fecha_baja ? new Date(row.fecha_baja).toLocaleString() : 'No dada de baja',
      row.estado || 'Distribuyendose'
    ]);
    const csvContent = [headers, ...csvData].map(row => row.join(',')).join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `informes_distribucion_${new Date().toISOString().slice(0, 19)}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    this.snackBar.open('Exportación completada', 'Cerrar', { duration: 3000 });
  }
  
  regresar(): void {
    this.router.navigate(['/logistica/dashboard']);
  }
}