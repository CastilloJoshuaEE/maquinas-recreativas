/**
 * @fileoverview Dashboard de Técnico de Mantenimiento
 * @description Panel para técnicos de mantenimiento para gestionar reparaciones
 * @component DashboardMantenimientoComponent
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header.component';
import { TecnicoService } from '../../services/tecnico.service';
import { AuthService } from '@core/services/auth.service';
import { NotificationService } from '@core/services/notification.service';
import { Maquina } from '@core/models/maquina.model';
import { User } from '@core/models/user.model';

@Component({
  selector: 'app-dashboard-mantenimiento',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    AdminHeaderComponent
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

        <!-- Máquinas en Mantenimiento -->
        <section class="machines-section">
          <h2>Máquinas Recreativas</h2>
          
          <div class="machine-list">
            <h3 (click)="mostrarMantenimiento = !mostrarMantenimiento">
              En Mantenimiento {{ mostrarMantenimiento ? '🔽' : '▶️' }}
            </h3>
            <div *ngIf="mostrarMantenimiento">
              <div *ngIf="cargandoMantenimiento" class="loading-small">
                <mat-spinner diameter="30"></mat-spinner>
              </div>
              <div *ngIf="!cargandoMantenimiento && maquinasMantenimiento.length === 0" class="no-data">
                <p>No hay máquinas para dar mantenimiento...</p>
              </div>
              <ul *ngIf="!cargandoMantenimiento && maquinasMantenimiento.length > 0">
                <li *ngFor="let maquina of maquinasMantenimiento"
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
                    <span class="span-comercio-details" *ngIf="maquina.mensaje_mantenimiento">
                      <strong>Mensaje:</strong> {{ maquina.mensaje_mantenimiento }}
                    </span>
                  </div>
                </li>
              </ul>
              <div class="action-buttons" *ngIf="maquinasMantenimiento.length > 0 && selectedMaquina">
                <button mat-raised-button color="success" 
                        (click)="abrirModalMensaje(true)"
                        [disabled]="!selectedMaquina">
                  Dar de alta ✅
                </button>
                <button mat-raised-button color="warn" 
                        (click)="abrirModalMensaje(false)"
                        [disabled]="!selectedMaquina">
                  Dar de baja ❌
                </button>
              </div>
            </div>
          </div>
        </section>

        <!-- Acciones -->
        <section class="actions-section">
          <h2>Acciones</h2>
          <div class="action-buttons">
            <button mat-raised-button (click)="irAInformesDistribucion()">
              <mat-icon>description</mat-icon>
              Consultar Informes de Distribución
            </button>
          </div>
        </section>
      </div>

      <!-- Modal de Mensaje -->
      <div class="modal-overlay" *ngIf="mostrarModalMensaje" (click)="cerrarModalMensaje($event)">
        <div class="modal-mensaje" (click)="$event.stopPropagation()">
          <div class="modal-header">
            <h3>Mensaje para logística</h3>
            <button class="modal-close" (click)="cerrarModalMensaje()">×</button>
          </div>
          <div class="modal-body">
            <textarea 
              [(ngModel)]="mensaje"
              placeholder="Describe el resultado del mantenimiento..."
              rows="4"
              class="mensaje-textarea"></textarea>
          </div>
          <div class="modal-footer">
            <button mat-button (click)="cerrarModalMensaje()">Cancelar</button>
            <button mat-raised-button color="primary" 
                    (click)="finalizarMantenimiento()"
                    [disabled]="!mensaje.trim() || enviando">
              <mat-spinner diameter="20" *ngIf="enviando"></mat-spinner>
              <span *ngIf="!enviando">Enviar</span>
            </button>
          </div>
        </div>
      </div>
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
    }
    
    .li-maquina:hover {
      background: rgba(255, 255, 255, 0.1);
    }
    
    .li-maquina.selected {
      background: rgba(79, 107, 237, 0.3);
      border-left-color: #4f6bed;
    }
    
    .li-maquina-content {
      display: block;
    }
    
    .span-comercio-details {
      display: block;
      font-size: 0.85rem;
      color: rgba(255, 255, 255, 0.7);
      margin-top: 0.25rem;
    }
    
    .actions-section {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 12px;
      padding: 1.5rem;
    }
    
    .actions-section h2 {
      color: white;
      margin-bottom: 1rem;
    }
    
    .action-buttons {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
    }
    
    button[color="success"] {
      background-color: #28a745;
      color: white;
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
    }
  `]
})
export class DashboardMantenimientoComponent implements OnInit {
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
  maquinasMantenimiento: Maquina[] = [];
  selectedMaquina: Maquina | null = null;
  
  // Estados de visualización
  mostrarMantenimiento = false;
  
  // Cargas
  cargandoMantenimiento = false;
  
  // Modal
  mostrarModalMensaje = false;
  mensaje = '';
  enviando = false;
  exitoMantenimiento = true;
  
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
    this.cargandoMantenimiento = true;
    this.tecnicoService.getMaquinasMantenimiento(this.user!.ID_Usuario).subscribe({
      next: (maquinas) => {
        this.maquinasMantenimiento = maquinas;
        this.cargandoMantenimiento = false;
      },
      error: () => {
        this.cargandoMantenimiento = false;
      }
    });
  }
  
  seleccionarMaquina(maquina: Maquina): void {
    this.selectedMaquina = this.selectedMaquina?.ID_Maquina === maquina.ID_Maquina ? null : maquina;
  }
  
  abrirModalMensaje(exito: boolean): void {
    if (!this.selectedMaquina) return;
    this.exitoMantenimiento = exito;
    this.mensaje = '';
    this.mostrarModalMensaje = true;
  }
  
  cerrarModalMensaje(event?: MouseEvent): void {
    this.mostrarModalMensaje = false;
    this.mensaje = '';
  }
  
  finalizarMantenimiento(): void {
    if (!this.selectedMaquina || !this.mensaje.trim()) return;
    
    this.enviando = true;
    
    this.tecnicoService.finalizarMantenimiento({
      idMaquina: this.selectedMaquina.ID_Maquina,
      idRemitente: this.user!.ID_Usuario,
      exito: this.exitoMantenimiento,
      mensaje: this.mensaje
    }).subscribe({
      next: (success) => {
        if (success) {
          const mensajeExito = this.exitoMantenimiento 
            ? 'Mantenimiento finalizado correctamente. Máquina operativa.'
            : 'Mantenimiento finalizado. Máquina enviada a reensamblar.';
          this.snackBar.open(mensajeExito, 'Cerrar', { duration: 3000 });
          this.cargarMaquinas();
          this.selectedMaquina = null;
          this.cerrarModalMensaje();
        } else {
          this.snackBar.open('Error al finalizar el mantenimiento', 'Cerrar', { duration: 3000 });
        }
        this.enviando = false;
      },
      error: () => {
        this.snackBar.open('Error al finalizar el mantenimiento', 'Cerrar', { duration: 3000 });
        this.enviando = false;
      }
    });
  }
  
  marcarNotificacionLeida(idNotificacion: string): void {
    this.notificationService.markAsRead(idNotificacion).subscribe({
      next: () => {
        this.cargarNotificaciones();
      },
      error: () => {}
    });
  }
  
  irAInformesDistribucion(): void {
    this.router.navigate(['/logistica/consultar-informe-distribucion']);
  }
}