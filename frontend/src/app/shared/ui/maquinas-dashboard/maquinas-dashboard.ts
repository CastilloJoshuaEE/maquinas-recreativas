import { Component, OnInit, inject, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatTooltipModule } from '@angular/material/tooltip';
import { MatChipsModule } from '@angular/material/chips';
import { MaquinasSharedService } from '@core/services/maquina';
import { Maquina } from '@core/models/maquina.model';
import { AuthService } from '@core/services/auth';

@Component({
  selector: 'app-maquinas-dashboard',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatTooltipModule,
    MatChipsModule
  ],
  templateUrl: './maquinas-dashboard.html',
  styleUrls: ['./maquinas-dashboard.css']
})
export class MaquinasDashboardComponent implements OnInit {
  private maquinasService = inject(MaquinasSharedService);
  private authService = inject(AuthService);
  private snackBar = inject(MatSnackBar);

  @Input() showActions: boolean = true;
  @Input() filterEstado?: string;
  @Input() filterEtapa?: string;
  @Output() maquinaSeleccionada = new EventEmitter<Maquina>();
  @Output() abrirModalCrear = new EventEmitter<void>();
  @Output() abrirModalEditar = new EventEmitter<Maquina>();
  @Output() verHistorial = new EventEmitter<Maquina>();

  maquinas: Maquina[] = [];
  maquinasFiltradas: Maquina[] = [];
  cargando = false;
  
  // Filtros locales
  searchTerm = '';
  estadoFilter = '';
  etapaFilter = '';

  // Estados de visualización
  mostrarTabla = true;
  selectedMaquina: Maquina | null = null;

  // Permisos
  puedeEditar = false;
  puedeCrear = false;
  puedeEliminar = false;

  ngOnInit(): void {
    this.cargarMaquinas();
    this.verificarPermisos();
  }

  private verificarPermisos(): void {
    const user = this.authService.getCurrentUser();
    const rol = user?.tipo || '';
    // Logística y técnicos pueden editar/crear
    this.puedeCrear = rol === 'Logistica' || rol === 'Tecnico' || rol === 'Administrador';
    this.puedeEditar = rol === 'Logistica' || rol === 'Tecnico' || rol === 'Administrador';
    this.puedeEliminar = rol === 'Administrador'; // Solo admin puede eliminar
  }

  cargarMaquinas(): void {
    this.cargando = true;
    const params: any = {};
    if (this.filterEstado) params.estado = this.filterEstado;
    if (this.filterEtapa) params.etapa = this.filterEtapa;

    this.maquinasService.getMaquinas(params).subscribe({
      next: (response) => {
        this.maquinas = response.maquinas || [];
        this.aplicarFiltros();
        this.cargando = false;
      },
      error: (error) => {
        console.error('Error cargando máquinas:', error);
        this.snackBar.open('Error al cargar las máquinas', 'Cerrar', { duration: 3000 });
        this.cargando = false;
      }
    });
  }

aplicarFiltros(): void {
  this.maquinasFiltradas = this.maquinas.filter(maq => {
    const matchesSearch = !this.searchTerm || 
      maq.Nombre_Maquina.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
      maq.tipo?.toLowerCase().includes(this.searchTerm.toLowerCase()) ||  // tipo en minúscula
      maq.NombreComercio?.toLowerCase().includes(this.searchTerm.toLowerCase());
    
    const matchesEstado = !this.estadoFilter || maq.estado === this.estadoFilter;  // estado en minúscula
    const matchesEtapa = !this.etapaFilter || maq.etapa === this.etapaFilter;      // etapa en minúscula
    
    return matchesSearch && matchesEstado && matchesEtapa;
  });
}

  onSearchChange(): void {
    this.aplicarFiltros();
  }

  limpiarFiltros(): void {
    this.searchTerm = '';
    this.estadoFilter = '';
    this.etapaFilter = '';
    this.aplicarFiltros();
  }

  seleccionarMaquina(maquina: Maquina): void {
    this.selectedMaquina = this.selectedMaquina?.ID_Maquina === maquina.ID_Maquina ? null : maquina;
    this.maquinaSeleccionada.emit(this.selectedMaquina || undefined);
  }

  editarMaquina(maquina: Maquina, event: Event): void {
    event.stopPropagation();
    this.abrirModalEditar.emit(maquina);
  }

  verHistorialMaquina(maquina: Maquina, event: Event): void {
    event.stopPropagation();
    this.verHistorial.emit(maquina);
  }

  getEstadoClass(estado: string): string {
    const classes: Record<string, string> = {
      'Operativa': 'estado-operativa',
      'No operativa': 'estado-no-operativa',
      'Retirada': 'estado-retirada',
      'Ensamblandose': 'estado-ensamblaje',
      'Comprobando': 'estado-comprobacion',
      'Distribucion': 'estado-distribucion',
      'Mantenimiento': 'estado-mantenimiento'
    };
    return classes[estado] || 'estado-default';
  }

  getEtapaClass(etapa: string): string {
    const classes: Record<string, string> = {
      'Montaje': 'etapa-montaje',
      'Distribucion': 'etapa-distribucion',
      'Recaudacion': 'etapa-recaudacion'
    };
    return classes[etapa] || 'etapa-default';
  }

  refresh(): void {
    this.cargarMaquinas();
  }
}