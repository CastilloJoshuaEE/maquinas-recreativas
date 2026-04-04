/**
 * @fileoverview Dashboard de Técnico Comprobador
 * @description Panel para técnicos comprobadores con checklist de calidad
 * @component DashboardComprobadorComponent
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header.component';
import { TecnicoService } from '../../services/tecnico.service';
import { AuthService } from '@core/services/auth.service';
import { NotificationService } from '@core/services/notification.service';
import { Maquina } from '@core/models/maquina.model';
import { User } from '@core/models/user.model';
import { HistorialMaquinaComponent } from '../ui/historial-maquina/historial-maquina';

@Component({
  selector: 'app-dashboard-comprobador',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatCheckboxModule,
    MatProgressSpinnerModule,
    AdminHeaderComponent,
    HistorialMaquinaComponent
  ],
  template: `
    <div class="dashboard-container">
      <app-admin-header></app-admin-header>
      
      <div class="dashboard-sections">
        <!-- Perfil del Usuario -->
        <section class="profile-section" *ngIf="user">
          <h2>Perfil</h2>
          <div class="profile-info">
            <p><strong>Cédula:</strong> {{ user.ci }}</p>
            <p><strong>Nombre:</strong> {{ user.nombre }} {{ user.apellido }}</p>
            <p><strong>Especialidad:</strong> {{ user.Especialidad }}</p>
          </div>
        </section>

        <hr />

        <!-- Notificaciones -->
        <section class="notifications-section">
          <h2 (click)="mostrarNotificaciones = !mostrarNotificaciones">
            Notificaciones {{ notificacionesNoLeidas > 0 ? '(' + notificacionesNoLeidas + ')' : '' }}
            {{ mostrarNotificaciones ? '🔽' : '▶️' }}
          </h2>
          <div *ngIf="mostrarNotificaciones">
            <div *ngIf="cargandoNotificaciones" class="loading-small">
              <mat-spinner diameter="30"></mat-spinner>
            </div>
            <div *ngIf="!cargandoNotificaciones && notificaciones.length === 0" class="no-data">
              <p>No hay notificaciones</p>
            </div>
            <ul *ngIf="!cargandoNotificaciones && notificaciones.length > 0" class="notificaciones-list">
              <li *ngFor="let notif of notificaciones" 
                  class="notificacion-item" 
                  [class.no-leida]="!notif.leida"
                  (click)="marcarNotificacionLeida(notif.ID_Notificaciones)">
                <p>
                  <strong>{{ notif.Tipo }}</strong> - {{ notif.fecha_hora | date:'dd/MM/yyyy HH:mm' }}
                  <br />
                  <span class="notificacion-content">{{ notif.mensaje }}</span>
                  <br />
                  <span class="notificacion-content" *ngIf="notif.Nombre_Maquina">
                    <strong>Máquina:</strong> {{ notif.Nombre_Maquina }}
                  </span>
                </p>
              </li>
            </ul>
          </div>
        </section>

        <hr />

        <!-- Máquinas -->
        <section class="machines-section">
          <h2>Máquinas Recreativas</h2>
          
          <!-- Máquinas Comprobando -->
          <div class="machine-list">
            <h3 (click)="mostrarComprobando = !mostrarComprobando">
              Comprobando {{ mostrarComprobando ? '🔽' : '▶️' }}
            </h3>
            <div *ngIf="mostrarComprobando">
              <div *ngIf="cargandoComprobando" class="loading-small">
                <mat-spinner diameter="30"></mat-spinner>
              </div>
              <div *ngIf="!cargandoComprobando && maquinasComprobando.length === 0" class="no-data">
                <p>No hay máquinas para comprobar...</p>
              </div>
              <ul *ngIf="!cargandoComprobando && maquinasComprobando.length > 0">
                <li *ngFor="let maquina of maquinasComprobando"
                    class="li-maquina"
                    [class.selected]="selectedMaquina?.ID_Maquina === maquina.ID_Maquina"
                    (click)="seleccionarMaquina(maquina)">
                  <div class="li-maquina-content">
                    <strong>{{ maquina.Nombre_Maquina }}</strong>
                    <br />
                    <span class="span-comercio-details">
                      <strong>Comercio:</strong> {{ maquina.NombreComercio }}
                    </span>
                    <span class="span-comercio-details">
                      <strong>Dirección:</strong> {{ maquina.DireccionComercio }}
                    </span>
                  </div>
                  <div class="maquina-actions">
                    <button mat-icon-button (click)="verHistorial(maquina); $event.stopPropagation()" matTooltip="Ver Historial">
                      <mat-icon>history</mat-icon>
                    </button>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </section>

        <!-- Checklist de Comprobación -->
        <div class="checklist-modal" *ngIf="selectedMaquina">
          <h3>Checklist de comprobación - {{ selectedMaquina.Nombre_Maquina }}</h3>
          
          <div class="checklist-item">
            <mat-checkbox [(ngModel)]="checklist.placaFuncional">
              ¿Placa funcional?
            </mat-checkbox>
          </div>
          
          <div class="checklist-item">
            <mat-checkbox [(ngModel)]="checklist.carcasaBuenEstado">
              ¿Carcasa en buen estado?
            </mat-checkbox>
          </div>
          
          <div class="checklist-item">
            <mat-checkbox [(ngModel)]="checklist.experienciaJuegoAcorde">
              ¿Experiencia de juego acorde?
            </mat-checkbox>
          </div>
          
          <div class="action-buttons">
            <button mat-raised-button color="primary" 
                    (click)="abrirModalMensaje('distribucion')"
                    [disabled]="!allChecksPassed()">
              Mandar a distribución ✅
            </button>
            <button mat-raised-button color="warn" 
                    (click)="abrirModalMensaje('reensamblar')">
              Mandar a reensamblar 🔄
            </button>
          </div>
        </div>
      </div>

      <!-- Modal de Mensaje -->
      <div class="modal-overlay" *ngIf="mostrarModalMensaje" (click)="cerrarModalMensaje($event)">
        <div class="modal-mensaje" (click)="$event.stopPropagation()">
          <div class="modal-header">
            <h3>Mensaje {{ accionActual === 'distribucion' ? 'para logística' : 'para el técnico ensamblador' }}</h3>
            <button class="modal-close" (click)="cerrarModalMensaje()">×</button>
          </div>
          <div class="modal-body">
            <textarea 
              [(ngModel)]="mensaje"
              placeholder="Escribe un mensaje..."
              rows="4"
              class="mensaje-textarea"></textarea>
          </div>
          <div class="modal-footer">
            <button mat-button (click)="cerrarModalMensaje()">Cancelar</button>
            <button mat-raised-button color="primary" 
                    (click)="enviarAccion()"
                    [disabled]="!mensaje.trim() || enviando">
              <mat-spinner diameter="20" *ngIf="enviando"></mat-spinner>
              <span *ngIf="!enviando">Enviar</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Historial Modal -->
      <app-historial-maquina
        *ngIf="mostrarHistorial"
        [idMaquina]="historialMaquinaId"
        [nombreMaquina]="historialMaquinaNombre"
        (onClose)="cerrarHistorial()">
      </app-historial-maquina>
    </div>
  `,
  styles: [`
    .dashboard-container {
      min-height: 100vh;
      background: linear-gradient(135deg, #07224c 0%, #124258 50%, #3b4a66 100%);
    }
    
    .dashboard-sections {
      max-width: 1200px;
      margin: 0 auto;
      padding: 2rem;
    }
    
    hr {
      margin: 1.5rem 0;
      border: none;
      height: 1px;
      background: rgba(255, 255, 255, 0.2);
    }
    
    .profile-section {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 12px;
      padding: 1.5rem;
      color: white;
    }
    
    .profile-section h2 {
      color: white;
      margin-bottom: 1rem;
    }
    
    .profile-info p {
      margin: 0.5rem 0;
      color: rgba(255, 255, 255, 0.9);
    }
    
    .notifications-section {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 12px;
      padding: 1rem;
    }
    
    .notifications-section h2 {
      cursor: pointer;
      color: white;
      margin: 0;
      padding: 0.5rem;
    }
    
    .notificaciones-list {
      list-style: none;
      padding: 0;
      margin-top: 1rem;
    }
    
    .notificacion-item {
      padding: 1rem;
      margin: 0.5rem 0;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 8px;
      cursor: pointer;
      transition: background 0.2s;
    }
    
    .notificacion-item:hover {
      background: rgba(255, 255, 255, 0.1);
    }
    
    .notificacion-item.no-leida {
      background: rgba(79, 107, 237, 0.2);
      border-left: 3px solid #4f6bed;
    }
    
    .notificacion-content {
      font-size: 0.9rem;
      color: rgba(255, 255, 255, 0.8);
    }
    
    .machines-section h2 {
      color: white;
      margin-bottom: 1rem;
    }
    
    .machine-list {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 12px;
      padding: 1rem;
      margin-bottom: 1rem;
    }
    
    .machine-list h3 {
      cursor: pointer;
      color: white;
      margin: 0;
      padding: 0.5rem;
    }
    
    .machine-list ul {
      list-style: none;
      padding: 0;
      margin-top: 0.5rem;
    }
    
    .li-maquina {
      padding: 1rem;
      margin: 0.5rem 0;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.2s;
      border-left: 3px solid transparent;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .li-maquina:hover {
      background: rgba(255, 255, 255, 0.1);
    }
    
    .li-maquina.selected {
      background: rgba(79, 107, 237, 0.3);
      border-left-color: #4f6bed;
    }
    
    .li-maquina-content {
      flex: 1;
    }
    
    .maquina-actions {
      display: flex;
      gap: 0.5rem;
    }
    
    .span-comercio-details {
      display: block;
      font-size: 0.85rem;
      color: rgba(255, 255, 255, 0.7);
      margin-top: 0.25rem;
    }
    
    .checklist-modal {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 12px;
      padding: 1.5rem;
      margin-top: 1rem;
    }
    
    .checklist-modal h3 {
      color: white;
      margin-bottom: 1rem;
    }
    
    .checklist-item {
      margin-bottom: 1rem;
      color: white;
    }
    
    .action-buttons {
      display: flex;
      gap: 1rem;
      margin-top: 1.5rem;
    }
    
    .loading-small {
      display: flex;
      justify-content: center;
      padding: 1rem;
    }
    
    .no-data {
      text-align: center;
      padding: 1rem;
      color: rgba(255, 255, 255, 0.7);
    }
    
    /* Modal Styles */
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.75);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }
    
    .modal-mensaje {
      background: white;
      border-radius: 12px;
      width: 90%;
      max-width: 500px;
      overflow: hidden;
    }
    
    .modal-header {
      padding: 1rem 1.5rem;
      background: linear-gradient(135deg, #4f6bed, #3d55c3);
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .modal-header h3 {
      margin: 0;
      color: white;
    }
    
    .modal-close {
      background: none;
      border: none;
      font-size: 1.5rem;
      cursor: pointer;
      color: white;
    }
    
    .modal-body {
      padding: 1.5rem;
    }
    
    .modal-footer {
      padding: 1rem 1.5rem;
      border-top: 1px solid #e0e0e0;
      display: flex;
      justify-content: flex-end;
      gap: 1rem;
    }
    
    .mensaje-textarea {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid #ddd;
      border-radius: 8px;
      resize: vertical;
      font-family: inherit;
    }
    
    @media (max-width: 768px) {
      .dashboard-sections {
        padding: 1rem;
      }
      
      .action-buttons {
        flex-direction: column;
      }
      
      .action-buttons button {
        width: 100%;
      }
      
      .li-maquina {
        flex-direction: column;
        gap: 0.5rem;
      }
    }
  `]
})
export class DashboardComprobadorComponent implements OnInit {
  private router = inject(Router);
  private tecnicoService = inject(TecnicoService);
  private authService = inject(AuthService);
  private notificationService = inject(NotificationService);
  private snackBar = inject(MatSnackBar);
  
  user: User | null = null;
  
  // Notificaciones
  notificaciones: any[] = [];
  notificacionesNoLeidas = 0;
  cargandoNotificaciones = false;
  mostrarNotificaciones = false;
  
  // Máquinas
  maquinasComprobando: Maquina[] = [];
  selectedMaquina: Maquina | null = null;
  
  // Estados de visualización
  mostrarComprobando = false;
  
  // Cargas
  cargandoComprobando = false;
  
  // Checklist
  checklist = {
    placaFuncional: false,
    carcasaBuenEstado: false,
    experienciaJuegoAcorde: false
  };
  
  // Modal
  mostrarModalMensaje = false;
  mensaje = '';
  enviando = false;
  accionActual: 'distribucion' | 'reensamblar' = 'distribucion';
  
  // Historial
  mostrarHistorial = false;
  historialMaquinaId = '';
  historialMaquinaNombre = '';
  
  ngOnInit(): void {
    this.user = this.authService.getCurrentUser();
    if (this.user?.ID_Usuario) {
      this.cargarNotificaciones();
      this.cargarMaquinas();
    }
  }
  
  private cargarNotificaciones(): void {
    this.cargandoNotificaciones = true;
    this.notificationService.getMaquinaNotifications(this.user!.ID_Usuario).subscribe({
      next: (notificaciones) => {
        this.notificaciones = notificaciones;
        this.notificacionesNoLeidas = notificaciones.filter(n => !n.leida).length;
        this.cargandoNotificaciones = false;
      },
      error: () => {
        this.cargandoNotificaciones = false;
      }
    });
  }
  
  private cargarMaquinas(): void {
    this.cargandoComprobando = true;
    this.tecnicoService.getMaquinasComprobador(this.user!.ID_Usuario).subscribe({
      next: (maquinas) => {
        this.maquinasComprobando = maquinas;
        this.cargandoComprobando = false;
      },
      error: () => {
        this.cargandoComprobando = false;
      }
    });
  }
  
  seleccionarMaquina(maquina: Maquina): void {
    this.selectedMaquina = this.selectedMaquina?.ID_Maquina === maquina.ID_Maquina ? null : maquina;
    this.resetChecklist();
  }
  
  private resetChecklist(): void {
    this.checklist = {
      placaFuncional: false,
      carcasaBuenEstado: false,
      experienciaJuegoAcorde: false
    };
  }
  
  allChecksPassed(): boolean {
    return this.checklist.placaFuncional && 
           this.checklist.carcasaBuenEstado && 
           this.checklist.experienciaJuegoAcorde;
  }
  
  abrirModalMensaje(accion: 'distribucion' | 'reensamblar'): void {
    if (!this.selectedMaquina) return;
    this.accionActual = accion;
    this.mensaje = '';
    this.mostrarModalMensaje = true;
  }
  
  cerrarModalMensaje(event?: MouseEvent): void {
    this.mostrarModalMensaje = false;
    this.mensaje = '';
  }
  
  enviarAccion(): void {
    if (!this.selectedMaquina || !this.mensaje.trim()) return;
    
    this.enviando = true;
    
    if (this.accionActual === 'distribucion') {
      this.tecnicoService.mandarADistribucion({
        idMaquina: this.selectedMaquina.ID_Maquina,
        idRemitente: this.user!.ID_Usuario,
        mensaje: this.mensaje
      }).subscribe({
        next: (success) => {
          if (success) {
            this.snackBar.open('Máquina enviada a distribución correctamente', 'Cerrar', { duration: 3000 });
            this.cargarMaquinas();
            this.selectedMaquina = null;
            this.cerrarModalMensaje();
          } else {
            this.snackBar.open('Error al enviar la máquina', 'Cerrar', { duration: 3000 });
          }
          this.enviando = false;
        },
        error: () => {
          this.snackBar.open('Error al enviar la máquina', 'Cerrar', { duration: 3000 });
          this.enviando = false;
        }
      });
    } else {
      this.tecnicoService.mandarAReensamblar({
        idMaquina: this.selectedMaquina.ID_Maquina,
        idRemitente: this.user!.ID_Usuario,
        mensaje: this.mensaje
      }).subscribe({
        next: (success) => {
          if (success) {
            this.snackBar.open('Máquina enviada a reensamblar correctamente', 'Cerrar', { duration: 3000 });
            this.cargarMaquinas();
            this.selectedMaquina = null;
            this.cerrarModalMensaje();
          } else {
            this.snackBar.open('Error al enviar la máquina', 'Cerrar', { duration: 3000 });
          }
          this.enviando = false;
        },
        error: () => {
          this.snackBar.open('Error al enviar la máquina', 'Cerrar', { duration: 3000 });
          this.enviando = false;
        }
      });
    }
  }
  
  verHistorial(maquina: Maquina): void {
    this.historialMaquinaId = maquina.ID_Maquina;
    this.historialMaquinaNombre = maquina.Nombre_Maquina;
    this.mostrarHistorial = true;
  }
  
  cerrarHistorial(): void {
    this.mostrarHistorial = false;
    this.historialMaquinaId = '';
    this.historialMaquinaNombre = '';
  }
  
  marcarNotificacionLeida(idNotificacion: string): void {
    this.notificationService.markAsRead(idNotificacion).subscribe({
      next: () => {
        this.cargarNotificaciones();
      },
      error: () => {}
    });
  }
}