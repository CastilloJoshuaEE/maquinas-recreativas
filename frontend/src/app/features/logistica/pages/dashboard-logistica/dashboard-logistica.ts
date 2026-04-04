/**
 * @fileoverview Dashboard de Logística
 * @description Panel principal del módulo de logística con gestión de máquinas
 * @component DashboardLogisticaComponent
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header.component';
import { LogisticaService } from '../../services/logistica.service';
import { AuthService } from '@core/services/auth.service';
import { NotificationService } from '@core/services/notification.service';
import { Maquina } from '@core/models/maquina.model';
import { User } from '@core/models/user.model';
import { ComercioFormComponent } from '../ui/comercio-form/comercio-form.component';
import { MaquinaFormComponent } from '../ui/maquina-form/maquina-form.component';

@Component({
  selector: 'app-dashboard-logistica',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    AdminHeaderComponent,
    ComercioFormComponent,
    MaquinaFormComponent
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
                  <span class="notificacion-content" *ngIf="notif.NombreComercio">
                    <strong>Comercio:</strong> {{ notif.NombreComercio }}
                  </span>
                </p>
              </li>
            </ul>
          </div>
        </section>

        <hr />

        <!-- Máquinas en Distribución -->
        <section class="machines-section">
          <h2>Máquinas Recreativas</h2>
          
          <!-- En Distribución -->
          <div class="machine-list">
            <h3 (click)="mostrarDistribucion = !mostrarDistribucion">
              En Distribución {{ mostrarDistribucion ? '🔽' : '▶️' }}
            </h3>
            <div *ngIf="mostrarDistribucion">
              <div *ngIf="cargandoDistribucion" class="loading-small">
                <mat-spinner diameter="30"></mat-spinner>
              </div>
              <div *ngIf="!cargandoDistribucion && maquinasDistribucion.length === 0" class="no-data">
                <p>No hay máquinas para distribuir...</p>
              </div>
              <ul *ngIf="!cargandoDistribucion && maquinasDistribucion.length > 0">
                <li *ngFor="let maquina of maquinasDistribucion"
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
                </li>
              </ul>
              <div class="action-buttons" *ngIf="maquinasDistribucion.length > 0">
                <button mat-raised-button color="primary" 
                        (click)="ponerOperativa()"
                        [disabled]="!selectedMaquina || !esMaquinaDistribucion()">
                  Poner operativa ✅
                </button>
              </div>
            </div>
          </div>

          <!-- Máquinas Operativas -->
          <div class="machine-list">
            <h3 (click)="mostrarOperativas = !mostrarOperativas">
              Operativas {{ mostrarOperativas ? '🔽' : '▶️' }}
            </h3>
            <div *ngIf="mostrarOperativas">
              <div *ngIf="cargandoOperativas" class="loading-small">
                <mat-spinner diameter="30"></mat-spinner>
              </div>
              <div *ngIf="!cargandoOperativas && maquinasOperativas.length === 0" class="no-data">
                <p>No hay máquinas operativas...</p>
              </div>
              <ul *ngIf="!cargandoOperativas && maquinasOperativas.length > 0">
                <li *ngFor="let maquina of maquinasOperativas"
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
                </li>
              </ul>
              <div class="action-buttons" *ngIf="maquinasOperativas.length > 0">
                <button mat-raised-button color="warn" 
                        (click)="abrirModalMantenimiento()"
                        [disabled]="!selectedMaquina || !esMaquinaOperativa()">
                  Llevar a mantenimiento ⚙️
                </button>
              </div>
            </div>
          </div>

          <!-- Máquinas Retiradas -->
          <div class="machine-list">
            <h3 (click)="mostrarRetiradas = !mostrarRetiradas">
              Retiradas {{ mostrarRetiradas ? '🔽' : '▶️' }}
            </h3>
            <div *ngIf="mostrarRetiradas">
              <div *ngIf="cargandoRetiradas" class="loading-small">
                <mat-spinner diameter="30"></mat-spinner>
              </div>
              <div *ngIf="!cargandoRetiradas && maquinasRetiradas.length === 0" class="no-data">
                <p>No hay máquinas retiradas...</p>
              </div>
              <ul *ngIf="!cargandoRetiradas && maquinasRetiradas.length > 0">
                <li *ngFor="let maquina of maquinasRetiradas" class="li-maquina retirada">
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
                </li>
              </ul>
            </div>
          </div>
        </section>

        <hr />

        <!-- Acciones -->
        <section class="actions-section">
          <h2>Acciones</h2>
          <div class="action-buttons">
            <button mat-raised-button color="primary" (click)="abrirFormularioComercio()">
              <mat-icon>store</mat-icon>
              Nuevo comercio
            </button>
            <button mat-raised-button color="accent" (click)="abrirFormularioMaquina()">
              <mat-icon>videogame_asset</mat-icon>
              Nueva máquina
            </button>
            <button mat-raised-button (click)="irAInformesDistribucion()">
              <mat-icon>description</mat-icon>
              Consultar Informes de Distribución
            </button>
          </div>
        </section>
      </div>

      <!-- Modal de Mensaje para Mantenimiento -->
      <div class="modal-overlay" *ngIf="mostrarMensajeMantenimiento" (click)="cerrarModalMantenimiento($event)">
        <div class="modal-mensaje" (click)="$event.stopPropagation()">
          <div class="modal-header">
            <h3>Mensaje para el técnico de mantenimiento</h3>
            <button class="modal-close" (click)="cerrarModalMantenimiento()">×</button>
          </div>
          <div class="modal-body">
            <div *ngIf="errorMantenimiento" class="error-message">
              {{ errorMantenimiento }}
            </div>
            <textarea 
              [(ngModel)]="mensajeMantenimiento"
              placeholder="Describe el problema..."
              rows="4"
              class="mensaje-textarea"></textarea>
          </div>
          <div class="modal-footer">
            <button mat-button (click)="cerrarModalMantenimiento()">Cancelar</button>
            <button mat-raised-button color="primary" 
                    (click)="enviarMantenimiento()"
                    [disabled]="!mensajeMantenimiento.trim() || enviandoMantenimiento">
              <mat-spinner diameter="20" *ngIf="enviandoMantenimiento"></mat-spinner>
              <span *ngIf="!enviandoMantenimiento">Enviar</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Formulario de Comercio -->
      <app-comercio-form 
        *ngIf="mostrarComercioForm"
        (onClose)="cerrarFormularioComercio()"
        (onSuccess)="onComercioRegistrado()">
      </app-comercio-form>

      <!-- Formulario de Máquina -->
      <app-maquina-form 
        *ngIf="mostrarMaquinaForm"
        (onClose)="cerrarFormularioMaquina()"
        (onSuccess)="onMaquinaRegistrada()">
      </app-maquina-form>
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
    
    .li-maquina.retirada {
      text-decoration: line-through;
      opacity: 0.7;
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
    
    .error-message {
      padding: 0.75rem;
      background: #f8d7da;
      color: #721c24;
      border-radius: 8px;
      margin-bottom: 1rem;
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
export class DashboardLogisticaComponent implements OnInit {
  private router = inject(Router);
  private logisticaService = inject(LogisticaService);
  private authService = inject(AuthService);
  private notificationService = inject(NotificationService);
  private snackBar = inject(MatSnackBar);
  
  // Usuario
  user: User | null = null;
  
  // Notificaciones
  notificaciones: any[] = [];
  notificacionesNoLeidas = 0;
  cargandoNotificaciones = false;
  mostrarNotificaciones = false;
  
  // Máquinas
  maquinasDistribucion: Maquina[] = [];
  maquinasOperativas: Maquina[] = [];
  maquinasRetiradas: Maquina[] = [];
  selectedMaquina: Maquina | null = null;
  
  // Estados de visualización
  mostrarDistribucion = false;
  mostrarOperativas = false;
  mostrarRetiradas = false;
  
  // Cargas
  cargandoDistribucion = false;
  cargandoOperativas = false;
  cargandoRetiradas = false;
  
  // Mantenimiento
  mostrarMensajeMantenimiento = false;
  mensajeMantenimiento = '';
  enviandoMantenimiento = false;
  errorMantenimiento = '';
  
  // Formularios
  mostrarComercioForm = false;
  mostrarMaquinaForm = false;
  
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
    this.cargarMaquinasDistribucion();
    this.cargarMaquinasOperativas();
    this.cargarMaquinasRetiradas();
  }
  
  private cargarMaquinasDistribucion(): void {
    this.cargandoDistribucion = true;
    this.logisticaService.getMaquinasDistribucion().subscribe({
      next: (maquinas) => {
        this.maquinasDistribucion = maquinas;
        this.cargandoDistribucion = false;
      },
      error: () => {
        this.cargandoDistribucion = false;
      }
    });
  }
  
  private cargarMaquinasOperativas(): void {
    this.cargandoOperativas = true;
    this.logisticaService.getMaquinasOperativas().subscribe({
      next: (maquinas) => {
        this.maquinasOperativas = maquinas;
        this.cargandoOperativas = false;
      },
      error: () => {
        this.cargandoOperativas = false;
      }
    });
  }
  
  private cargarMaquinasRetiradas(): void {
    this.cargandoRetiradas = true;
    this.logisticaService.getMaquinasRetiradas().subscribe({
      next: (maquinas) => {
        this.maquinasRetiradas = maquinas;
        this.cargandoRetiradas = false;
      },
      error: () => {
        this.cargandoRetiradas = false;
      }
    });
  }
  
  seleccionarMaquina(maquina: Maquina): void {
    this.selectedMaquina = this.selectedMaquina?.ID_Maquina === maquina.ID_Maquina ? null : maquina;
  }
  
  esMaquinaDistribucion(): boolean {
    return this.selectedMaquina ? this.maquinasDistribucion.some(m => m.ID_Maquina === this.selectedMaquina!.ID_Maquina) : false;
  }
  
  esMaquinaOperativa(): boolean {
    return this.selectedMaquina ? this.maquinasOperativas.some(m => m.ID_Maquina === this.selectedMaquina!.ID_Maquina) : false;
  }
  
  ponerOperativa(): void {
    if (!this.selectedMaquina) return;
    
    if (!confirm(`¿Está seguro de poner operativa la máquina ${this.selectedMaquina.Nombre_Maquina}?`)) return;
    
    this.logisticaService.ponerMaquinaOperativa(this.selectedMaquina.ID_Maquina).subscribe({
      next: (success) => {
        if (success) {
          this.snackBar.open('Máquina puesta operativa correctamente', 'Cerrar', { duration: 3000 });
          this.cargarMaquinas();
          this.selectedMaquina = null;
        } else {
          this.snackBar.open('Error al poner operativa la máquina', 'Cerrar', { duration: 3000 });
        }
      },
      error: () => {
        this.snackBar.open('Error al poner operativa la máquina', 'Cerrar', { duration: 3000 });
      }
    });
  }
  
  abrirModalMantenimiento(): void {
    if (!this.selectedMaquina) return;
    this.mensajeMantenimiento = '';
    this.errorMantenimiento = '';
    this.mostrarMensajeMantenimiento = true;
  }
  
  cerrarModalMantenimiento(event?: MouseEvent): void {
    this.mostrarMensajeMantenimiento = false;
    this.mensajeMantenimiento = '';
    this.errorMantenimiento = '';
  }
  
  enviarMantenimiento(): void {
    if (!this.selectedMaquina || !this.mensajeMantenimiento.trim()) return;
    
    this.enviandoMantenimiento = true;
    this.errorMantenimiento = '';
    
    this.logisticaService.solicitarMantenimiento({
      idMaquina: this.selectedMaquina.ID_Maquina,
      mensaje: this.mensajeMantenimiento,
      idLogistica: this.user!.ID_Usuario
    }).subscribe({
      next: (success) => {
        if (success) {
          this.snackBar.open('Solicitud de mantenimiento enviada correctamente', 'Cerrar', { duration: 3000 });
          this.cargarMaquinas();
          this.selectedMaquina = null;
          this.cerrarModalMantenimiento();
        } else {
          this.errorMantenimiento = 'No hay técnicos de mantenimiento disponibles';
        }
        this.enviandoMantenimiento = false;
      },
      error: () => {
        this.errorMantenimiento = 'No hay técnicos de mantenimiento disponibles';
        this.enviandoMantenimiento = false;
      }
    });
  }
  
  marcarNotificacionLeida(idNotificacion: string): void {
    this.notificationService.markAsRead(idNotificacion).subscribe({
      next: () => {
        this.cargarNotificaciones();
      },
      error: () => {
        // Error silencioso
      }
    });
  }
  
  abrirFormularioComercio(): void {
    this.mostrarComercioForm = true;
  }
  
  cerrarFormularioComercio(): void {
    this.mostrarComercioForm = false;
  }
  
  onComercioRegistrado(): void {
    this.cerrarFormularioComercio();
    this.snackBar.open('Comercio registrado correctamente', 'Cerrar', { duration: 3000 });
  }
  
  abrirFormularioMaquina(): void {
    this.mostrarMaquinaForm = true;
  }
  
  cerrarFormularioMaquina(): void {
    this.mostrarMaquinaForm = false;
  }
  
  onMaquinaRegistrada(): void {
    this.cerrarFormularioMaquina();
    this.cargarMaquinas();
    this.snackBar.open('Máquina registrada correctamente', 'Cerrar', { duration: 3000 });
  }
  
  irAInformesDistribucion(): void {
    this.router.navigate(['/logistica/consultar-informe-distribucion']);
  }
}