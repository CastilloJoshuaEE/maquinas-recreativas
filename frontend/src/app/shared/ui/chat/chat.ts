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
import { MatTabsModule } from '@angular/material/tabs';
import { AuthService } from '@core/services/auth.service';
import { ReportService } from '@core/services/report.service';
import { NotificationService } from '@core/services/notification.service';
import { User } from '@core/models/user.model';
import { Reporte, Comentario } from '@core/models/reporte.model';
import { Subject, takeUntil, interval } from 'rxjs';

@Component({
  selector: 'app-chat',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatFormFieldModule,
    MatInputModule,
    MatProgressSpinnerModule,
    MatTabsModule
  ],
  template: `
    <div class="chat-container" [class.panel-mode]="asPanel" [class.modal-scrollable]="modalScrollable">
      <!-- Sidebar de usuarios -->
      <div class="chat-sidebar" *ngIf="showUserList">
        <div class="sidebar-header">
          <h3>Conversaciones</h3>
          <button mat-icon-button (click)="onClose.emit()" *ngIf="asPanel">
            <mat-icon>close</mat-icon>
          </button>
        </div>
        
        <div class="usuarios-search" *ngIf="showSearch">
          <mat-form-field appearance="outline" class="search-field">
            <mat-label>Buscar usuario</mat-label>
            <input matInput [(ngModel)]="searchTerm" (ngModelChange)="filtrarUsuarios()">
            <mat-icon matPrefix>search</mat-icon>
          </mat-form-field>
        </div>
        
        <div *ngIf="cargandoUsuarios" class="loading-users">
          <mat-spinner diameter="30"></mat-spinner>
        </div>
        
        <ul class="usuarios-list" *ngIf="!cargandoUsuarios">
          <li *ngFor="let usuario of usuariosFiltrados"
              class="usuario-chat-item"
              [class.activo]="selectedUser?.ID_Usuario === usuario.ID_Usuario"
              [ngClass]="'tipo-' + (usuario.tipo | lowercase)"
              (click)="seleccionarUsuario(usuario)">
            <div class="usuario-info">
              <strong>{{ usuario.nombre }} {{ usuario.apellido }}</strong>
              <small>{{ usuario.tipo }}</small>
              <small class="usuario-email">{{ usuario.email }}</small>
            </div>
            <div class="unread-badge" *ngIf="getUnreadCount(usuario.ID_Usuario) > 0">
              {{ getUnreadCount(usuario.ID_Usuario) }}
            </div>
          </li>
          
          <li *ngIf="usuariosFiltrados.length === 0" class="no-usuarios">
            No hay usuarios disponibles
          </li>
        </ul>
      </div>
      
      <!-- Área principal del chat -->
      <div class="chat-main">
        <!-- Cabecera -->
        <div class="chat-header" *ngIf="selectedUser">
          <div class="user-info">
            <h3>{{ selectedUser.nombre }} {{ selectedUser.apellido }}</h3>
            <small>{{ selectedUser.tipo }} - {{ selectedUser.email }}</small>
          </div>
          <button mat-icon-button (click)="cerrarChat()" *ngIf="!asPanel">
            <mat-icon>close</mat-icon>
          </button>
        </div>
        
        <div class="no-chat-selected" *ngIf="!selectedUser">
          <mat-icon>chat</mat-icon>
          <p>Selecciona un usuario para chatear</p>
        </div>
        
        <!-- Selector de reportes -->
        <div class="reportes-list" *ngIf="selectedUser && reportes.length > 0">
          <mat-form-field appearance="outline">
            <mat-label>Seleccionar reporte</mat-label>
            <mat-select [(ngModel)]="selectedReporteId" (selectionChange)="cargarComentarios()">
              <mat-option *ngFor="let reporte of reportes" [value]="reporte.ID_Reporte">
                Reporte #{{ reporte.ID_Reporte | slice:0:8 }} - {{ reporte.estado }}
              </mat-option>
            </mat-select>
          </mat-form-field>
        </div>
        
        <!-- Mensajes -->
        <div class="chat-mensajes" #mensajesContainer>
          <div *ngIf="cargandoMensajes" class="loading-mensajes">
            <mat-spinner diameter="30"></mat-spinner>
          </div>
          
          <div *ngFor="let comentario of comentarios" class="comentario-item" [ngClass]="{'emisor': comentario.ID_Usuario_Emisor === currentUserId, 'receptor': comentario.ID_Usuario_Emisor !== currentUserId}">
            <div class="comentario-header">
              <strong>{{ comentario.nombre }} {{ comentario.apellido }}</strong>
              <small>{{ comentario.fecha_hora | date:'dd/MM/yyyy HH:mm' }}</small>
            </div>
            <p>{{ comentario.comentario }}</p>
          </div>
          
          <div *ngIf="!cargandoMensajes && comentarios.length === 0 && selectedUser" class="no-mensajes">
            <p>No hay mensajes aún. ¡Envía el primero!</p>
          </div>
          
          <div #scrollAnchor></div>
        </div>
        
        <!-- Formulario de envío -->
        <form class="chat-form" (ngSubmit)="enviarMensaje()" *ngIf="selectedUser">
          <mat-form-field appearance="outline" class="message-input">
            <mat-label>Escribe un mensaje...</mat-label>
            <textarea matInput [(ngModel)]="nuevoMensaje" name="mensaje" rows="2" [disabled]="enviando"></textarea>
          </mat-form-field>
          <button mat-raised-button color="primary" type="submit" [disabled]="!nuevoMensaje.trim() || enviando">
            <mat-icon>send</mat-icon>
            Enviar
          </button>
        </form>
      </div>
    </div>
  `,
  styles: [`
    .chat-container {
      display: flex;
      height: 100%;
      min-height: 500px;
      background: white;
      border-radius: 12px;
      overflow: hidden;
    }
    
    .chat-container.panel-mode {
      height: 600px;
    }
    
    .chat-sidebar {
      width: 300px;
      border-right: 1px solid #e0e0e0;
      display: flex;
      flex-direction: column;
      background: #f8f9fa;
    }
    
    .sidebar-header {
      padding: 1rem;
      border-bottom: 1px solid #e0e0e0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .sidebar-header h3 {
      margin: 0;
      color: #2c3e50;
    }
    
    .usuarios-search {
      padding: 0.75rem;
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
      width: 22px;
      height: 22px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.7rem;
      font-weight: bold;
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
    
    @media (max-width: 768px) {
      .chat-container {
        flex-direction: column;
      }
      
      .chat-sidebar {
        width: 100%;
        max-height: 300px;
      }
      
      .comentario-item {
        max-width: 90%;
      }
    }
  `]
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
  private notificationService = inject(NotificationService);
  
  @ViewChild('mensajesContainer') mensajesContainer!: ElementRef;
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
    if (this.currentUserId) {
      this.cargarUsuarios();
    }
    
    // Refrescar mensajes cada 5 segundos
    this.refreshInterval = setInterval(() => {
      if (this.selectedUser && this.selectedReporteId) {
        this.cargarComentarios();
      }
    }, 5000);
  }
  
  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
    if (this.refreshInterval) {
      clearInterval(this.refreshInterval);
    }
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
          if (usuarioInicial) {
            this.seleccionarUsuario(usuarioInicial);
          }
        }
      },
      error: () => {
        this.cargandoUsuarios = false;
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
    this.selectedUser = usuario;
    this.cargarReportes();
  }
  
  cargarReportes(): void {
    if (!this.selectedUser) return;
    
    this.reportService.getChat(this.currentUserId, this.selectedUser.ID_Usuario).subscribe({
      next: (data) => {
        this.reportes = data.reportes;
        
        if (this.initialReporteId && this.reportes.length > 0) {
          const reporteInicial = this.reportes.find(r => r.ID_Reporte === this.initialReporteId);
          if (reporteInicial) {
            this.selectedReporteId = reporteInicial.ID_Reporte;
            this.cargarComentarios();
          } else if (this.reportes.length > 0) {
            this.selectedReporteId = this.reportes[0].ID_Reporte;
            this.cargarComentarios();
          }
        } else if (this.reportes.length > 0) {
          this.selectedReporteId = this.reportes[0].ID_Reporte;
          this.cargarComentarios();
        } else {
          this.comentarios = [];
        }
      },
      error: () => {
        this.comentarios = [];
      }
    });
  }
  
  cargarComentarios(): void {
    if (!this.selectedReporteId) return;
    
    this.cargandoMensajes = true;
    this.reportService.getComentariosByReporte(this.selectedReporteId).subscribe({
      next: (comentarios) => {
        this.comentarios = comentarios;
        this.cargandoMensajes = false;
        setTimeout(() => this.scrollToBottom(), 100);
      },
      error: () => {
        this.cargandoMensajes = false;
      }
    });
  }
  
  enviarMensaje(): void {
    if (!this.nuevoMensaje.trim() || !this.selectedUser) return;
    
    this.enviando = true;
    
    const crearReporte = () => {
      this.reportService.createReporte({
        ID_Usuario_Emisor: this.currentUserId,
        ID_Usuario_Destinatario: this.selectedUser!.ID_Usuario,
        descripcion: `Chat con ${this.selectedUser!.nombre} ${this.selectedUser!.apellido}`
      }).subscribe({
        next: (reporteId) => {
          if (reporteId) {
            this.selectedReporteId = reporteId;
            this.enviarComentario(reporteId);
          } else {
            this.enviando = false;
          }
        },
        error: () => {
          this.enviando = false;
        }
      });
    };
    
    if (!this.selectedReporteId) {
      crearReporte();
    } else {
      this.enviarComentario(this.selectedReporteId);
    }
  }
  
  private enviarComentario(reporteId: string): void {
    this.reportService.createComentario(reporteId, this.currentUserId, this.nuevoMensaje).subscribe({
      next: (success) => {
        if (success) {
          this.onMessageSent.emit({
            ID_Comentario: Date.now().toString(),
            ID_Reporte: reporteId,
            ID_Usuario_Emisor: this.currentUserId,
            comentario: this.nuevoMensaje,
            fecha_hora: new Date().toISOString(),
            nombre: this.currentUser?.nombre,
            apellido: this.currentUser?.apellido
          } as Comentario);
          this.cargarComentarios();
          this.nuevoMensaje = '';
        }
        this.enviando = false;
      },
      error: () => {
        this.enviando = false;
      }
    });
  }
  
  getUnreadCount(userId: string): number {
    // Implementar lógica de mensajes no leídos
    return 0;
  }
  
  cerrarChat(): void {
    this.selectedUser = null;
    this.selectedReporteId = null;
    this.comentarios = [];
    this.onClose.emit();
  }
  
  private scrollToBottom(): void {
    if (this.scrollAnchor) {
      this.scrollAnchor.nativeElement.scrollIntoView({ behavior: 'smooth' });
    }
  }
}