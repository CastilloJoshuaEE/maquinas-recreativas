/**
 * @fileoverview Dashboard de Logística
 * @description Panel principal del módulo de logística con gestión de máquinas y comercios
 * @component DashboardLogisticaComponent
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header';
import { LogisticaService } from '../../services/logistica';
import { AuthService } from '@core/services/auth';
import { NotificationService } from '@core/services/notification';
import { Maquina } from '@core/models/maquina.model';
import { Comercio } from '@core/models/recaudacion.model';
import { User } from '@core/models/user.model';
import { ComercioFormComponent } from '../../ui/comercio-form/comercio-form';
import { MaquinaFormComponent } from '../../ui/maquina-form/maquina-form';

@Component({
  selector: 'app-dashboard-logistica',
  standalone: true,
  imports: [
    CommonModule, FormsModule, ReactiveFormsModule,
    MatCardModule, MatButtonModule, MatIconModule,
    MatProgressSpinnerModule, MatFormFieldModule, MatInputModule, MatSelectModule,
    AdminHeaderComponent, ComercioFormComponent, MaquinaFormComponent
  ],
  templateUrl: './dashboard-logistica.html',
  styleUrls: ['./dashboard-logistica.css']
})
export class DashboardLogisticaComponent implements OnInit {
  private router = inject(Router);
  private logisticaService = inject(LogisticaService);
  private authService = inject(AuthService);
  private notificationService = inject(NotificationService);
  private snackBar = inject(MatSnackBar);
  private fb = inject(FormBuilder);

  user: User | null = null;

  // ── Notificaciones ────────────────────────────────────────────────────────
  notificaciones: any[] = [];
  notificacionesNoLeidas = 0;
  cargandoNotificaciones = false;
  mostrarNotificaciones = false;

  // ── Máquinas ──────────────────────────────────────────────────────────────
  maquinasDistribucion: Maquina[] = [];
  maquinasOperativas: Maquina[] = [];
  maquinasRetiradas: Maquina[] = [];
  selectedMaquina: Maquina | null = null;
  mostrarDistribucion = false;
  mostrarOperativas = false;
  mostrarRetiradas = false;
  cargandoDistribucion = false;
  cargandoOperativas = false;
  cargandoRetiradas = false;

  // ── Modal mantenimiento ───────────────────────────────────────────────────
  mostrarMensajeMantenimiento = false;
  mensajeMantenimiento = '';
  enviandoMantenimiento = false;
  errorMantenimiento = '';

  // ── Formularios hijos ─────────────────────────────────────────────────────
  mostrarComercioForm = false;
  mostrarMaquinaForm = false;

  // ── Gestión de Comercios ──────────────────────────────────────────────────
  comercios: Comercio[] = [];
  cargandoComercios = false;
  mostrarComercios = false;

  // Modal editar comercio
  mostrarEditarComercio = false;
  comercioEditando: Comercio | null = null;
  guardandoComercio = false;
  errorComercio = '';
  editComercioForm!: FormGroup;

  // Modal confirmar eliminar
  mostrarConfirmarEliminar = false;
  comercioAEliminar: Comercio | null = null;
  eliminandoComercio = false;

  ngOnInit(): void {
    this.user = this.authService.getCurrentUser();
    this.editComercioForm = this.fb.group({
      nombre:    ['', Validators.required],
      tipo:      ['', Validators.required],
      direccion: ['', Validators.required],
      telefono:  ['']
    });
    if (this.user?.id) {
      this.cargarNotificaciones();
      this.cargarMaquinas();
      this.cargarComercios();
    }
  }

  // ── Notificaciones ────────────────────────────────────────────────────────
  private cargarNotificaciones(): void {
    this.cargandoNotificaciones = true;
    this.notificationService.getMaquinaNotifications(this.user!.id).subscribe({
      next: (n) => {
        this.notificaciones = n;
        this.notificacionesNoLeidas = n.filter(x => !x.leida).length;
        this.cargandoNotificaciones = false;
      },
      error: () => { this.cargandoNotificaciones = false; }
    });
  }

  marcarNotificacionLeida(id: string): void {
    this.notificationService.markAsRead(id).subscribe({ next: () => this.cargarNotificaciones() });
  }

  // ── Máquinas ──────────────────────────────────────────────────────────────
  private cargarMaquinas(): void {
    this.cargarMaquinasDistribucion();
    this.cargarMaquinasOperativas();
    this.cargarMaquinasRetiradas();
  }

  private cargarMaquinasDistribucion(): void {
    this.cargandoDistribucion = true;
    this.logisticaService.getMaquinasDistribucion().subscribe({
      next: (m) => { this.maquinasDistribucion = m; this.cargandoDistribucion = false; },
      error: () => { this.cargandoDistribucion = false; }
    });
  }

  private cargarMaquinasOperativas(): void {
    this.cargandoOperativas = true;
    this.logisticaService.getMaquinasOperativas().subscribe({
      next: (m) => { this.maquinasOperativas = m; this.cargandoOperativas = false; },
      error: () => { this.cargandoOperativas = false; }
    });
  }

  private cargarMaquinasRetiradas(): void {
    this.cargandoRetiradas = true;
    this.logisticaService.getMaquinasRetiradas().subscribe({
      next: (m) => { this.maquinasRetiradas = m; this.cargandoRetiradas = false; },
      error: () => { this.cargandoRetiradas = false; }
    });
  }

  seleccionarMaquina(maquina: Maquina): void {
    this.selectedMaquina = this.selectedMaquina?.ID_Maquina === maquina.ID_Maquina ? null : maquina;
  }

  esMaquinaDistribucion(): boolean {
    return !!this.selectedMaquina && this.maquinasDistribucion.some(m => m.ID_Maquina === this.selectedMaquina!.ID_Maquina);
  }

  esMaquinaOperativa(): boolean {
    return !!this.selectedMaquina && this.maquinasOperativas.some(m => m.ID_Maquina === this.selectedMaquina!.ID_Maquina);
  }

  ponerOperativa(): void {
    if (!this.selectedMaquina) return;
    if (!confirm(`¿Está seguro de poner operativa la máquina ${this.selectedMaquina.Nombre_Maquina}?`)) return;
    this.logisticaService.ponerMaquinaOperativa(this.selectedMaquina.ID_Maquina).subscribe({
      next: (ok) => {
        if (ok) { this.snackBar.open('Máquina puesta operativa', 'Cerrar', { duration: 3000 }); this.cargarMaquinas(); this.selectedMaquina = null; }
        else { this.snackBar.open('Error al poner operativa', 'Cerrar', { duration: 3000 }); }
      },
      error: () => { this.snackBar.open('Error al poner operativa', 'Cerrar', { duration: 3000 }); }
    });
  }

  abrirModalMantenimiento(): void {
    if (!this.selectedMaquina) return;
    this.mensajeMantenimiento = '';
    this.errorMantenimiento = '';
    this.mostrarMensajeMantenimiento = true;
  }

  cerrarModalMantenimiento(): void {
    this.mostrarMensajeMantenimiento = false;
    this.mensajeMantenimiento = '';
    this.errorMantenimiento = '';
  }

  enviarMantenimiento(): void {
    if (!this.selectedMaquina || !this.mensajeMantenimiento.trim()) return;
    this.enviandoMantenimiento = true;
    this.errorMantenimiento = '';
    this.logisticaService.solicitarMantenimiento({
      idMaquina: this.selectedMaquina.ID_Maquina,
      mensaje: this.mensajeMantenimiento,
      idLogistica: this.user!.id
    }).subscribe({
      next: (ok) => {
        if (ok) {
          this.snackBar.open('Solicitud enviada', 'Cerrar', { duration: 3000 });
          this.cargarMaquinas(); this.selectedMaquina = null; this.cerrarModalMantenimiento();
        } else {
          this.errorMantenimiento = 'No hay técnicos de mantenimiento disponibles';
        }
        this.enviandoMantenimiento = false;
      },
      error: () => { this.errorMantenimiento = 'No hay técnicos disponibles'; this.enviandoMantenimiento = false; }
    });
  }

  // ── Formularios hijos ─────────────────────────────────────────────────────
  abrirFormularioComercio(): void { this.mostrarComercioForm = true; }
  cerrarFormularioComercio(): void { this.mostrarComercioForm = false; }
  onComercioRegistrado(): void { this.cerrarFormularioComercio(); this.cargarComercios(); this.snackBar.open('Comercio registrado', 'Cerrar', { duration: 3000 }); }

  abrirFormularioMaquina(): void { this.mostrarMaquinaForm = true; }
  cerrarFormularioMaquina(): void { this.mostrarMaquinaForm = false; }
  onMaquinaRegistrada(): void { this.cerrarFormularioMaquina(); this.cargarMaquinas(); this.snackBar.open('Máquina registrada', 'Cerrar', { duration: 3000 }); }

  irAInformesDistribucion(): void { this.router.navigate(['/logistica/consultar-informe-distribucion']); }

  // ── Gestión de Comercios ──────────────────────────────────────────────────
  cargarComercios(): void {
    this.cargandoComercios = true;
    this.logisticaService.getComercios().subscribe({
      next: (data) => { this.comercios = data; this.cargandoComercios = false; },
      error: () => { this.cargandoComercios = false; }
    });
  }

  abrirEditarComercio(comercio: Comercio, event: Event): void {
    event.stopPropagation();
    this.comercioEditando = comercio;
    this.errorComercio = '';
    this.editComercioForm.patchValue({
      nombre:    (comercio as any).nombre    || (comercio as any).Nombre    || '',
      tipo:      (comercio as any).tipo      || (comercio as any).Tipo      || '',
      direccion: (comercio as any).direccion || (comercio as any).Direccion || '',
      telefono:  (comercio as any).telefono  || (comercio as any).Telefono  || ''
    });
    this.mostrarEditarComercio = true;
  }

  cerrarEditarComercio(): void {
    this.mostrarEditarComercio = false;
    this.comercioEditando = null;
    this.editComercioForm.reset();
  }

  guardarComercio(): void {
    if (this.editComercioForm.invalid || !this.comercioEditando) return;
    this.guardandoComercio = true;
    this.errorComercio = '';
    const id = this.comercioEditando.ID_Comercio;
    this.logisticaService.actualizarComercio(id, this.editComercioForm.value).subscribe({
      next: (ok) => {
        if (ok) {
          this.snackBar.open('Comercio actualizado correctamente', 'Cerrar', { duration: 3000 });
          this.cerrarEditarComercio();
          this.cargarComercios();
        } else {
          this.errorComercio = 'No se pudo actualizar el comercio';
        }
        this.guardandoComercio = false;
      },
      error: () => { this.errorComercio = 'Error al actualizar'; this.guardandoComercio = false; }
    });
  }

  confirmarEliminarComercio(comercio: Comercio, event: Event): void {
    event.stopPropagation();
    this.comercioAEliminar = comercio;
    this.mostrarConfirmarEliminar = true;
  }

  cancelarEliminar(): void {
    this.mostrarConfirmarEliminar = false;
    this.comercioAEliminar = null;
  }

  ejecutarEliminarComercio(): void {
    if (!this.comercioAEliminar) return;
    this.eliminandoComercio = true;
    this.logisticaService.eliminarComercio(this.comercioAEliminar.ID_Comercio).subscribe({
      next: (ok) => {
        if (ok) {
          this.snackBar.open('Comercio eliminado', 'Cerrar', { duration: 3000 });
          this.cargarComercios();
          this.cancelarEliminar();
        } else {
          this.snackBar.open('No se pudo eliminar el comercio', 'Cerrar', { duration: 3000 });
          this.cancelarEliminar();
        }
        this.eliminandoComercio = false;
      },
      error: () => { this.snackBar.open('Error al eliminar', 'Cerrar', { duration: 3000 }); this.eliminandoComercio = false; this.cancelarEliminar(); }
    });
  }

  getNombreComercio(c: any): string { return c.nombre || c.Nombre || '—'; }
  getTipoComercio(c: any): string   { return c.tipo    || c.Tipo    || '—'; }
  getDirComercio(c: any): string    { return c.direccion || c.Direccion || '—'; }
  getTelComercio(c: any): string    { return c.telefono  || c.Telefono  || '—'; }
}