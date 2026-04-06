/**
 * @fileoverview Componente de Chat
 * @description Componente reutilizable para chat entre usuarios
 * @component ChatComponent
 */

import { Component, Input, Output, EventEmitter, OnInit, OnDestroy, ElementRef, ViewChild, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { AuthService } from '@core/services/auth';
import { ReportService } from '@core/services/report';
import { User } from '@core/models/user.model';
import { Reporte, Comentario } from '@core/models/reporte.model';
import { Subject } from 'rxjs';

@Component({
  selector: 'app-chat',
  standalone: true,
  imports: [CommonModule, FormsModule, MatCardModule, MatButtonModule, MatIconModule, MatFormFieldModule, MatInputModule, MatProgressSpinnerModule],
  templateUrl: './chat.html',
  styleUrls: ['./chat.css']
})
export class ChatComponent implements OnInit, OnDestroy {
  @Input() currentUser: User | null = null;
  @Input() asPanel = false;
  @Input() modalScrollable = false;
  @Input() showUserList = true;
  @Input() showSearch = true;
  @Input() initialUserId: string | null = null;
  @Input() initialReporteId: string | null = null;
  @Output() onClose = new EventEmitter<void>();
  @Output() onMessageSent = new EventEmitter<Comentario>();
  
  private authService = inject(AuthService);
  private reportService = inject(ReportService);
  
  @ViewChild('scrollAnchor') scrollAnchor!: ElementRef;
  
  currentUserId = '';
  usuarios: User[] = [];
  usuariosFiltrados: User[] = [];
  searchTerm = '';
  selectedUser: User | null = null;
  selectedReporteId: string | null = null;
  reportes: Reporte[] = [];
  comentarios: Comentario[] = [];
  nuevoMensaje = '';
  cargandoUsuarios = false;
  cargandoMensajes = false;
  enviando = false;
  private destroy$ = new Subject<void>();
  private refreshInterval: any;
  
  ngOnInit(): void {
    this.currentUserId = this.currentUser?.ID_Usuario || this.authService.getCurrentUser()?.ID_Usuario || '';
    if (this.currentUserId) this.cargarUsuarios();
    this.refreshInterval = setInterval(() => { if (this.selectedUser && this.selectedReporteId) this.cargarComentarios(); }, 5000);
  }
  
  ngOnDestroy(): void {
    this.destroy$.next(); this.destroy$.complete();
    if (this.refreshInterval) clearInterval(this.refreshInterval);
  }
  
  cargarUsuarios(): void {
    this.cargandoUsuarios = true;
    this.reportService.getUsuariosChat(this.currentUserId).subscribe({
      next: (usuarios) => {
        this.usuarios = usuarios.filter(u => u.ID_Usuario !== this.currentUserId);
        this.usuariosFiltrados = [...this.usuarios];
        this.cargandoUsuarios = false;
        if (this.initialUserId && !this.selectedUser) {
          const usuarioInicial = this.usuarios.find(u => u.ID_Usuario === this.initialUserId);
          if (usuarioInicial) this.seleccionarUsuario(usuarioInicial);
        }
      },
      error: () => { this.cargandoUsuarios = false; }
    });
  }
  
  filtrarUsuarios(): void {
    if (!this.searchTerm.trim()) this.usuariosFiltrados = [...this.usuarios];
    else {
      const term = this.searchTerm.toLowerCase();
      this.usuariosFiltrados = this.usuarios.filter(u => u.nombre.toLowerCase().includes(term) || u.apellido.toLowerCase().includes(term) || u.email.toLowerCase().includes(term));
    }
  }
  
  seleccionarUsuario(usuario: User): void { this.selectedUser = usuario; this.cargarReportes(); }
  
  cargarReportes(): void {
    if (!this.selectedUser) return;
    this.reportService.getChat(this.currentUserId, this.selectedUser.ID_Usuario).subscribe({
      next: (data) => {
        this.reportes = data.reportes;
        if (this.initialReporteId && this.reportes.length > 0) {
          const reporteInicial = this.reportes.find(r => r.ID_Reporte === this.initialReporteId);
          if (reporteInicial) { this.selectedReporteId = reporteInicial.ID_Reporte; this.cargarComentarios(); }
          else if (this.reportes.length > 0) { this.selectedReporteId = this.reportes[0].ID_Reporte; this.cargarComentarios(); }
        } else if (this.reportes.length > 0) { this.selectedReporteId = this.reportes[0].ID_Reporte; this.cargarComentarios(); }
        else { this.comentarios = []; }
      },
      error: () => { this.comentarios = []; }
    });
  }
  
  cargarComentarios(): void {
    if (!this.selectedReporteId) return;
    this.cargandoMensajes = true;
    this.reportService.getComentariosByReporte(this.selectedReporteId).subscribe({
      next: (comentarios) => { this.comentarios = comentarios; this.cargandoMensajes = false; setTimeout(() => this.scrollToBottom(), 100); },
      error: () => { this.cargandoMensajes = false; }
    });
  }
  
  enviarMensaje(): void {
    if (!this.nuevoMensaje.trim() || !this.selectedUser) return;
    this.enviando = true;
    const crearReporte = () => {
      this.reportService.createReporte({ ID_Usuario_Emisor: this.currentUserId, ID_Usuario_Destinatario: this.selectedUser!.ID_Usuario, descripcion: `Chat con ${this.selectedUser!.nombre} ${this.selectedUser!.apellido}` }).subscribe({
        next: (reporteId) => { if (reporteId) { this.selectedReporteId = reporteId; this.enviarComentario(reporteId); } else { this.enviando = false; } },
        error: () => { this.enviando = false; }
      });
    };
    if (!this.selectedReporteId) crearReporte();
    else this.enviarComentario(this.selectedReporteId);
  }
  
  private enviarComentario(reporteId: string): void {
    this.reportService.createComentario(reporteId, this.currentUserId, this.nuevoMensaje).subscribe({
      next: (success) => {
        if (success) {
          this.onMessageSent.emit({ ID_Comentario: Date.now().toString(), ID_Reporte: reporteId, ID_Usuario_Emisor: this.currentUserId, comentario: this.nuevoMensaje, fecha_hora: new Date().toISOString(), nombre: this.currentUser?.nombre, apellido: this.currentUser?.apellido } as Comentario);
          this.cargarComentarios(); this.nuevoMensaje = '';
        }
        this.enviando = false;
      },
      error: () => { this.enviando = false; }
    });
  }
  
  getUnreadCount(userId: string): number { return 0; }
  cerrarChat(): void { this.selectedUser = null; this.selectedReporteId = null; this.comentarios = []; this.onClose.emit(); }
  private scrollToBottom(): void { setTimeout(() => { if (this.scrollAnchor) this.scrollAnchor.nativeElement.scrollIntoView({ behavior: 'smooth' }); }, 100); }
}