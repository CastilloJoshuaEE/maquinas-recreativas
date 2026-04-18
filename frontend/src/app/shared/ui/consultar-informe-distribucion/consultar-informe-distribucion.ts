/**
 * @fileoverview Consultar Informes de Distribución (Compartido)
 * @description Componente reutilizable para consultar informes de distribución
 * @component ConsultarInformeDistribucionComponent
 */

import { Component, OnInit, ViewChild, AfterViewInit, inject, Input } from '@angular/core';
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
import { AdminHeaderComponent } from '../admin-header/admin-header';
import { LogisticaService } from '@features/logistica/services/logistica';
import { ContabilidadService } from '@features/contabilidad/services/contabilidad';
import { AuthService } from '@core/services/auth';
import { Maquina } from '@core/models/maquina.model';
import { Comercio } from '@core/models/recaudacion.model';

interface InformeDistribucion {
  ID_Distribucion: string; ID_Maquina: string; Nombre_Maquina: string;
  ID_Tecnico: string; Nombre_Tecnico: string; ID_Comercio: string;
  Nombre_Comercio: string; fecha_alta: string; fecha_baja: string | null; estado: string;
}

@Component({
  selector: 'app-consultar-informe-distribucion',
  standalone: true,
  imports: [
    CommonModule, ReactiveFormsModule, MatTableModule, MatPaginatorModule, MatSortModule,
    MatFormFieldModule, MatInputModule, MatSelectModule, MatDatepickerModule, MatNativeDateModule,
    MatButtonModule, MatIconModule, MatProgressSpinnerModule, AdminHeaderComponent
  ],
  templateUrl: './consultar-informe-distribucion.html',
  styleUrls: ['./consultar-informe-distribucion.css']
})
export class ConsultarInformeDistribucionComponent implements OnInit, AfterViewInit {
  private fb = inject(FormBuilder);
  private router = inject(Router);
  private authService = inject(AuthService);
  private snackBar = inject(MatSnackBar);
  private logisticaService = inject(LogisticaService);
  private contabilidadService = inject(ContabilidadService);
  
  // Determinar el rol del usuario actual
  userRole: string = '';
  
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
    this.userRole = this.authService.getCurrentUser()?.tipo || '';
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
    // Usar el servicio según el rol
    if (this.userRole === 'Contabilidad') {
      this.contabilidadService.getMaquinasRecaudacion().subscribe({
        next: (data) => { this.maquinas = data; },
        error: () => { this.snackBar.open('Error al cargar máquinas', 'Cerrar', { duration: 3000 }); }
      });
    } else {
      this.logisticaService.getMaquinasPorEstado('Operativa').subscribe({
        next: (data) => { this.maquinas = data; },
        error: () => { this.snackBar.open('Error al cargar máquinas', 'Cerrar', { duration: 3000 }); }
      });
    }
  }
  
  private cargarComercios(): void {
    const service = this.userRole === 'Contabilidad' ? this.contabilidadService : this.logisticaService;
    service.getComercios().subscribe({
      next: (data) => { 
        this.comercios = data; 
        if (data.length === 0) {
          this.snackBar.open('No hay comercios registrados', 'Cerrar', { duration: 3000 });
        }
      },
      error: (err) => { 
        console.error('Error cargando comercios:', err);
        this.snackBar.open('Error al cargar comercios', 'Cerrar', { duration: 3000 }); 
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
    
    const service = this.userRole === 'Contabilidad' ? this.contabilidadService : this.logisticaService;
    service.getInformesDistribucion(params).subscribe({
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
      this.escapeCsvValue(row.ID_Distribucion),
      this.escapeCsvValue(row.Nombre_Maquina),
      this.escapeCsvValue(row.Nombre_Tecnico || 'No asignado'),
      this.escapeCsvValue(row.Nombre_Comercio || 'Desconocido'),
      this.formatDateForCsv(row.fecha_alta),
      row.fecha_baja ? this.formatDateForCsv(row.fecha_baja) : 'No dada de baja',
      this.escapeCsvValue(row.estado || 'Distribuyendose')
    ]);
    
    const csvContent = [headers, ...csvData].map(row => row.join(';')).join('\n');
    const blob = new Blob(["\uFEFF" + csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `informes_distribucion_${this.formatDateForFilename(new Date())}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
    this.snackBar.open('Exportación completada', 'Cerrar', { duration: 3000 });
  }
  
  private escapeCsvValue(value: string): string {
    if (!value) return '';
    const escaped = value.replace(/"/g, '""');
    if (escaped.includes(';') || escaped.includes('"') || escaped.includes('\n')) {
      return `"${escaped}"`;
    }
    return escaped;
  }
  
  private formatDateForCsv(dateStr: string): string {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return `${date.getDate()}/${date.getMonth() + 1}/${date.getFullYear()} ${date.getHours()}:${date.getMinutes().toString().padStart(2, '0')}`;
  }
  
  private formatDateForFilename(date: Date): string {
    return `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}-${date.getDate().toString().padStart(2, '0')}_${date.getHours().toString().padStart(2, '0')}-${date.getMinutes().toString().padStart(2, '0')}-${date.getSeconds().toString().padStart(2, '0')}`;
  }
  
  regresar(): void {
    // Redirigir según el rol
    if (this.userRole === 'Contabilidad') {
      this.router.navigate(['/contabilidad/dashboard']);
    } else {
      this.router.navigate(['/logistica/dashboard']);
    }
  }
}