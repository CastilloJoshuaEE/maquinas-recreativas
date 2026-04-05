/**
 * @fileoverview Vista de Chat
 * @description Página para visualizar y participar en conversaciones de chat
 * @component ChatViewComponent
 */

import { Component, OnInit, OnDestroy, ElementRef, ViewChild, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
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
import { Reporte, Comentario } from '@core/models/reporte.model';

@Component({
  selector: 'app-chat-view',
  standalone: true,
  imports: [CommonModule, FormsModule, MatCardModule, MatButtonModule, MatIconModule, MatFormFieldModule, MatInputModule, MatSelectModule, MatProgressSpinnerModule, AdminHeaderComponent],
  templateUrl: './chat-view.html',
  styleUrls: ['./chat-view.css']
})
export class ChatViewComponent implements OnInit, OnDestroy {
  private route = inject(ActivatedRoute);
  private router = inject(Router);
  private reportesService = inject(ReportesService);
  private authService = inject(AuthService);
  private snackBar = inject(MatSnackBar);
  
  @ViewChild('scrollAnchor') scrollAnchor!: ElementRef;
  
  currentUserId = '';
  currentUser: User | null = null;
  usuarios: User[] = [];
  usuariosFiltrados: User[] = [];
  searchTerm = '';
  usuarioSeleccionado: User | null = null;
  cargandoUsuarios = false;
  reportes: Reporte[] = [];
  reporteSeleccionadoId: string | null = null;
  comentarios: Comentario[] = [];
  nuevoMensaje = '';
  cargandoMensajes = false;
  enviando = false;
  mensajesNoLeidos: { [key: string]: number } = {};
  private refreshInterval: any;
  
  ngOnInit(): void {
    this.currentUser = this.authService.getCurrentUser();
    this.currentUserId = this.currentUser?.ID_Usuario || '';
    if (this.currentUserId) {
      this.cargarUsuarios();
      this.inicializarDesdeParams();
    }
    this.refreshInterval = setInterval(() => {
      if (this.reporteSeleccionadoId && this.reporteSeleccionadoId !== 'nuevo') this.cargarComentarios();
    }, 5000);
  }
  
  ngOnDestroy(): void { if (this.refreshInterval) clearInterval(this.refreshInterval); }
  
  private inicializarDesdeParams(): void {
    const reporteId = this.route.snapshot.params['reporteId'];
    const emisorId = this.route.snapshot.params['emisorId'];
    const destinatarioId = this.route.snapshot.params['destinatarioId'];
    if (reporteId) this.cargarReportePorId(reporteId);
    else if (emisorId && destinatarioId) this.cargarUsuarioPorId(emisorId === this.currentUserId ? destinatarioId : emisorId);
  }
  
  private cargarReportePorId(reporteId: string): void {
    this.reportesService.getReportesByUser(this.currentUserId).subscribe({
      next: (reportes) => {
        const reporte = reportes.find(r => r.ID_Reporte === reporteId);
        if (reporte) {
          const otroId = reporte.ID_Usuario_Emisor === this.currentUserId ? reporte.ID_Usuario_Destinatario : reporte.ID_Usuario_Emisor;
          this.cargarUsuarioPorId(otroId);
          this.reporteSeleccionadoId = reporteId;
        }
      }
    });
  }
  
  private cargarUsuarioPorId(usuarioId: string): void {
    this.reportesService.getUsuariosChat(this.currentUserId).subscribe({
      next: (usuarios) => {
        const usuario = usuarios.find(u => u.ID_Usuario === usuarioId);
        if (usuario) { this.usuarioSeleccionado = usuario; this.cargarReportes(); }
      }
    });
  }
  
  cargarUsuarios(): void {
    this.cargandoUsuarios = true;
    this.reportesService.getUsuariosChat(this.currentUserId).subscribe({
      next: (usuarios) => {
        this.usuarios = usuarios.filter(u => u.ID_Usuario !== this.currentUserId);
        this.usuariosFiltrados = [...this.usuarios];
        this.cargandoUsuarios = false;
      },
      error: () => { this.cargandoUsuarios = false; this.snackBar.error('Error al cargar usuarios', 'Cerrar'); }
    });
  }
  
  filtrarUsuarios(): void {
    if (!this.searchTerm.trim()) this.usuariosFiltrados = [...this.usuarios];
    else {
      const term = this.searchTerm.toLowerCase();
      this.usuariosFiltrados = this.usuarios.filter(u => u.nombre.toLowerCase().includes(term) || u.apellido.toLowerCase().includes(term) || u.email.toLowerCase().includes(term));
    }
  }
  
  seleccionarUsuario(usuario: User): void {
    this.usuarioSeleccionado = usuario;
    this.reporteSeleccionadoId = null;
    this.comentarios = [];
    this.cargarReportes();
  }
  
  cargarReportes(): void {
    if (!this.usuarioSeleccionado) return;
    this.reportesService.getChat(this.currentUserId, this.usuarioSeleccionado.ID_Usuario).subscribe({
      next: (data) => {
        this.reportes = data.reportes;
        if (this.reportes.length > 0) { this.reporteSeleccionadoId = this.reportes[0].ID_Reporte; this.cargarComentarios(); }
        else this.reporteSeleccionadoId = null;
      },
      error: () => { this.snackBar.error('Error al cargar reportes', 'Cerrar'); }
    });
  }
  
  cargarComentarios(): void {
    if (!this.reporteSeleccionadoId || this.reporteSeleccionadoId === 'nuevo') return;
    this.cargandoMensajes = true;
    this.reportesService.getComentarios(this.reporteSeleccionadoId).subscribe({
      next: (comentarios) => {
        this.comentarios = comentarios;
        this.cargandoMensajes = false;
        setTimeout(() => this.scrollToBottom(), 100);
        if (this.usuarioSeleccionado) this.mensajesNoLeidos[this.usuarioSeleccionado.ID_Usuario] = 0;
      },
      error: () => { this.cargandoMensajes = false; }
    });
  }
  
  crearNuevoReporte(): void {
    if (!this.usuarioSeleccionado) return;
    this.reportesService.crearReporte({
      ID_Usuario_Emisor: this.currentUserId,
      ID_Usuario_Destinatario: this.usuarioSeleccionado.ID_Usuario,
      descripcion: `Chat con ${this.usuarioSeleccionado.nombre} ${this.usuarioSeleccionado.apellido}`
    }).subscribe({
      next: (reporteId) => {
        if (reporteId) { this.reporteSeleccionadoId = reporteId; this.cargarReportes(); this.snackBar.success('Conversación iniciada', 'Éxito'); }
        else this.snackBar.error('Error al crear conversación', 'Error');
      },
      error: () => { this.snackBar.error('Error al crear conversación', 'Error'); }
    });
  }
  
  enviarMensaje(): void {
    if (!this.nuevoMensaje.trim() || !this.reporteSeleccionadoId || this.reporteSeleccionadoId === 'nuevo') return;
    this.enviando = true;
    this.reportesService.crearComentario(this.reporteSeleccionadoId, this.currentUserId, this.nuevoMensaje).subscribe({
      next: (success) => {
        if (success) { this.cargarComentarios(); this.nuevoMensaje = ''; }
        else this.snackBar.error('Error al enviar mensaje', 'Error');
        this.enviando = false;
      },
      error: () => { this.snackBar.error('Error al enviar mensaje', 'Error'); this.enviando = false; }
    });
  }
  
  obtenerNoLeidas(usuarioId: string): number { return this.mensajesNoLeidos[usuarioId] || 0; }
  private scrollToBottom(): void { setTimeout(() => { if (this.scrollAnchor) this.scrollAnchor.nativeElement.scrollIntoView({ behavior: 'smooth' }); }, 100); }
  regresar(): void { this.router.navigate(['/reportes/gestion']); }
}