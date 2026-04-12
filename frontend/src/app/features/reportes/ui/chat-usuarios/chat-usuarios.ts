/**
 * @fileoverview Componente de Chat de Usuarios
 * @description Componente reutilizable para chat entre usuarios (modo panel)
 * @component ChatUsuariosComponent
 */

import { Component, Input, Output, EventEmitter, OnInit, OnDestroy, ElementRef, ViewChild, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { ReportesService } from '../../services/reportes';
import { User } from '@core/models/user.model';
import { Reporte, Comentario } from '@core/models/reporte.model';

@Component({
  selector: 'app-chat-usuarios',
  standalone: true,
  imports: [CommonModule, FormsModule, MatCardModule, MatButtonModule, MatIconModule, MatFormFieldModule, MatInputModule, MatSelectModule, MatProgressSpinnerModule],
  templateUrl: './chat-usuarios.html',
  styleUrls: ['./chat-usuarios.css']
})
export class ChatUsuariosComponent implements OnInit, OnDestroy {
  @Input() currentUser: User | null = null;
  @Input() asPanel = false;
  @Output() onClose = new EventEmitter<void>();
  
  private reportesService = inject(ReportesService);
  @ViewChild('scrollAnchor') scrollAnchor!: ElementRef;
  
  currentUserId = '';
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
  private refreshInterval: any;
  
  ngOnInit(): void {
    this.currentUserId = this.currentUser?.id || '';
    if (this.currentUserId) this.cargarUsuarios();
    this.refreshInterval = setInterval(() => { if (this.reporteSeleccionadoId) this.cargarComentarios(); }, 5000);
  }
  
  ngOnDestroy(): void { if (this.refreshInterval) clearInterval(this.refreshInterval); }
  
  cargarUsuarios(): void {
    this.cargandoUsuarios = true;
    this.reportesService.getUsuariosChat(this.currentUserId).subscribe({
      next: (usuarios) => { this.usuarios = usuarios.filter(u => u.id !== this.currentUserId); this.usuariosFiltrados = [...this.usuarios]; this.cargandoUsuarios = false; },
      error: () => { this.cargandoUsuarios = false; }
    });
  }
  
  filtrarUsuarios(): void {
    if (!this.searchTerm.trim()) this.usuariosFiltrados = [...this.usuarios];
    else {
      const term = this.searchTerm.toLowerCase();
      this.usuariosFiltrados = this.usuarios.filter(u => u.nombre.toLowerCase().includes(term) || u.apellido.toLowerCase().includes(term));
    }
  }
  
  seleccionarUsuario(usuario: User): void { this.usuarioSeleccionado = usuario; this.cargarReportes(); }
  
  cargarReportes(): void {
    if (!this.usuarioSeleccionado) return;
    this.reportesService.getChat(this.currentUserId, this.usuarioSeleccionado.id).subscribe({
      next: (data) => { this.reportes = data.reportes; if (this.reportes.length > 0) { this.reporteSeleccionadoId = this.reportes[0].ID_Reporte; this.cargarComentarios(); } }
    });
  }
  
  cargarComentarios(): void {
    if (!this.reporteSeleccionadoId) return;
    this.cargandoMensajes = true;
    this.reportesService.getComentarios(this.reporteSeleccionadoId).subscribe({
      next: (comentarios) => { this.comentarios = comentarios; this.cargandoMensajes = false; setTimeout(() => this.scrollToBottom(), 100); },
      error: () => { this.cargandoMensajes = false; }
    });
  }
  
  enviarMensaje(): void {
    if (!this.nuevoMensaje.trim() || !this.reporteSeleccionadoId) return;
    this.enviando = true;
    this.reportesService.crearComentario(this.reporteSeleccionadoId, this.currentUserId, this.nuevoMensaje).subscribe({
      next: (success) => { if (success) { this.cargarComentarios(); this.nuevoMensaje = ''; } this.enviando = false; },
      error: () => { this.enviando = false; }
    });
  }
  
  private scrollToBottom(): void { setTimeout(() => { if (this.scrollAnchor) this.scrollAnchor.nativeElement.scrollIntoView({ behavior: 'smooth' }); }, 100); }
}