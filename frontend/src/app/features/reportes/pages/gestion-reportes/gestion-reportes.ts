/**
 * @fileoverview Gestión de Reportes
 * @description Página para crear y gestionar reportes del sistema
 * @component GestionReportesComponent
 */

import { Component, OnInit, OnDestroy, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header.component';
import { ReportesService } from '../../services/reportes';
import { AuthService } from '@core/services/auth.service';
import { User } from '@core/models/user.model';
import { Reporte } from '@core/models/reporte.model';

@Component({
  selector: 'app-gestion-reportes',
  standalone: true,
  imports: [CommonModule, FormsModule, MatCardModule, MatButtonModule, MatIconModule, MatFormFieldModule, MatInputModule, MatSelectModule, MatProgressSpinnerModule, AdminHeaderComponent],
  templateUrl: './gestion-reportes.html',
  styleUrls: ['./gestion-reportes.css']
})
export class GestionReportesComponent implements OnInit, OnDestroy {
  private router = inject(Router);
  private reportesService = inject(ReportesService);
  private authService = inject(AuthService);
  private snackBar = inject(MatSnackBar);
  
  currentUser: User | null = null;
  modoAdmin = false;
  esUsuarioInhabilitado = false;
  
  nuevoReporte = { tipoDestinatario: '', destinatario: '', descripcion: '' };
  tiposUsuario = ['Tecnico', 'Contabilidad', 'Logistica', 'Administrador'];
  usuariosDisponibles: User[] = [];
  administradores: User[] = [];
  
  reportes: Reporte[] = [];
  reportesFiltrados: Reporte[] = [];
  filtroEstado = 'todos';
  
  enviando = false;
  cargandoReportes = false;
  statusMessage = '';
  isError = false;
  
  ngOnInit(): void {
    this.currentUser = this.authService.getCurrentUser();
    this.cargarEstadoInicial();
    if (this.currentUser?.ID_Usuario) {
      if (!this.esUsuarioInhabilitado && !this.modoAdmin) this.cargarReportes();
      else if (this.esUsuarioInhabilitado) this.cargarAdministradores();
    }
  }
  
  ngOnDestroy(): void {}
  
  private cargarEstadoInicial(): void {
    const navigation = this.router.getCurrentNavigation();
    const state = navigation?.extras.state as any;
    if (state) {
      if (state.isDisabledUser) { this.esUsuarioInhabilitado = true; this.statusMessage = 'Su cuenta está inhabilitada. Por favor contacte al administrador.'; }
      if (state.message) this.statusMessage = state.message;
      if (state.userData) this.currentUser = state.userData;
    }
    if (new URLSearchParams(window.location.search).get('modo') === 'admin') this.modoAdmin = true;
  }
  
  private cargarAdministradores(): void {
    this.reportesService.getUsuariosChat(this.currentUser?.ID_Usuario || '').subscribe({
      next: (usuarios) => {
        this.administradores = usuarios.filter(u => u.tipo === 'Administrador');
        if (this.administradores.length > 0) this.nuevoReporte.destinatario = this.administradores[0].ID_Usuario;
      },
      error: () => { this.snackBar.error('Error al cargar administradores', 'Cerrar'); }
    });
  }
  
  cargarUsuariosPorTipo(): void {
    if (!this.nuevoReporte.tipoDestinatario) { this.usuariosDisponibles = []; return; }
    this.reportesService.getUsuariosChat(this.currentUser!.ID_Usuario).subscribe({
      next: (usuarios) => { this.usuariosDisponibles = usuarios.filter(u => u.tipo === this.nuevoReporte.tipoDestinatario); },
      error: () => { this.snackBar.error('Error al cargar usuarios', 'Cerrar'); }
    });
  }
  
  cargarReportes(): void {
    this.cargandoReportes = true;
    this.reportesService.getReportesByUser(this.currentUser!.ID_Usuario).subscribe({
      next: (reportes) => { this.reportes = reportes; this.filtrarReportes(); this.cargandoReportes = false; },
      error: () => { this.cargandoReportes = false; this.snackBar.error('Error al cargar reportes', 'Cerrar'); }
    });
  }
  
  filtrarReportes(): void {
    this.reportesFiltrados = this.filtroEstado === 'todos' ? [...this.reportes] : this.reportes.filter(r => r.estado === this.filtroEstado);
  }
  
  onSubmit(): void {
    if (!this.nuevoReporte.destinatario || !this.nuevoReporte.descripcion) { this.snackBar.warning('Complete todos los campos', 'Cerrar'); return; }
    if (!confirm('¿Está seguro de enviar este reporte?')) return;
    this.enviando = true;
    let descripcion = this.nuevoReporte.descripcion;
    if (this.esUsuarioInhabilitado) descripcion = `[SOLICITUD DE REACTIVACIÓN] ${descripcion}`;
    else if (this.modoAdmin) descripcion = `[USUARIO RESTRINGIDO] ${descripcion}`;
    
    this.reportesService.crearReporte({
      ID_Usuario_Emisor: this.currentUser!.ID_Usuario,
      ID_Usuario_Destinatario: this.nuevoReporte.destinatario,
      descripcion
    }).subscribe({
      next: (reporteId) => {
        if (reporteId) {
          this.snackBar.success('Reporte enviado correctamente', 'Éxito');
          this.nuevoReporte = { tipoDestinatario: '', destinatario: '', descripcion: '' };
          if (!this.esUsuarioInhabilitado && !this.modoAdmin) this.cargarReportes();
          else if (this.esUsuarioInhabilitado) setTimeout(() => this.router.navigate(['/']), 2000);
        } else this.snackBar.error('Error al enviar el reporte', 'Error');
        this.enviando = false;
      },
      error: () => { this.snackBar.error('Error al enviar el reporte', 'Error'); this.enviando = false; }
    });
  }
  
  cambiarEstado(reporte: Reporte): void {
    this.reportesService.actualizarEstado(reporte.ID_Reporte, reporte.estado).subscribe({
      next: (success) => {
        if (success) this.snackBar.success('Estado actualizado correctamente', 'Éxito');
        else { this.snackBar.error('Error al actualizar estado', 'Error'); this.cargarReportes(); }
      },
      error: () => { this.snackBar.error('Error al actualizar estado', 'Error'); this.cargarReportes(); }
    });
  }
  
  puedeCambiarEstado(reporte: Reporte): boolean {
    const esEmisor = reporte.ID_Usuario_Emisor === this.currentUser?.ID_Usuario;
    const esDestinatario = reporte.ID_Usuario_Destinatario === this.currentUser?.ID_Usuario;
    return esEmisor || esDestinatario;
  }
  
  verChat(reporte: Reporte): void { this.router.navigate(['/reportes/chat', reporte.ID_Reporte]); }
  regresar(): void { this.router.navigate(['/']); }
}