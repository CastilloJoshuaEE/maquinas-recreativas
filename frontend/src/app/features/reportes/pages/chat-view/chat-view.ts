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
import { ReportesService } from '../../services/reportes.service';
import { AuthService } from '@core/services/auth.service';
import { User } from '@core/models/user.model';
import { Reporte, Comentario } from '@core/models/reporte.model';
import { Subscription, interval } from 'rxjs';

@Component({
  selector: 'app-chat-view',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatProgressSpinnerModule,
    AdminHeaderComponent
  ],
  template: `
    <div class="chat-view-container">
      <app-admin-header></app-admin-header>
      
      <div class="content-wrapper">
        <div class="page-header">
          <button mat-icon-button (click)="regresar()" class="back-button">
            <mat-icon>arrow_back</mat-icon>
          </button>
          <h2>Chat de Usuarios</h2>
        </div>

        <div class="chat-container">
          <!-- Sidebar de usuarios -->
          <div class="chat-sidebar">
            <div class="sidebar-header">
              <h3>Conversaciones</h3>
              <div class="usuarios-search">
                <mat-form-field appearance="outline" class="search-field">
                  <mat-label>Buscar usuario</mat-label>
                  <input matInput [(ngModel)]="searchTerm" (ngModelChange)="filtrarUsuarios()">
                  <mat-icon matPrefix>search</mat-icon>
                </mat-form-field>
              </div>
            </div>
            
            <div *ngIf="cargandoUsuarios" class="loading-users">
              <mat-spinner diameter="30"></mat-spinner>
            </div>
            
            <ul class="usuarios-list" *ngIf="!cargandoUsuarios">
              <li *ngFor="let usuario of usuariosFiltrados"
                  class="usuario-chat-item"
                  [class.activo]="usuarioSeleccionado?.ID_Usuario === usuario.ID_Usuario"
                  [ngClass]="'tipo-' + (usuario.tipo | lowercase)"
                  (click)="seleccionarUsuario(usuario)">
                <div class="usuario-info">
                  <strong>{{ usuario.nombre }} {{ usuario.apellido }}</strong>
                  <small>{{ usuario.tipo }}</small>
                  <small class="usuario-email">{{ usuario.email }}</small>
                </div>
                <div class="unread-badge" *ngIf="obtenerNoLeidas(usuario.ID_Usuario) > 0">
                  {{ obtenerNoLeidas(usuario.ID_Usuario) }}
                </div>
              </li>
              
              <li *ngIf="usuariosFiltrados.length === 0" class="no-usuarios">
                No hay usuarios disponibles
              </li>
            </ul>
          </div>
          
          <!-- Área principal del chat -->
          <div class="chat-main">
            <div *ngIf="!usuarioSeleccionado" class="no-chat-selected">
              <mat-icon>chat</mat-icon>
              <p>Selecciona un usuario para chatear</p>
            </div>
            
            <ng-container *ngIf="usuarioSeleccionado">
              <!-- Cabecera -->
              <div class="chat-header">
                <div class="user-info">
                  <h3>{{ usuarioSeleccionado.nombre }} {{ usuarioSeleccionado.apellido }}</h3>
                  <small>{{ usuarioSeleccionado.tipo }} - {{ usuarioSeleccionado.email }}</small>
                </div>
              </div>
              
              <!-- Selector de reportes -->
              <div class="reportes-list" *ngIf="reportes.length > 0">
                <mat-form-field appearance="outline">
                  <mat-label>Seleccionar reporte</mat-label>
                  <mat-select [(ngModel)]="reporteSeleccionadoId" (selectionChange)="cargarComentarios()">
                    <mat-option *ngFor="let reporte of reportes" [value]="reporte.ID_Reporte">
                      Reporte #{{ reporte.ID_Reporte | slice:0:8 }} - {{ reporte.estado }}
                    </mat-option>
                    <mat-option value="nuevo">+ Crear nuevo reporte</mat-option>
                  </mat-select>
                </mat-form-field>
              </div>
              
              <div class="reportes-list" *ngIf="reportes.length === 0">
                <button mat-raised-button color="primary" (click)="crearNuevoReporte()">
                  <mat-icon>add</mat-icon>
                  Iniciar nueva conversación
                </button>
              </div>
              
              <!-- Mensajes -->
              <div class="chat-mensajes" #mensajesContainer>
                <div *ngIf="cargandoMensajes" class="loading-mensajes">
                  <mat-spinner diameter="30"></mat-spinner>
                </div>
                
                <div *ngFor="let comentario of comentarios" 
                     class="comentario-item" 
                     [ngClass]="{'emisor': comentario.ID_Usuario_Emisor === currentUserId, 'receptor': comentario.ID_Usuario_Emisor !== currentUserId}">
                  <div class="comentario-header">
                    <strong>{{ comentario.nombre }} {{ comentario.apellido }}</strong>
                    <small>{{ comentario.fecha_hora | date:'dd/MM/yyyy HH:mm' }}</small>
                  </div>
                  <p>{{ comentario.comentario }}</p>
                </div>
                
                <div *ngIf="!cargandoMensajes && comentarios.length === 0 && reporteSeleccionadoId && reporteSeleccionadoId !== 'nuevo'" class="no-mensajes">
                  <p>No hay mensajes aún. ¡Envía el primero!</p>
                </div>
                
                <div #scrollAnchor></div>
              </div>
              
              <!-- Formulario de envío -->
              <form class="chat-form" (ngSubmit)="enviarMensaje()" *ngIf="reporteSeleccionadoId && reporteSeleccionadoId !== 'nuevo'">
                <mat-form-field appearance="outline" class="message-input">
                  <mat-label>Escribe un mensaje...</mat-label>
                  <textarea matInput [(ngModel)]="nuevoMensaje" name="mensaje" rows="2" [disabled]="enviando"></textarea>
                </mat-form-field>
                <button mat-raised-button color="primary" type="submit" [disabled]="!nuevoMensaje.trim() || enviando">
                  <mat-icon>send</mat-icon>
                  Enviar
                </button>
              </form>
            </ng-container>
          </div>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .chat-view-container {
      min-height: 100vh;
      background: linear-gradient(135deg, #07224c 0%, #124258 50%, #3b4a66 100%);
    }
    
    .content-wrapper {
      max-width: 1400px;
      margin: 0 auto;
      padding: 2rem;
    }
    
    .page-header {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 2rem;
    }
    
    .page-header h2 {
      color: white;
      margin: 0;
    }
    
    .back-button {
      color: white;
      background: rgba(255, 255, 255, 0.15);
    }
    
    .chat-container {
      display: flex;
      height: calc(100vh - 200px);
      min-height: 500px;
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    }
    
    .chat-sidebar {
      width: 320px;
      border-right: 1px solid #e0e0e0;
      display: flex;
      flex-direction: column;
      background: #f8f9fa;
    }
    
    .sidebar-header {
      padding: 1rem;
      border-bottom: 1px solid #e0e0e0;
    }
    
    .sidebar-header h3 {
      margin: 0 0 0.75rem 0;
      color: #2c3e50;
    }
    
    .search-field {
      width: 100%;
    }
    
    .usuarios-list {
      list-style: none;
      padding: 0;
      margin: 0;
      overflow-y: auto;
      flex: 1;
    }
    
    .usuario-chat-item {
      padding: 0.75rem 1rem;
      cursor: pointer;
      transition: background 0.2s;
      border-left: 3px solid transparent;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .usuario-chat-item:hover {
      background: #e9ecef;
    }
    
    .usuario-chat-item.activo {
      background: #e3f2fd;
      border-left-color: #4f6bed;
    }
    
    .usuario-info strong {
      display: block;
      color: #333;
    }
    
    .usuario-info small {
      font-size: 0.75rem;
      color: #666;
    }
    
    .usuario-email {
      display: block;
      font-size: 0.7rem;
      color: #999;
    }
    
    .unread-badge {
      background: #4f6bed;
      color: white;
      border-radius: 50%;
      min-width: 22px;
      height: 22px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.7rem;
      font-weight: bold;
      padding: 0 4px;
    }
    
    .no-usuarios {
      padding: 1rem;
      text-align: center;
      color: #999;
    }
    
    .loading-users {
      display: flex;
      justify-content: center;
      padding: 2rem;
    }
    
    .chat-main {
      flex: 1;
      display: flex;
      flex-direction: column;
      background: white;
    }
    
    .no-chat-selected {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: #999;
    }
    
    .no-chat-selected mat-icon {
      font-size: 4rem;
      width: auto;
      height: auto;
      margin-bottom: 1rem;
    }
    
    .chat-header {
      padding: 1rem;
      border-bottom: 1px solid #e0e0e0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .user-info h3 {
      margin: 0;
      color: #2c3e50;
    }
    
    .user-info small {
      color: #666;
    }
    
    .reportes-list {
      padding: 0.75rem 1rem;
      border-bottom: 1px solid #e0e0e0;
    }
    
    .reportes-list mat-form-field {
      width: 100%;
    }
    
    .chat-mensajes {
      flex: 1;
      overflow-y: auto;
      padding: 1rem;
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
      background: #f5f5f5;
    }
    
    .comentario-item {
      max-width: 75%;
      padding: 0.75rem 1rem;
      border-radius: 12px;
      position: relative;
    }
    
    .comentario-item.emisor {
      align-self: flex-end;
      background: #4f6bed;
      color: white;
      border-bottom-right-radius: 4px;
    }
    
    .comentario-item.emisor .comentario-header {
      color: rgba(255, 255, 255, 0.8);
    }
    
    .comentario-item.receptor {
      align-self: flex-start;
      background: white;
      border: 1px solid #e0e0e0;
      border-bottom-left-radius: 4px;
    }
    
    .comentario-header {
      display: flex;
      justify-content: space-between;
      margin-bottom: 0.25rem;
      font-size: 0.75rem;
    }
    
    .comentario-item p {
      margin: 0;
      word-wrap: break-word;
    }
    
    .loading-mensajes {
      display: flex;
      justify-content: center;
      padding: 2rem;
    }
    
    .no-mensajes {
      text-align: center;
      padding: 2rem;
      color: #999;
    }
    
    .chat-form {
      display: flex;
      gap: 0.5rem;
      padding: 1rem;
      border-top: 1px solid #e0e0e0;
      background: white;
    }
    
    .message-input {
      flex: 1;
    }
    
    .message-input textarea {
      resize: none;
    }
    
    /* Estilos por tipo de usuario */
    .tipo-administrador .usuario-info strong { color: #004085; }
    .tipo-contabilidad .usuario-info strong { color: #155724; }
    .tipo-logistica .usuario-info strong { color: #0c5460; }
    .tipo-tecnico .usuario-info strong { color: #4a1d6d; }
    
    @media (max-width: 768px) {
      .content-wrapper {
        padding: 1rem;
      }
      
      .chat-container {
        flex-direction: column;
        height: calc(100vh - 150px);
      }
      
      .chat-sidebar {
        width: 100%;
        max-height: 300px;
      }
      
      .comentario-item {
        max-width: 90%;
      }
      
      .chat-form {
        flex-direction: column;
      }
      
      .chat-form button {
        width: 100%;
      }
    }
  `]
})
export class ChatViewComponent implements OnInit, OnDestroy {
  private route = inject(ActivatedRoute);
  private router = inject(Router);
  private reportesService = inject(ReportesService);
  private authService = inject(AuthService);
  private snackBar = inject(MatSnackBar);
  
  @ViewChild('mensajesContainer') mensajesContainer!: ElementRef;
  @ViewChild('scrollAnchor') scrollAnchor!: ElementRef;
  
  currentUserId = '';
  currentUser: User | null = null;
  
  // Usuarios
  usuarios: User[] = [];
  usuariosFiltrados: User[] = [];
  searchTerm = '';
  usuarioSeleccionado: User | null = null;
  cargandoUsuarios = false;
  
  // Reportes y mensajes
  reportes: Reporte[] = [];
  reporteSeleccionadoId: string | null = null;
  comentarios: Comentario[] = [];
  nuevoMensaje = '';
  
  // Estados
  cargandoMensajes = false;
  enviando = false;
  
  // Contador de no leídos
  mensajesNoLeidos: { [key: string]: number } = {};
  
  private refreshInterval: any;
  private subscriptions: Subscription[] = [];
  
  ngOnInit(): void {
    this.currentUser = this.authService.getCurrentUser();
    this.currentUserId = this.currentUser?.ID_Usuario || '';
    
    if (this.currentUserId) {
      this.cargarUsuarios();
      this.inicializarDesdeParams();
    }
    
    // Refrescar mensajes cada 5 segundos
    this.refreshInterval = setInterval(() => {
      if (this.reporteSeleccionadoId && this.reporteSeleccionadoId !== 'nuevo') {
        this.cargarComentarios();
      }
    }, 5000);
  }
  
  ngOnDestroy(): void {
    if (this.refreshInterval) {
      clearInterval(this.refreshInterval);
    }
    this.subscriptions.forEach(sub => sub.unsubscribe());
  }
  
  private inicializarDesdeParams(): void {
    const reporteId = this.route.snapshot.params['reporteId'];
    const emisorId = this.route.snapshot.params['emisorId'];
    const destinatarioId = this.route.snapshot.params['destinatarioId'];
    
    if (reporteId) {
      // Cargar chat por ID de reporte
      this.cargarReportePorId(reporteId);
    } else if (emisorId && destinatarioId) {
      // Cargar chat entre dos usuarios
      const otroId = emisorId === this.currentUserId ? destinatarioId : emisorId;
      this.cargarUsuarioPorId(otroId);
    }
  }
  
  private cargarReportePorId(reporteId: string): void {
    this.reportesService.getReportesByUser(this.currentUserId).subscribe({
      next: (reportes) => {
        const reporte = reportes.find(r => r.ID_Reporte === reporteId);
        if (reporte) {
          const otroId = reporte.ID_Usuario_Emisor === this.currentUserId 
            ? reporte.ID_Usuario_Destinatario 
            : reporte.ID_Usuario_Emisor;
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
        if (usuario) {
          this.usuarioSeleccionado = usuario;
          this.cargarReportes();
        }
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
      error: () => {
        this.cargandoUsuarios = false;
        this.snackBar.error('Error al cargar usuarios', 'Cerrar');
      }
    });
  }
  
  filtrarUsuarios(): void {
    if (!this.searchTerm.trim()) {
      this.usuariosFiltrados = [...this.usuarios];
    } else {
      const term = this.searchTerm.toLowerCase();
      this.usuariosFiltrados = this.usuarios.filter(u =>
        u.nombre.toLowerCase().includes(term) ||
        u.apellido.toLowerCase().includes(term) ||
        u.email.toLowerCase().includes(term)
      );
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
        
        if (this.reportes.length > 0) {
          this.reporteSeleccionadoId = this.reportes[0].ID_Reporte;
          this.cargarComentarios();
        } else {
          this.reporteSeleccionadoId = null;
        }
      },
      error: () => {
        this.snackBar.error('Error al cargar reportes', 'Cerrar');
      }
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
        
        // Marcar mensajes como leídos
        this.marcarComoLeidos();
      },
      error: () => {
        this.cargandoMensajes = false;
      }
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
        if (reporteId) {
          this.reporteSeleccionadoId = reporteId;
          this.cargarReportes();
          this.snackBar.success('Conversación iniciada', 'Éxito');
        } else {
          this.snackBar.error('Error al crear conversación', 'Error');
        }
      },
      error: () => {
        this.snackBar.error('Error al crear conversación', 'Error');
      }
    });
  }
  
  enviarMensaje(): void {
    if (!this.nuevoMensaje.trim() || !this.reporteSeleccionadoId || this.reporteSeleccionadoId === 'nuevo') return;
    
    this.enviando = true;
    
    this.reportesService.crearComentario(
      this.reporteSeleccionadoId,
      this.currentUserId,
      this.nuevoMensaje
    ).subscribe({
      next: (success) => {
        if (success) {
          this.cargarComentarios();
          this.nuevoMensaje = '';
        } else {
          this.snackBar.error('Error al enviar mensaje', 'Error');
        }
        this.enviando = false;
      },
      error: () => {
        this.snackBar.error('Error al enviar mensaje', 'Error');
        this.enviando = false;
      }
    });
  }
  
  private marcarComoLeidos(): void {
    // Actualizar contador de no leídos para este usuario
    if (this.usuarioSeleccionado) {
      this.mensajesNoLeidos[this.usuarioSeleccionado.ID_Usuario] = 0;
    }
  }
  
  obtenerNoLeidas(usuarioId: string): number {
    return this.mensajesNoLeidos[usuarioId] || 0;
  }
  
  private scrollToBottom(): void {
    setTimeout(() => {
      if (this.scrollAnchor) {
        this.scrollAnchor.nativeElement.scrollIntoView({ behavior: 'smooth' });
      }
    }, 100);
  }
  
  regresar(): void {
    this.router.navigate(['/reportes/gestion']);
  }
}