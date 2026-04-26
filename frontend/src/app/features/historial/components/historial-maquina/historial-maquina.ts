/**
 * @fileoverview Componente de historial de máquina recreativa
 * @description Dialog que muestra el historial completo de cambios de una máquina,
 *              con filtros, paginación y detalle de cada evento.
 */

import {
  Component, OnInit, OnDestroy, inject,
  ChangeDetectionStrategy, ChangeDetectorRef, signal, computed
} from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Subject, takeUntil } from 'rxjs';
import { debounceTime, distinctUntilChanged } from 'rxjs/operators';
import { MinPipe } from '@shared/pipes/min.pipe';

import { MatDialogRef, MAT_DIALOG_DATA, MatDialogModule } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatTooltipModule } from '@angular/material/tooltip';
import { MatChipsModule } from '@angular/material/chips';
import { MatDividerModule } from '@angular/material/divider';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatNativeDateModule } from '@angular/material/core';

import { HistorialService } from '@features/historial/services/historial';
import { HistorialMaquina, PaginacionHistorial } from '@core/models/historial.model';
import { Maquina } from '@core/models/maquina.model';

export interface HistorialDialogData {
  maquina: Maquina;
}

interface EventoAgrupado {
  fecha: string;  // 'DD/MM/YYYY'
  eventos: HistorialMaquina[];
}

@Component({
  selector: 'app-historial-maquina',
  standalone: true,
  changeDetection: ChangeDetectionStrategy.OnPush,
  imports: [
    CommonModule,
    FormsModule,
    MatDialogModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatTooltipModule,
    MatChipsModule,
    MatDividerModule,
    MatDatepickerModule,
    MatNativeDateModule,
    MinPipe
  ],
  templateUrl: './historial-maquina.html',
  styleUrls: ['./historial-maquina.css']
})
export class HistorialMaquinaComponent implements OnInit, OnDestroy {
  private historialService = inject(HistorialService);
  private cdr = inject(ChangeDetectorRef);
  private destroy$ = new Subject<void>();

  dialogRef = inject(MatDialogRef<HistorialMaquinaComponent>);
  data = inject<HistorialDialogData>(MAT_DIALOG_DATA);

  // --- Estado reactivo ---
  historial = signal<HistorialMaquina[]>([]);
  paginacion = signal<PaginacionHistorial>({
    pagina_actual: 1,
    por_pagina: 50,
    total: 0,
    total_paginas: 0
  });
  cargando = signal(false);
  eventoDetalle = signal<HistorialMaquina | null>(null);

  // Filtros
  filtroAccion = '';
  filtroTipoUsuario = '';
  filtroFechaInicio = '';
  filtroFechaFin = '';
  pagina = 1;
  porPagina = 50;

  // Acciones únicas detectadas
  accionesUnicas: string[] = [];

  // Agrupado por fecha para timeline
  eventosAgrupados = computed<EventoAgrupado[]>(() => {
    const grupos = new Map<string, HistorialMaquina[]>();
    for (const evento of this.historial()) {
      const fecha = new Date(evento.fecha_hora).toLocaleDateString('es-EC', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
      });
      if (!grupos.has(fecha)) grupos.set(fecha, []);
      grupos.get(fecha)!.push(evento);
    }
    return Array.from(grupos.entries()).map(([fecha, eventos]) => ({ fecha, eventos }));
  });

  readonly tiposUsuario = [
    { value: '', label: 'Todos los roles' },
    { value: 'Logistica',     label: 'Logística' },
    { value: 'Tecnico',       label: 'Técnico' },
    { value: 'Administrador', label: 'Administrador' },
    { value: 'Recaudador',    label: 'Recaudador' },
  ];

  ngOnInit(): void {
    this.cargarHistorial();
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  cargarHistorial(): void {
    this.cargando.set(true);

    this.historialService
      .getHistorialPorMaquina(
        this.data.maquina.ID_Maquina,
        this.pagina,
        this.porPagina
      )
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: (res) => {
          this.historial.set(res.historial);
          this.paginacion.set(res.paginacion);
          this.extraerAcciones(res.historial);
          this.cargando.set(false);
          this.cdr.markForCheck();
        },
        error: () => {
          this.cargando.set(false);
          this.cdr.markForCheck();
        }
      });
  }

  aplicarFiltros(): void {
    this.pagina = 1;
    this.cargando.set(true);

    this.historialService
      .getHistorialGeneral({
        idMaquina: this.data.maquina.ID_Maquina,
        tipoUsuario: this.filtroTipoUsuario || undefined,
        accion: this.filtroAccion || undefined,
        fechaInicio: this.filtroFechaInicio || undefined,
        fechaFin: this.filtroFechaFin || undefined,
        pagina: this.pagina,
        por_pagina: this.porPagina
      })
      .pipe(takeUntil(this.destroy$))
      .subscribe({
        next: (res) => {
          this.historial.set(res.historial);
          this.paginacion.set(res.paginacion);
          this.cargando.set(false);
          this.cdr.markForCheck();
        },
        error: () => {
          this.cargando.set(false);
          this.cdr.markForCheck();
        }
      });
  }

  limpiarFiltros(): void {
    this.filtroAccion = '';
    this.filtroTipoUsuario = '';
    this.filtroFechaInicio = '';
    this.filtroFechaFin = '';
    this.pagina = 1;
    this.cargarHistorial();
  }

  tieneFiltrosActivos(): boolean {
    return !!(this.filtroAccion || this.filtroTipoUsuario || this.filtroFechaInicio || this.filtroFechaFin);
  }

  irPagina(nueva: number): void {
    const p = this.paginacion();
    if (nueva < 1 || nueva > p.total_paginas) return;
    this.pagina = nueva;
    if (this.tieneFiltrosActivos()) {
      this.aplicarFiltros();
    } else {
      this.cargarHistorial();
    }
  }

  verDetalle(evento: HistorialMaquina): void {
    this.eventoDetalle.set(
      this.eventoDetalle()?.ID_Historial === evento.ID_Historial ? null : evento
    );
  }

  cerrar(): void {
    this.dialogRef.close();
  }

  // --- Helpers de UI ---

  getAccionIcon(accion: string): string {
    const lower = accion.toLowerCase();
    if (lower.includes('cre') || lower.includes('registr'))  return 'add_circle';
    if (lower.includes('estado'))                             return 'swap_horiz';
    if (lower.includes('etapa'))                             return 'move_up';
    if (lower.includes('mont') || lower.includes('ensam'))   return 'build';
    if (lower.includes('compr') || lower.includes('verif'))  return 'verified';
    if (lower.includes('distribu'))                          return 'local_shipping';
    if (lower.includes('manten'))                            return 'handyman';
    if (lower.includes('recau'))                             return 'payments';
    if (lower.includes('retir'))                             return 'remove_circle';
    if (lower.includes('actualiz') || lower.includes('edit')) return 'edit';
    return 'history';
  }

  getAccionColor(accion: string): string {
    const lower = accion.toLowerCase();
    if (lower.includes('cre') || lower.includes('registr'))  return 'color-create';
    if (lower.includes('distribu'))                          return 'color-distrib';
    if (lower.includes('compr') || lower.includes('verif'))  return 'color-check';
    if (lower.includes('manten'))                            return 'color-maint';
    if (lower.includes('retir'))                             return 'color-retire';
    if (lower.includes('recau'))                             return 'color-recaud';
    return 'color-default';
  }

  getTipoUsuarioBadge(tipo: string): string {
    const map: Record<string, string> = {
      'Logistica': 'badge-logistica',
      'Tecnico': 'badge-tecnico',
      'Administrador': 'badge-admin',
      'Recaudador': 'badge-recaudador',
    };
    return map[tipo] ?? 'badge-default';
  }

  formatFechaHora(fechaHora: string): { fecha: string; hora: string } {
    const d = new Date(fechaHora);
    return {
      fecha: d.toLocaleDateString('es-EC', { day: '2-digit', month: 'short', year: 'numeric' }),
      hora: d.toLocaleTimeString('es-EC', { hour: '2-digit', minute: '2-digit' })
    };
  }

  private extraerAcciones(historial: HistorialMaquina[]): void {
    const set = new Set<string>();
    historial.forEach(h => set.add(h.accion));
    this.accionesUnicas = Array.from(set).sort();
  }

  get paginaArray(): number[] {
    const p = this.paginacion();
    const total = p.total_paginas;
    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
    const current = p.pagina_actual;
    const pages = new Set([1, total, current, current - 1, current + 1]);
    return Array.from(pages).filter(n => n >= 1 && n <= total).sort((a, b) => a - b);
  }
}