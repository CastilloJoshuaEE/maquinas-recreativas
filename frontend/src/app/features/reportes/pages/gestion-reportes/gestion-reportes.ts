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
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header';
import { ReportesService } from '../../services/reportes';
import { AuthService } from '@core/services/auth';
import { User } from '@core/models/user.model';
import { Reporte } from '@core/models/reporte.model';
import { UserService } from '@app/core/services/user';

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
  private userService = inject(UserService);
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
    if (this.currentUser?.id) {
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
    this.reportesService.getUsuariosChat(this.currentUser?.id || '').subscribe({
      next: (usuarios) => {
        this.administradores = usuarios.filter(u => u.tipo === 'Administrador');
        if (this.administradores.length > 0) this.nuevoReporte.destinatario = this.administradores[0].id;
      },
      error: () => { this.snackBar.open('Error al cargar administradores', 'Cerrar', { duration: 3000 });}
    });
  }
cargarUsuariosPorTipo(): void {
  if (!this.nuevoReporte.tipoDestinatario) { 
    this.usuariosDisponibles = []; 
    return; 
  }
  this.userService.getUsersByTipo(this.nuevoReporte.tipoDestinatario, this.currentUser!.id).subscribe({
    next: (usuarios) => { 
      this.usuariosDisponibles = usuarios.filter(u => u.id !== this.currentUser!.id);
      console.log('Usuarios cargados:', this.usuariosDisponibles);
    },
    error: (err) => { 
      console.error('Error al cargar usuarios:', err);
      this.snackBar.open('Error al cargar usuarios', 'Cerrar', { duration: 3000 }); 
    }
  });
}
  
cargarReportes(): void {
  this.cargandoReportes = true;
  this.reportesService.getReportesByUser(this.currentUser!.id).subscribe({
    next: (reportes) => { 
      console.log(' Reportes recibidos:', reportes);  // ← Agregar log
      this.reportes = reportes; 
      this.filtrarReportes(); 
      this.cargandoReportes = false; 
    },
    error: (err) => { 
      console.error(' Error cargando reportes:', err);
      this.cargandoReportes = false; 
      this.snackBar.open('Error al cargar reportes', 'Cerrar', { duration: 3000 }); 
    }
  });
}
  
  filtrarReportes(): void {
    this.reportesFiltrados = this.filtroEstado === 'todos' ? [...this.reportes] : this.reportes.filter(r => r.estado === this.filtroEstado);
  }
 onSubmit(): void {
    if (!this.nuevoReporte.destinatario || !this.nuevoReporte.descripcion) {
        this.snackBar.open('Complete todos los campos', 'Cerrar', { duration: 3000 });
        return;
    }
    if (this.nuevoReporte.destinatario === '') {
        this.snackBar.open('Seleccione un destinatario válido', 'Cerrar', { duration: 3000 });
        return;
    }
    
    if (this.nuevoReporte.destinatario === this.currentUser?.id) {
        this.snackBar.open('No puede enviar un reporte a sí mismo', 'Cerrar', { duration: 3000 });
        return;
    }
    
    if (!confirm('¿Está seguro de enviar este reporte?')) return;
    
    this.enviando = true;
    let descripcion = this.nuevoReporte.descripcion;
    if (this.esUsuarioInhabilitado) descripcion = `[SOLICITUD DE REACTIVACIÓN] ${descripcion}`;
    else if (this.modoAdmin) descripcion = `[USUARIO RESTRINGIDO] ${descripcion}`;
    
    const reporteData = {
        ID_Usuario_Emisor: this.currentUser!.id,
        ID_Usuario_Destinatario: this.nuevoReporte.destinatario,
        descripcion: descripcion
    };
    
    console.log('Enviando reporte:', reporteData);
    
    this.reportesService.crearReporte(reporteData).subscribe({
        next: (reporteId) => {
            console.log('ReporteId recibido:', reporteId);
            if (reporteId) {
                this.snackBar.open('Reporte enviado correctamente', 'Cerrar', { duration: 3000 });
                this.nuevoReporte = { tipoDestinatario: '', destinatario: '', descripcion: '' };
                if (!this.esUsuarioInhabilitado && !this.modoAdmin) {
                    this.cargarReportes();
                } else if (this.esUsuarioInhabilitado) {
                    setTimeout(() => this.router.navigate(['/']), 2000);
                }
            } else {
                this.snackBar.open('Error al enviar el reporte. El destinatario podría no existir.', 'Cerrar', { duration: 5000 });
            }
            this.enviando = false;
        },
        error: (err) => { 
            console.error('Error detallado:', err);
            this.snackBar.open('Error al enviar el reporte. Verifique que el destinatario exista.', 'Cerrar', { duration: 5000 }); 
            this.enviando = false; 
        }
    });
}
cambiarEstado(reporte: Reporte): void {
  //  Usar 'id' o 'ID_Reporte'
  const reporteId = (reporte as any).id || reporte.ID_Reporte;
  this.reportesService.updateReporteStatus(reporteId, reporte.estado).subscribe({
    next: (success: boolean) => {  // Tipar el parámetro success como boolean
      if (success) {
        this.snackBar.open('Estado actualizado correctamente', 'Cerrar', { duration: 3000 });
      } else { 
        this.snackBar.open('Error al actualizar estado', 'Cerrar', { duration: 3000 }); 
        this.cargarReportes(); 
      }
    },
    error: () => { 
      this.snackBar.open('Error al actualizar estado', 'Cerrar', { duration: 3000 }); 
      this.cargarReportes(); 
    }
  });
}
puedeCambiarEstado(reporte: Reporte): boolean {
  //  Usar 'id_emisor' o 'ID_Usuario_Emisor'
  const idEmisor = (reporte as any).id_emisor || reporte.ID_Usuario_Emisor;
  const idDestinatario = (reporte as any).id_destinatario || reporte.ID_Usuario_Destinatario;
  
  const esEmisor = idEmisor === this.currentUser?.id;
  const esDestinatario = idDestinatario === this.currentUser?.id;
  return esEmisor || esDestinatario;
}
  
verChat(reporte: Reporte): void { 
  //  Usar 'id' o 'ID_Reporte'
  const reporteId = (reporte as any).id || reporte.ID_Reporte;
  if (reporteId) {
    this.router.navigate(['/reportes/chat', reporteId]); 
  } else {
    console.warn('No se pudo obtener ID del reporte');
  }
}
  regresar(): void { this.router.navigate(['/']); }
}