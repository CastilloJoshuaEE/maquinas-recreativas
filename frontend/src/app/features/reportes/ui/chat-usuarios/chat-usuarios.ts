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
import { ReportesService } from '../../services/reportes.service';
import { User } from '@core/models/user.model';
import { Reporte, Comentario } from '@core/models/reporte.model';

@Component({
  selector: 'app-chat-usuarios',
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
    MatProgressSpinnerModule
  ],
  template: `
    <div class="chat-panel" [class.panel-mode]="asPanel">
      <div class="chat-panel-header" *ngIf="asPanel">
        <h3>Chat de Usuarios</h3>
        <button mat-icon-button (click)="onClose.emit()">
          <mat-icon>close</mat-icon>
        </button>
      </div>
      
      <div class="chat-panel-content">
        <div class="chat-sidebar">
          <div class="usuarios-search">
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
                [class.activo]="usuarioSeleccionado?.ID_Usuario === usuario.ID_Usuario"
                (click)="seleccionarUsuario(usuario)">
              <div class="usuario-info">
                <strong>{{ usuario.nombre }} {{ usuario.apellido }}</strong>
                <small>{{ usuario.tipo }}</small>
              </div>
            </li>
          </ul>
        </div>
        
        <div class="chat-area">
          <div *ngIf="!usuarioSeleccionado" class="no-selected">
            <mat-icon>chat</mat-icon>
            <p>Selecciona un usuario para chatear</p>
          </div>
          
          <ng-container *ngIf="usuarioSeleccionado">
            <div class="chat-header">
              <strong>{{ usuarioSeleccionado.nombre }} {{ usuarioSeleccionado.apellido }}</strong>
              <small>{{ usuarioSeleccionado.tipo }}</small>
            </div>
            
            <div class="reportes-selector" *ngIf="reportes.length > 0">
              <mat-form-field appearance="outline">
                <mat-label>Seleccionar reporte</mat-label>
                <mat-select [(ngModel)]="reporteSeleccionadoId" (selectionChange)="cargarComentarios()">
                  <mat-option *ngFor="let reporte of reportes" [value]="reporte.ID_Reporte">
                    Reporte #{{ reporte.ID_Reporte | slice:0:8 }} - {{ reporte.estado }}
                  </mat-option>
                </mat-select>
              </mat-form-field>
            </div>
            
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
              
              <div #scrollAnchor></div>
            </div>
            
            <form class="chat-form" (ngSubmit)="enviarMensaje()" *ngIf="reporteSeleccionadoId">
              <mat-form-field appearance="outline" class="message-input">
                <mat-label>Escribe un mensaje...</mat-label>
                <textarea matInput [(ngModel)]="nuevoMensaje" name="mensaje" rows="2" [disabled]="enviando"></textarea>
              </mat-form-field>
              <button mat-raised-button color="primary" type="submit" [disabled]="!nuevoMensaje.trim() || enviando">
                <mat-icon>send</mat-icon>
              </button>
            </form>
          </ng-container>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .chat-panel {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      height: 100%;
      display: flex;
      flex-direction: column;
    }
    
    .chat-panel.panel-mode {
      height: 500px;
    }
    
    .chat-panel-header {
      padding: 1rem;
      background: linear-gradient(135deg, #4f6bed, #3d55c3);
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .chat-panel-header h3 {
      margin: 0;
      color: white;
    }
    
    .chat-panel-content {
      display: flex;
      flex: 1;
      overflow: hidden;
    }
    
    .chat-sidebar {
      width: 250px;
      border-right: 1px solid #e0e0e0;
      display: flex;
      flex-direction: column;
      background: #f8f9fa;
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
      padding: 0.75rem;
      cursor: pointer;
      transition: background 0.2s;
      border-left: 3px solid transparent;
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
      font-size: 0.7rem;
      color: #666;
    }
    
    .loading-users {
      display: flex;
      justify-content: center;
      padding: 2rem;
    }
    
    .chat-area {
      flex: 1;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }
    
    .no-selected {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: #999;
    }
    
    .chat-header {
      padding: 0.75rem;
      border-bottom: 1px solid #e0e0e0;
      background: #f8f9fa;
    }
    
    .reportes-selector {
      padding: 0.75rem;
      border-bottom: 1px solid #e0e0e0;
    }
    
    .reportes-selector mat-form-field {
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
      max-width: 85%;
      padding: 0.5rem 0.75rem;
      border-radius: 12px;
    }
    
    .comentario-item.emisor {
      align-self: flex-end;
      background: #4f6bed;
      color: white;
      border-bottom-right-radius: 4px;
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
      font-size: 0.7rem;
    }
    
    .comentario-item p {
      margin: 0;
      font-size: 0.85rem;
      word-wrap: break-word;
    }
    
    .loading-mensajes {
      display: flex;
      justify-content: center;
      padding: 2rem;
    }
    
    .chat-form {
      display: flex;
      gap: 0.5rem;
      padding: 0.75rem;
      border-top: 1px solid #e0e0e0;
      background: white;
    }
    
    .message-input {
      flex: 1;
    }
    
    .message-input textarea {
      resize: none;
      font-size: 0.85rem;
    }
    
    @media (max-width: 768px) {
      .chat-panel-content {
        flex-direction: column;
      }
      
      .chat-sidebar {
        width: 100%;
        max-height: 200px;
      }
    }
  `]
})
export class ChatUsuariosComponent implements OnInit, OnDestroy {
  @Input() currentUser: User | null = null;
  @Input() asPanel = false;
  @Output() onClose = new EventEmitter<void>();
  
  private reportesService = inject(ReportesService);
  
  @ViewChild('mensajesContainer') mensajesContainer!: ElementRef;
  @ViewChild('scrollAnchor') scrollAnchor!: ElementRef;
  
  currentUserId = '';
  
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
  
  private refreshInterval: any;
  
  ngOnInit(): void {
    this.currentUserId = this.currentUser?.ID_Usuario || '';
    if (this.currentUserId) {
      this.cargarUsuarios();
    }
    
    this.refreshInterval = setInterval(() => {
      if (this.reporteSeleccionadoId) {
        this.cargarComentarios();
      }
    }, 5000);
  }
  
  ngOnDestroy(): void {
    if (this.refreshInterval) {
      clearInterval(this.refreshInterval);
    }
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
        u.apellido.toLowerCase().includes(term)
      );
    }
  }
  
  seleccionarUsuario(usuario: User): void {
    this.usuarioSeleccionado = usuario;
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
        }
      }
    });
  }
  
  cargarComentarios(): void {
    if (!this.reporteSeleccionadoId) return;
    
    this.cargandoMensajes = true;
    this.reportesService.getComentarios(this.reporteSeleccionadoId).subscribe({
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
    if (!this.nuevoMensaje.trim() || !this.reporteSeleccionadoId) return;
    
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
        }
        this.enviando = false;
      },
      error: () => {
        this.enviando = false;
      }
    });
  }
  
  private scrollToBottom(): void {
    setTimeout(() => {
      if (this.scrollAnchor) {
        this.scrollAnchor.nativeElement.scrollIntoView({ behavior: 'smooth' });
      }
    }, 100);
  }
}