/**
 * @fileoverview Gestión de Reportes
 * @description Página para crear y gestionar reportes del sistema
 * @component GestionReportesComponent
 */

import { Component, OnInit, OnDestroy, inject } from '@angular/core';
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
import { MatTabsModule } from '@angular/material/tabs';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header.component';
import { ReportesService } from '../../services/reportes.service';
import { AuthService } from '@core/services/auth.service';
import { User } from '@core/models/user.model';
import { Reporte } from '@core/models/reporte.model';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-gestion-reportes',
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
    MatTabsModule,
    AdminHeaderComponent
  ],
  template: `
    <div class="gestion-reportes-container">
      <app-admin-header></app-admin-header>
      
      <div class="content-wrapper">
        <div class="page-header">
          <button mat-icon-button (click)="regresar()" class="back-button" *ngIf="modoAdmin">
            <mat-icon>arrow_back</mat-icon>
          </button>
          <h2>{{ esUsuarioInhabilitado ? 'Solicitud de Reactivación' : 'Gestión de Reportes' }}</h2>
        </div>

        <!-- Mensaje de estado -->
        <div *ngIf="statusMessage" class="status-message" [class.error]="isError">
          {{ statusMessage }}
        </div>

        <!-- Formulario de nuevo reporte -->
        <mat-card class="nuevo-reporte-card">
          <mat-card-header>
            <mat-card-title>
              <mat-icon>{{ esUsuarioInhabilitado ? 'warning' : 'add_circle' }}</mat-icon>
              {{ esUsuarioInhabilitado ? 'Solicitar Reactivación de Cuenta' : 'Crear Nuevo Reporte' }}
            </mat-card-title>
          </mat-card-header>
          
          <mat-card-content>
            <form (ngSubmit)="onSubmit()">
              <!-- Selección de área (solo para usuarios normales) -->
              <mat-form-field appearance="outline" class="full-width" *ngIf="!esUsuarioInhabilitado && !modoAdmin">
                <mat-label>Área del destinatario</mat-label>
                <mat-select [(ngModel)]="nuevoReporte.tipoDestinatario" name="tipoDestinatario" required (selectionChange)="cargarUsuariosPorTipo()">
                  <mat-option value="">Seleccionar área</mat-option>
                  <mat-option *ngFor="let tipo of tiposUsuario" [value]="tipo">{{ tipo }}</mat-option>
                </mat-select>
                <mat-icon matPrefix>business</mat-icon>
              </mat-form-field>

              <!-- Selección de destinatario -->
              <mat-form-field appearance="outline" class="full-width" *ngIf="!esUsuarioInhabilitado && !modoAdmin">
                <mat-label>Destinatario</mat-label>
                <mat-select [(ngModel)]="nuevoReporte.destinatario" name="destinatario" required [disabled]="!nuevoReporte.tipoDestinatario">
                  <mat-option value="">Seleccionar destinatario</mat-option>
                  <mat-option *ngFor="let user of usuariosDisponibles" [value]="user.ID_Usuario">
                    {{ user.nombre }} {{ user.apellido }} ({{ user.email }})
                  </mat-option>
                </mat-select>
                <mat-icon matPrefix>person</mat-icon>
              </mat-form-field>

              <!-- Selección de administrador (modo usuario inhabilitado) -->
              <mat-form-field appearance="outline" class="full-width" *ngIf="esUsuarioInhabilitado">
                <mat-label>Administrador</mat-label>
                <mat-select [(ngModel)]="nuevoReporte.destinatario" name="destinatario" required>
                  <mat-option value="">Seleccionar administrador</mat-option>
                  <mat-option *ngFor="let admin of administradores" [value]="admin.ID_Usuario">
                    {{ admin.nombre }} {{ admin.apellido }} ({{ admin.email }})
                  </mat-option>
                </mat-select>
                <mat-icon matPrefix>admin_panel_settings</mat-icon>
              </mat-form-field>

              <!-- Descripción -->
              <mat-form-field appearance="outline" class="full-width">
                <mat-label>{{ esUsuarioInhabilitado ? 'Explicación para reactivación' : 'Descripción' }}</mat-label>
                <textarea matInput 
                          [(ngModel)]="nuevoReporte.descripcion" 
                          name="descripcion" 
                          rows="4"
                          [placeholder]="esUsuarioInhabilitado ? 'Por favor explique por qué desea reactivar su cuenta...' : 'Describe tu situación...'"
                          required></textarea>
                <mat-icon matPrefix>description</mat-icon>
              </mat-form-field>

              <div class="form-actions">
                <button mat-raised-button color="primary" type="submit" [disabled]="enviando">
                  <mat-spinner diameter="20" *ngIf="enviando"></mat-spinner>
                  <span *ngIf="!enviando">{{ esUsuarioInhabilitado ? 'Enviar Solicitud' : 'Enviar Reporte' }}</span>
                </button>
                <button mat-button type="button" (click)="regresar()" *ngIf="!modoAdmin">Volver al inicio</button>
              </div>
            </form>
          </mat-card-content>
        </mat-card>

        <!-- Lista de reportes (solo para usuarios normales) -->
        <mat-card class="reportes-list-card" *ngIf="!esUsuarioInhabilitado && !modoAdmin">
          <mat-card-header>
            <mat-card-title>
              <mat-icon>list_alt</mat-icon>
              Mis Reportes
            </mat-card-title>
            <div class="filtros-reportes">
              <mat-form-field appearance="outline">
                <mat-label>Filtrar por estado</mat-label>
                <mat-select [(ngModel)]="filtroEstado" (selectionChange)="filtrarReportes()">
                  <mat-option value="todos">Todos</mat-option>
                  <mat-option value="Pendiente">Pendientes</mat-option>
                  <mat-option value="En proceso">En proceso</mat-option>
                  <mat-option value="Resuelto">Resueltos</mat-option>
                </mat-select>
              </mat-form-field>
            </div>
          </mat-card-header>
          
          <mat-card-content>
            <div *ngIf="cargandoReportes" class="loading-container">
              <mat-spinner diameter="40"></mat-spinner>
            </div>
            
            <div *ngIf="!cargandoReportes && reportesFiltrados.length === 0" class="no-data">
              <mat-icon>inbox</mat-icon>
              <p>No hay reportes</p>
            </div>
            
            <div *ngIf="!cargandoReportes && reportesFiltrados.length > 0" class="reportes-table">
              <table class="tabla-reportes">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Emisor</th>
                    <th>Destinatario</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr *ngFor="let reporte of reportesFiltrados">
                    <td>{{ reporte.ID_Reporte | slice:0:8 }}...</td>
                    <td>{{ reporte.fecha_hora | date:'dd/MM/yyyy HH:mm' }}</td>
                    <td class="descripcion-cell">{{ reporte.descripcion }}</td>
                    <td>
                      <span class="estado-badge" [ngClass]="{
                        'estado-pendiente': reporte.estado === 'Pendiente',
                        'estado-proceso': reporte.estado === 'En proceso',
                        'estado-resuelto': reporte.estado === 'Resuelto'
                      }">
                        {{ reporte.estado }}
                      </span>
                    </td>
                    <td>{{ reporte.emisor_nombre }} {{ reporte.emisor_apellido }}</td>
                    <td>{{ reporte.destinatario_nombre }} {{ reporte.destinatario_apellido }}</td>
                    <td>
                      <button mat-icon-button color="primary" (click)="verChat(reporte)" matTooltip="Ver Chat">
                        <mat-icon>chat</mat-icon>
                      </button>
                      <mat-select *ngIf="puedeCambiarEstado(reporte)" 
                                  [(ngModel)]="reporte.estado" 
                                  (selectionChange)="cambiarEstado(reporte)"
                                  class="estado-select">
                        <mat-option value="Pendiente">Pendiente</mat-option>
                        <mat-option value="En proceso">En proceso</mat-option>
                        <mat-option value="Resuelto">Resuelto</mat-option>
                      </mat-select>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </mat-card-content>
        </mat-card>
      </div>
    </div>
  `,
  styles: [`
    .gestion-reportes-container {
      min-height: 100vh;
      background: linear-gradient(135deg, #07224c 0%, #124258 50%, #3b4a66 100%);
    }
    
    .content-wrapper {
      max-width: 1200px;
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
    
    .status-message {
      padding: 1rem;
      background: #fff3cd;
      color: #856404;
      border-radius: 8px;
      margin-bottom: 1rem;
      text-align: center;
    }
    
    .status-message.error {
      background: #f8d7da;
      color: #721c24;
    }
    
    .nuevo-reporte-card,
    .reportes-list-card {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 12px;
      margin-bottom: 1.5rem;
    }
    
    .nuevo-reporte-card mat-card-title,
    .reportes-list-card mat-card-title {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 1.2rem;
      color: #2c3e50;
    }
    
    .full-width {
      width: 100%;
      margin-bottom: 1rem;
    }
    
    .form-actions {
      display: flex;
      gap: 1rem;
      justify-content: flex-end;
      margin-top: 1rem;
    }
    
    .filtros-reportes {
      margin-top: 1rem;
    }
    
    .filtros-reportes mat-form-field {
      width: 200px;
    }
    
    .reportes-table {
      overflow-x: auto;
    }
    
    .tabla-reportes {
      width: 100%;
      border-collapse: collapse;
    }
    
    .tabla-reportes th,
    .tabla-reportes td {
      padding: 0.75rem;
      text-align: left;
      border-bottom: 1px solid #e0e0e0;
    }
    
    .tabla-reportes th {
      background: #f5f5f5;
      font-weight: 600;
    }
    
    .descripcion-cell {
      max-width: 300px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    
    .estado-badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
    }
    
    .estado-pendiente {
      background: #fff3cd;
      color: #856404;
    }
    
    .estado-proceso {
      background: #cce5ff;
      color: #004085;
    }
    
    .estado-resuelto {
      background: #d4edda;
      color: #155724;
    }
    
    .estado-select {
      width: 120px;
      margin-left: 0.5rem;
    }
    
    .loading-container {
      display: flex;
      justify-content: center;
      padding: 2rem;
    }
    
    .no-data {
      text-align: center;
      padding: 2rem;
      color: #999;
    }
    
    .no-data mat-icon {
      font-size: 3rem;
      width: auto;
      height: auto;
      margin-bottom: 0.5rem;
    }
    
    @media (max-width: 768px) {
      .content-wrapper {
        padding: 1rem;
      }
      
      .form-actions {
        flex-direction: column;
      }
      
      .form-actions button {
        width: 100%;
      }
      
      .tabla-reportes {
        font-size: 0.8rem;
      }
      
      .descripcion-cell {
        max-width: 150px;
      }
    }
  `]
})
export class GestionReportesComponent implements OnInit, OnDestroy {
  private router = inject(Router);
  private route = inject(ActivatedRoute);
  private reportesService = inject(ReportesService);
  private authService = inject(AuthService);
  private snackBar = inject(MatSnackBar);
  
  currentUser: User | null = null;
  modoAdmin = false;
  esUsuarioInhabilitado = false;
  
  // Nuevo reporte
  nuevoReporte = {
    tipoDestinatario: '',
    destinatario: '',
    descripcion: ''
  };
  
  tiposUsuario = ['Tecnico', 'Contabilidad', 'Logistica', 'Administrador'];
  usuariosDisponibles: User[] = [];
  administradores: User[] = [];
  
  // Reportes
  reportes: Reporte[] = [];
  reportesFiltrados: Reporte[] = [];
  filtroEstado = 'todos';
  
  // Estados
  enviando = false;
  cargandoReportes = false;
  statusMessage = '';
  isError = false;
  
  private subscriptions: Subscription[] = [];
  
  ngOnInit(): void {
    this.currentUser = this.authService.getCurrentUser();
    this.cargarEstadoInicial();
    
    if (this.currentUser?.ID_Usuario) {
      if (!this.esUsuarioInhabilitado && !this.modoAdmin) {
        this.cargarReportes();
      } else if (this.esUsuarioInhabilitado) {
        this.cargarAdministradores();
      }
    }
  }
  
  ngOnDestroy(): void {
    this.subscriptions.forEach(sub => sub.unsubscribe());
  }
  
  private cargarEstadoInicial(): void {
    const navigation = this.router.getCurrentNavigation();
    const state = navigation?.extras.state as any;
    
    if (state) {
      if (state.isDisabledUser) {
        this.esUsuarioInhabilitado = true;
        this.statusMessage = 'Su cuenta está inhabilitada. Por favor contacte al administrador.';
      }
      if (state.message) {
        this.statusMessage = state.message;
      }
      if (state.userData) {
        this.currentUser = state.userData;
      }
    }
    
    // Verificar si viene de acceso restringido
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('modo') === 'admin') {
      this.modoAdmin = true;
    }
  }
  
  private cargarAdministradores(): void {
    this.reportesService.getUsuariosChat(this.currentUser?.ID_Usuario || '').subscribe({
      next: (usuarios) => {
        this.administradores = usuarios.filter(u => u.tipo === 'Administrador');
        if (this.administradores.length > 0) {
          this.nuevoReporte.destinatario = this.administradores[0].ID_Usuario;
        }
      },
      error: () => {
        this.snackBar.error('Error al cargar administradores', 'Cerrar');
      }
    });
  }
  
  cargarUsuariosPorTipo(): void {
    if (!this.nuevoReporte.tipoDestinatario) {
      this.usuariosDisponibles = [];
      return;
    }
    
    this.reportesService.getUsuariosChat(this.currentUser!.ID_Usuario).subscribe({
      next: (usuarios) => {
        this.usuariosDisponibles = usuarios.filter(u => u.tipo === this.nuevoReporte.tipoDestinatario);
      },
      error: () => {
        this.snackBar.error('Error al cargar usuarios', 'Cerrar');
      }
    });
  }
  
  cargarReportes(): void {
    this.cargandoReportes = true;
    this.reportesService.getReportesByUser(this.currentUser!.ID_Usuario).subscribe({
      next: (reportes) => {
        this.reportes = reportes;
        this.filtrarReportes();
        this.cargandoReportes = false;
      },
      error: () => {
        this.cargandoReportes = false;
        this.snackBar.error('Error al cargar reportes', 'Cerrar');
      }
    });
  }
  
  filtrarReportes(): void {
    if (this.filtroEstado === 'todos') {
      this.reportesFiltrados = [...this.reportes];
    } else {
      this.reportesFiltrados = this.reportes.filter(r => r.estado === this.filtroEstado);
    }
  }
  
  onSubmit(): void {
    if (!this.nuevoReporte.destinatario || !this.nuevoReporte.descripcion) {
      this.snackBar.warning('Complete todos los campos', 'Cerrar');
      return;
    }
    
    if (!confirm('¿Está seguro de enviar este reporte?')) return;
    
    this.enviando = true;
    
    let descripcion = this.nuevoReporte.descripcion;
    if (this.esUsuarioInhabilitado) {
      descripcion = `[SOLICITUD DE REACTIVACIÓN] ${descripcion}`;
    } else if (this.modoAdmin) {
      descripcion = `[USUARIO RESTRINGIDO] ${descripcion}`;
    }
    
    this.reportesService.crearReporte({
      ID_Usuario_Emisor: this.currentUser!.ID_Usuario,
      ID_Usuario_Destinatario: this.nuevoReporte.destinatario,
      descripcion: descripcion
    }).subscribe({
      next: (reporteId) => {
        if (reporteId) {
          this.snackBar.success('Reporte enviado correctamente', 'Éxito');
          this.nuevoReporte = { tipoDestinatario: '', destinatario: '', descripcion: '' };
          
          if (!this.esUsuarioInhabilitado && !this.modoAdmin) {
            this.cargarReportes();
          } else if (this.esUsuarioInhabilitado) {
            setTimeout(() => this.router.navigate(['/']), 2000);
          }
        } else {
          this.snackBar.error('Error al enviar el reporte', 'Error');
        }
        this.enviando = false;
      },
      error: () => {
        this.snackBar.error('Error al enviar el reporte', 'Error');
        this.enviando = false;
      }
    });
  }
  
  cambiarEstado(reporte: Reporte): void {
    this.reportesService.actualizarEstado(reporte.ID_Reporte, reporte.estado).subscribe({
      next: (success) => {
        if (success) {
          this.snackBar.success('Estado actualizado correctamente', 'Éxito');
        } else {
          this.snackBar.error('Error al actualizar estado', 'Error');
          this.cargarReportes();
        }
      },
      error: () => {
        this.snackBar.error('Error al actualizar estado', 'Error');
        this.cargarReportes();
      }
    });
  }
  
  puedeCambiarEstado(reporte: Reporte): boolean {
    const esEmisor = reporte.ID_Usuario_Emisor === this.currentUser?.ID_Usuario;
    const esDestinatario = reporte.ID_Usuario_Destinatario === this.currentUser?.ID_Usuario;
    return esEmisor || esDestinatario;
  }
  
  verChat(reporte: Reporte): void {
    this.router.navigate(['/reportes/chat', reporte.ID_Reporte]);
  }
  
  regresar(): void {
    if (this.modoAdmin || this.esUsuarioInhabilitado) {
      this.router.navigate(['/']);
    } else {
      this.router.navigate(['/']);
    }
  }
}