import { Component, OnInit, OnDestroy, inject, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Subject, takeUntil } from 'rxjs';
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
export class MaquinasDashboardComponent implements OnInit, OnDestroy {
  private maquinasService = inject(MaquinasSharedService);
  private authService     = inject(AuthService);
  private snackBar        = inject(MatSnackBar);
  private destroy$        = new Subject<void>();

  @Input() showActions  = true;
  @Input() filterEstado?: string;
  @Input() filterEtapa?:  string;

  @Output() maquinaSeleccionada = new EventEmitter<Maquina>();
  @Output() abrirModalCrear     = new EventEmitter<void>();
  @Output() abrirModalEditar    = new EventEmitter<Maquina>();
  @Output() verHistorial        = new EventEmitter<Maquina>();

  maquinas:         Maquina[] = [];
  maquinasFiltradas: Maquina[] = [];
  cargando = false;

  // Filtros locales
  searchTerm   = '';
  estadoFilter = '';
  etapaFilter  = '';

  selectedMaquina: Maquina | null = null;

  // Permisos por rol
  esLogistica    = false;
  puedeEditar    = false;
  puedeCrear     = false;
  puedeEliminar  = false;

  ngOnInit(): void {
    this.verificarPermisos();
    this.cargarMaquinas();
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  // ── Permisos ──────────────────────────────────────────────────────────────
  private verificarPermisos(): void {
    const rol = this.authService.getCurrentUser()?.tipo || '';
    this.esLogistica   = rol === 'Logistica';
    this.puedeCrear    = rol === 'Logistica';                          // solo logística crea
    this.puedeEditar   = rol === 'Logistica';                          // solo logística edita datos
    this.puedeEliminar = rol === 'Administrador';
  }

  // ── Carga de datos ─────────────────────────────────────────────────────────
  cargarMaquinas(): void {
    this.cargando = true;

    this.maquinasService
      .getTodasMaquinas()
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: (res) => {
          if (res.success) {
            this.maquinas = res.maquinas ?? [];
            this.aplicarFiltros();
          } else {
            this.snackBar.open('Error al cargar las máquinas', 'Cerrar', { duration: 3000 });
          }
          this.cargando = false;
        },
        error: (err) => {
          console.error('Error cargando máquinas:', err);
          this.snackBar.open(
            err?.message || 'Error al cargar las máquinas',
            'Cerrar',
            { duration: 3000 }
          );
          this.cargando = false;
        }
      });
  }

  // ── Filtros ────────────────────────────────────────────────────────────────
  aplicarFiltros(): void {
    this.maquinasFiltradas = this.maquinas.filter(maq => {
      const term = this.searchTerm.toLowerCase();

      const matchesSearch = !term ||
        maq.Nombre_Maquina?.toLowerCase().includes(term) ||
        maq.tipo?.toLowerCase().includes(term) ||
        maq.NombreComercio?.toLowerCase().includes(term);

      const matchesEstado = !this.estadoFilter ||
        maq.estado?.toLowerCase() === this.estadoFilter.toLowerCase();

      const matchesEtapa = !this.etapaFilter ||
        maq.etapa?.toLowerCase() === this.etapaFilter.toLowerCase();

      return matchesSearch && matchesEstado && matchesEtapa;
    });
  }

  onSearchChange(): void { this.aplicarFiltros(); }

  limpiarFiltros(): void {
    this.searchTerm   = '';
    this.estadoFilter = '';
    this.etapaFilter  = '';
    this.aplicarFiltros();
  }

  // ── Acciones de fila ───────────────────────────────────────────────────────
  seleccionarMaquina(maquina: Maquina): void {
    this.selectedMaquina =
      this.selectedMaquina?.ID_Maquina === maquina.ID_Maquina ? null : maquina;
    this.maquinaSeleccionada.emit(this.selectedMaquina ?? undefined);
  }

  editarMaquina(maquina: Maquina, event: Event): void {
    event.stopPropagation();
    this.abrirModalEditar.emit(maquina);
  }

  verHistorialMaquina(maquina: Maquina, event: Event): void {
    event.stopPropagation();
    this.verHistorial.emit(maquina);
  }

  refresh(): void { this.cargarMaquinas(); }

  // ── Helpers de UI ──────────────────────────────────────────────────────────
  getEstadoClass(estado: string): string {
    const map: Record<string, string> = {
      'Operativa':      'estado-operativa',
      'No operativa':   'estado-no-operativa',
      'Retirada':       'estado-retirada',
      'Ensamblandose':  'estado-ensamblaje',
      'Comprobandose':  'estado-comprobacion',
      'Distribuyendose':'estado-distribucion',
      'Mantenimiento':  'estado-mantenimiento'
    };
    return map[estado] ?? 'estado-default';
  }

  getEtapaClass(etapa: string): string {
    const map: Record<string, string> = {
      'Montaje':     'etapa-montaje',
      'Distribucion':'etapa-distribucion',
      'Recaudacion': 'etapa-recaudacion'
    };
    return map[etapa] ?? 'etapa-default';
  }
}