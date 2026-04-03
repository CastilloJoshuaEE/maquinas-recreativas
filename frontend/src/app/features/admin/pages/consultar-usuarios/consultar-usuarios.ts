/**
 * @fileoverview Componente de Consulta de Usuarios
 * @description Permite consultar, filtrar, editar y eliminar usuarios del sistema
 * @component ConsultarUsuariosComponent
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { MatTableModule } from '@angular/material/table';
import { MatPaginatorModule, PageEvent } from '@angular/material/paginator';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatDialogModule, MatDialog } from '@angular/material/dialog';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header.component';
import { ApiService } from '@core/services/api.service';
import { UserService } from '@core/services/user.service';
import { USER_STATES, USER_ROLES, API_ENDPOINTS } from '@core/constants/app.constants';
import { User } from '@core/models/user.model';

/**
 * Interfaz para el diálogo de confirmación
 */
interface ConfirmDialogData {
  title: string;
  message: string;
  confirmText: string;
  cancelText: string;
}

@Component({
  selector: 'app-consultar-usuarios',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatTableModule,
    MatPaginatorModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatButtonModule,
    MatIconModule,
    MatDialogModule,
    MatProgressSpinnerModule,
    AdminHeaderComponent
  ],
  template: `
    <div class="consultar-usuarios-container">
      <app-admin-header></app-admin-header>
      
      <div class="content-wrapper">
        <div class="page-header">
          <h2>Consultar Usuarios</h2>
          <button mat-raised-button color="accent" (click)="regresar()">
            <mat-icon>arrow_back</mat-icon>
            Regresar
          </button>
        </div>

        <!-- Filtros -->
        <div class="contenedor-filtros">
          <form [formGroup]="filtrosForm" class="filtros-form">
            <mat-form-field appearance="outline">
              <mat-label>Cédula</mat-label>
              <input matInput formControlName="ci" placeholder="Número de cédula">
              <mat-icon matPrefix>badge</mat-icon>
            </mat-form-field>

            <mat-form-field appearance="outline">
              <mat-label>Estado</mat-label>
              <mat-select formControlName="estado">
                <mat-option value="">-- Estado --</mat-option>
                <mat-option value="Activo">Activo</mat-option>
                <mat-option value="Inhabilitado">Inhabilitado</mat-option>
                <mat-option value="Pendiente de asignacion">Pendiente de asignación</mat-option>
              </mat-select>
            </mat-form-field>

            <mat-form-field appearance="outline">
              <mat-label>Tipo de Usuario</mat-label>
              <mat-select formControlName="tipo">
                <mat-option value="">-- Tipo de Usuario --</mat-option>
                <mat-option value="Contabilidad">Área de Contabilidad</mat-option>
                <mat-option value="Logistica">Área de Logística</mat-option>
                <mat-option value="Tecnico">Técnico</mat-option>
                <mat-option value="Administrador">Administrador</mat-option>
              </mat-select>
            </mat-form-field>

            <mat-form-field appearance="outline">
              <mat-label>Rango Fecha Última Sesión</mat-label>
              <mat-select formControlName="rango_fecha">
                <mat-option value="">-- Rango Fecha --</mat-option>
                <mat-option value="hoy">Hoy</mat-option>
                <mat-option value="ayer">Ayer</mat-option>
                <mat-option value="15dias">Últimos 15 días</mat-option>
                <mat-option value="30dias">Últimos 30 días</mat-option>
              </mat-select>
            </mat-form-field>

            <button mat-raised-button color="primary" (click)="buscarUsuarios()" [disabled]="loading">
              <mat-icon>search</mat-icon>
              Buscar
            </button>
            
            <button mat-raised-button (click)="limpiarFiltros()" [disabled]="loading">
              <mat-icon>clear</mat-icon>
              Limpiar
            </button>
          </form>
        </div>

        <!-- Tabla de resultados -->
        <div class="contenedor-tabla">
          <div *ngIf="loading" class="loading-container">
            <mat-spinner diameter="40"></mat-spinner>
            <p>Cargando usuarios...</p>
          </div>

          <div *ngIf="!loading && error" class="error-message">
            {{ error }}
          </div>

          <div *ngIf="!loading && !error">
            <table mat-table [dataSource]="usuarios" class="mat-elevation-z8">
              
              <!-- ID Column -->
              <ng-container matColumnDef="ID_Usuario">
                <th mat-header-cell *matHeaderCellDef> ID </th>
                <td mat-cell *matCellDef="let user"> {{user.ID_Usuario | slice:0:8}}... </td>
              </ng-container>

              <!-- Cédula Column -->
              <ng-container matColumnDef="ci">
                <th mat-header-cell *matHeaderCellDef> Cédula </th>
                <td mat-cell *matCellDef="let user"> {{user.ci}} </td>
              </ng-container>

              <!-- Nombre Column -->
              <ng-container matColumnDef="nombre">
                <th mat-header-cell *matHeaderCellDef> Nombre </th>
                <td mat-cell *matCellDef="let user"> {{user.nombre}} {{user.apellido}} </td>
              </ng-container>

              <!-- Email Column -->
              <ng-container matColumnDef="email">
                <th mat-header-cell *matHeaderCellDef> Email </th>
                <td mat-cell *matCellDef="let user"> {{user.email}} </td>
              </ng-container>

              <!-- Usuario Asignado Column -->
              <ng-container matColumnDef="usuario_asignado">
                <th mat-header-cell *matHeaderCellDef> Usuario </th>
                <td mat-cell *matCellDef="let user"> {{user.usuario_asignado}} </td>
              </ng-container>

              <!-- Estado Column -->
              <ng-container matColumnDef="estado">
                <th mat-header-cell *matHeaderCellDef> Estado </th>
                <td mat-cell *matCellDef="let user">
                  <span class="estado-badge" [ngClass]="{
                    'estado-activo': user.estado === 'Activo',
                    'estado-inhabilitado': user.estado === 'Inhabilitado',
                    'estado-pendiente': user.estado === 'Pendiente de asignacion'
                  }">
                    {{user.estado}}
                  </span>
                </td>
              </ng-container>

              <!-- Tipo Column -->
              <ng-container matColumnDef="tipo">
                <th mat-header-cell *matHeaderCellDef> Tipo </th>
                <td mat-cell *matCellDef="let user">
                  <span class="tipo-badge" [ngClass]="{
                    'tipo-admin': user.tipo === 'Administrador',
                    'tipo-contabilidad': user.tipo === 'Contabilidad',
                    'tipo-logistica': user.tipo === 'Logistica',
                    'tipo-tecnico': user.tipo === 'Tecnico'
                  }">
                    {{user.tipo}}
                  </span>
                </td>
              </ng-container>

              <!-- Acciones Column -->
              <ng-container matColumnDef="acciones">
                <th mat-header-cell *matHeaderCellDef> Acciones </th>
                <td mat-cell *matCellDef="let user">
                  <button mat-icon-button color="primary" (click)="verHistorial(user)" matTooltip="Ver Historial">
                    <mat-icon>history</mat-icon>
                  </button>
                  <button mat-icon-button color="accent" (click)="editarUsuario(user.ID_Usuario)" matTooltip="Editar Usuario">
                    <mat-icon>edit</mat-icon>
                  </button>
                  <button mat-icon-button color="warn" (click)="cambiarEstado(user)" matTooltip="Cambiar Estado">
                    <mat-icon>swap_horiz</mat-icon>
                  </button>
                  <button mat-icon-button color="warn" (click)="eliminarUsuario(user)" matTooltip="Eliminar">
                    <mat-icon>delete</mat-icon>
                  </button>
                </td>
              </ng-container>

              <tr mat-header-row *matHeaderRowDef="displayedColumns"></tr>
              <tr mat-row *matRowDef="let row; columns: displayedColumns;"></tr>
            </table>

            <!-- Paginación -->
            <mat-paginator
              [length]="totalItems"
              [pageSize]="pageSize"
              [pageSizeOptions]="[5, 10, 20, 50]"
              (page)="onPageChange($event)"
              showFirstLastButtons>
            </mat-paginator>

            <!-- Sin resultados -->
            <div *ngIf="usuarios.length === 0" class="no-results">
              <mat-icon>info</mat-icon>
              <p>No se encontraron usuarios</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Historial -->
    <div class="modal-overlay" *ngIf="modalHistorialVisible" (click)="cerrarModalHistorial($event)">
      <div class="modal-historial" (click)="$event.stopPropagation()">
        <div class="modal-header">
          <h2>Historial de Actividades</h2>
          <button class="modal-close" (click)="cerrarModalHistorial()">×</button>
        </div>
        <div class="modal-body">
          <h3>{{usuarioSeleccionado?.nombre}} {{usuarioSeleccionado?.apellido}}</h3>
          
          <div *ngIf="cargandoHistorial" class="loading-small">
            <mat-spinner diameter="30"></mat-spinner>
          </div>
          
          <div *ngIf="!cargandoHistorial">
            <div *ngIf="historialUsuario.length > 0; else sinHistorial">
              <table class="tabla-historial">
                <thead>
                  <tr>
                    <th>Fecha</th>
                    <th>Actividad</th>
                  </tr>
                </thead>
                <tbody>
                  <tr *ngFor="let actividad of historialUsuario">
                    <td>{{actividad.fecha_registro | date:'dd/MM/yyyy HH:mm:ss'}}</td>
                    <td>{{actividad.descripcion}}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <ng-template #sinHistorial>
              <p class="sin-registros">No hay actividades registradas</p>
            </ng-template>
          </div>
        </div>
        <div class="modal-footer">
          <button mat-button (click)="cerrarModalHistorial()">Cerrar</button>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .consultar-usuarios-container {
      min-height: 100vh;
      background: linear-gradient(135deg, #07224c 0%, #124258 50%, #3b4a66 100%);
    }
    
    .content-wrapper {
      padding: 2rem 5%;
    }
    
    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
    }
    
    .page-header h2 {
      color: white;
      margin: 0;
    }
    
    .contenedor-filtros {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 2rem;
    }
    
    .filtros-form {
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      align-items: center;
    }
    
    .filtros-form mat-form-field {
      flex: 1;
      min-width: 180px;
    }
    
    .contenedor-tabla {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }
    
    table {
      width: 100%;
    }
    
    .estado-badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
    }
    
    .estado-activo {
      background: #d4edda;
      color: #155724;
    }
    
    .estado-inhabilitado {
      background: #f8d7da;
      color: #721c24;
    }
    
    .estado-pendiente {
      background: #fff3cd;
      color: #856404;
    }
    
    .tipo-badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
    }
    
    .tipo-admin {
      background: #cce5ff;
      color: #004085;
    }
    
    .tipo-contabilidad {
      background: #d4edda;
      color: #155724;
    }
    
    .tipo-logistica {
      background: #d1ecf1;
      color: #0c5460;
    }
    
    .tipo-tecnico {
      background: #e2d5f1;
      color: #4a1d6d;
    }
    
    .loading-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 3rem;
    }
    
    .loading-small {
      display: flex;
      justify-content: center;
      padding: 2rem;
    }
    
    .error-message {
      padding: 1rem;
      margin: 1rem;
      background: #f8d7da;
      color: #721c24;
      border-radius: 8px;
    }
    
    .no-results {
      text-align: center;
      padding: 3rem;
      color: #666;
    }
    
    .no-results mat-icon {
      font-size: 3rem;
      width: auto;
      height: auto;
      margin-bottom: 1rem;
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
    
    .modal-historial {
      background: white;
      border-radius: 12px;
      max-width: 800px;
      width: 90%;
      max-height: 80vh;
      overflow-y: auto;
    }
    
    .modal-header {
      padding: 1rem 1.5rem;
      border-bottom: 1px solid #e0e0e0;
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: linear-gradient(135deg, #4f6bed, #3d55c3);
      color: white;
      border-radius: 12px 12px 0 0;
    }
    
    .modal-header h2 {
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
    }
    
    .tabla-historial {
      width: 100%;
      border-collapse: collapse;
    }
    
    .tabla-historial th,
    .tabla-historial td {
      padding: 0.75rem;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    
    .tabla-historial th {
      background: #f5f5f5;
      font-weight: 600;
    }
    
    .sin-registros {
      text-align: center;
      padding: 2rem;
      color: #666;
    }
    
    @media (max-width: 768px) {
      .content-wrapper {
        padding: 1rem;
      }
      
      .filtros-form {
        flex-direction: column;
      }
      
      .filtros-form mat-form-field {
        width: 100%;
      }
      
      .page-header {
        flex-direction: column;
        gap: 1rem;
      }
    }
  `]
})
export class ConsultarUsuariosComponent implements OnInit {
  private fb = inject(FormBuilder);
  private router = inject(Router);
  private apiService = inject(ApiService);
  private userService = inject(UserService);
  private snackBar = inject(MatSnackBar);
  
  filtrosForm: FormGroup;
  usuarios: User[] = [];
  displayedColumns: string[] = ['ID_Usuario', 'ci', 'nombre', 'email', 'usuario_asignado', 'estado', 'tipo', 'acciones'];
  
  loading = false;
  error = '';
  totalItems = 0;
  pageSize = 10;
  currentPage = 0;
  
  // Modal historial
  modalHistorialVisible = false;
  historialUsuario: any[] = [];
  usuarioSeleccionado: User | null = null;
  cargandoHistorial = false;
  
  ngOnInit(): void {
    this.initForm();
    this.cargarUsuarios();
  }
  
  private initForm(): void {
    this.filtrosForm = this.fb.group({
      ci: [''],
      estado: [''],
      tipo: [''],
      rango_fecha: ['']
    });
  }
  
  cargarUsuarios(): void {
    this.loading = true;
    this.error = '';
    
    const params: any = {
      page: this.currentPage + 1,
      limit: this.pageSize
    };
    
    const filtros = this.filtrosForm.value;
    if (filtros.ci) params.ci = filtros.ci;
    if (filtros.estado) params.estado = filtros.estado;
    if (filtros.tipo) params.tipo = filtros.tipo;
    if (filtros.rango_fecha) params.rango = filtros.rango_fecha;
    
    this.apiService.get(API_ENDPOINTS.ADMIN_USERS, params).subscribe({
      next: (response) => {
        if (response.success) {
          this.usuarios = response.usuarios || [];
          this.totalItems = response.total || this.usuarios.length;
        } else {
          this.error = response.message || 'Error al cargar usuarios';
        }
        this.loading = false;
      },
      error: (err) => {
        this.error = err.message || 'Error de conexión';
        this.loading = false;
      }
    });
  }
  
  buscarUsuarios(): void {
    this.currentPage = 0;
    this.cargarUsuarios();
  }
  
  limpiarFiltros(): void {
    this.filtrosForm.reset({
      ci: '',
      estado: '',
      tipo: '',
      rango_fecha: ''
    });
    this.buscarUsuarios();
  }
  
  onPageChange(event: PageEvent): void {
    this.currentPage = event.pageIndex;
    this.pageSize = event.pageSize;
    this.cargarUsuarios();
  }
  
  regresar(): void {
    this.router.navigate(['/admin/gestion-usuarios']);
  }
  
  editarUsuario(uuid: string): void {
    this.router.navigate([`/admin/editar-usuario/${uuid}`]);
  }
  
  cambiarEstado(usuario: User): void {
    this.router.navigate([`/admin/editar-usuario/${usuario.ID_Usuario}`], {
      queryParams: { modo: 'estado' }
    });
  }
  
  verHistorial(usuario: User): void {
    this.usuarioSeleccionado = usuario;
    this.modalHistorialVisible = true;
    this.cargarHistorialUsuario(usuario.ID_Usuario);
  }
  
  cargarHistorialUsuario(usuarioId: string): void {
    this.cargandoHistorial = true;
    this.userService.getHistorialActividades(usuarioId).subscribe({
      next: (historial) => {
        this.historialUsuario = historial;
        this.cargandoHistorial = false;
      },
      error: () => {
        this.historialUsuario = [];
        this.cargandoHistorial = false;
      }
    });
  }
  
  cerrarModalHistorial(event?: MouseEvent): void {
    this.modalHistorialVisible = false;
    this.historialUsuario = [];
    this.usuarioSeleccionado = null;
  }
  
  eliminarUsuario(usuario: User): void {
    const confirmDelete = confirm(`¿Está seguro de eliminar al usuario ${usuario.nombre} ${usuario.apellido}?`);
    
    if (!confirmDelete) return;
    
    this.loading = true;
    this.apiService.delete(API_ENDPOINTS.ADMIN_USER_BY_ID(usuario.ID_Usuario)).subscribe({
      next: (response) => {
        if (response.success) {
          this.snackBar.open('Usuario eliminado correctamente', 'Cerrar', { duration: 3000 });
          this.cargarUsuarios();
        } else {
          this.snackBar.open(response.message || 'Error al eliminar usuario', 'Cerrar', { duration: 3000 });
        }
        this.loading = false;
      },
      error: (err) => {
        this.snackBar.open(err.message || 'Error al eliminar usuario', 'Cerrar', { duration: 3000 });
        this.loading = false;
      }
    });
  }
}