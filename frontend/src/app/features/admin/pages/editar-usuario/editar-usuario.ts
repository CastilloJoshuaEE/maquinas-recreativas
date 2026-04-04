/**
 * @fileoverview Componente de Edición de Usuario
 * @description Permite editar información de usuarios y cambiar su estado
 * @component EditarUsuarioComponent
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header.component';
import { ApiService } from '@core/services/api.service';
import { UserService } from '@core/services/user.service';
import { API_ENDPOINTS } from '@core/constants/app.constants';
import { User } from '@core/models/user.model';

@Component({
  selector: 'app-editar-usuario',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatCardModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    AdminHeaderComponent
  ],
  template: `
    <div class="editar-usuario-container">
      <app-admin-header></app-admin-header>
      
      <div class="content-wrapper">
        <div class="page-header">
          <button mat-icon-button (click)="regresar()" class="back-button">
            <mat-icon>arrow_back</mat-icon>
          </button>
          <h2>{{ modo === 'actualizar' ? 'Editar Usuario' : 'Cambiar Estado de Usuario' }}</h2>
        </div>

        <mat-card>
          <mat-card-content>
            <div *ngIf="loading" class="loading-container">
              <mat-spinner diameter="40"></mat-spinner>
              <p>Cargando datos del usuario...</p>
            </div>

            <div *ngIf="error" class="error-message">
              {{ error }}
            </div>

            <div *ngIf="!loading && usuario">
              <form [formGroup]="usuarioForm" (ngSubmit)="onSubmit()">
                <!-- Modo Actualizar - Todos los campos -->
                <ng-container *ngIf="modo === 'actualizar'">
                  <mat-form-field appearance="outline" class="full-width">
                    <mat-label>Nombre</mat-label>
                    <input matInput formControlName="nombre">
                    <mat-icon matPrefix>person</mat-icon>
                    <mat-error *ngIf="usuarioForm.get('nombre')?.hasError('required')">
                      Nombre requerido
                    </mat-error>
                  </mat-form-field>

                  <mat-form-field appearance="outline" class="full-width">
                    <mat-label>Apellido</mat-label>
                    <input matInput formControlName="apellido">
                    <mat-icon matPrefix>person</mat-icon>
                    <mat-error *ngIf="usuarioForm.get('apellido')?.hasError('required')">
                      Apellido requerido
                    </mat-error>
                  </mat-form-field>

                  <mat-form-field appearance="outline" class="full-width">
                    <mat-label>Cédula</mat-label>
                    <input matInput formControlName="ci">
                    <mat-icon matPrefix>badge</mat-icon>
                    <mat-error *ngIf="usuarioForm.get('ci')?.hasError('required')">
                      Cédula requerida
                    </mat-error>
                    <mat-error *ngIf="usuarioForm.get('ci')?.hasError('pattern')">
                      Cédula debe tener 10 dígitos
                    </mat-error>
                  </mat-form-field>

                  <mat-form-field appearance="outline" class="full-width">
                    <mat-label>Email</mat-label>
                    <input matInput formControlName="email" type="email">
                    <mat-icon matPrefix>email</mat-icon>
                    <mat-error *ngIf="usuarioForm.get('email')?.hasError('required')">
                      Email requerido
                    </mat-error>
                    <mat-error *ngIf="usuarioForm.get('email')?.hasError('email')">
                      Email inválido
                    </mat-error>
                  </mat-form-field>

                  <mat-form-field appearance="outline" class="full-width">
                    <mat-label>Usuario Asignado</mat-label>
                    <input matInput formControlName="usuario_asignado">
                    <mat-icon matPrefix>account_circle</mat-icon>
                    <mat-error *ngIf="usuarioForm.get('usuario_asignado')?.hasError('required')">
                      Usuario requerido
                    </mat-error>
                    <mat-error *ngIf="usuarioForm.get('usuario_asignado')?.hasError('maxlength')">
                      Máximo 15 caracteres
                    </mat-error>
                  </mat-form-field>

                  <mat-form-field appearance="outline" class="full-width">
                    <mat-label>Tipo de Usuario</mat-label>
                    <mat-select formControlName="tipo">
                      <mat-option value="Administrador">Administrador del sistema</mat-option>
                      <mat-option value="Contabilidad">Contabilidad</mat-option>
                      <mat-option value="Logistica">Logística</mat-option>
                      <mat-option value="Tecnico">Técnico</mat-option>
                    </mat-select>
                    <mat-icon matPrefix>work</mat-icon>
                  </mat-form-field>

                  <mat-form-field appearance="outline" class="full-width" *ngIf="usuarioForm.get('tipo')?.value === 'Tecnico'">
                    <mat-label>Especialidad</mat-label>
                    <mat-select formControlName="especialidad">
                      <mat-option value="Ensamblador">Ensamblador</mat-option>
                      <mat-option value="Comprobador">Comprobador</mat-option>
                      <mat-option value="Mantenimiento">Mantenimiento</mat-option>
                    </mat-select>
                    <mat-icon matPrefix>build</mat-icon>
                  </mat-form-field>

                  <mat-form-field appearance="outline" class="full-width">
                    <mat-label>Nueva Contraseña (opcional)</mat-label>
                    <input matInput [type]="hidePassword ? 'password' : 'text'" formControlName="contrasena">
                    <mat-icon matPrefix>lock</mat-icon>
                    <mat-icon matSuffix (click)="hidePassword = !hidePassword">
                      {{hidePassword ? 'visibility_off' : 'visibility'}}
                    </mat-icon>
                    <mat-hint>Dejar vacío para mantener la contraseña actual</mat-hint>
                    <mat-error *ngIf="usuarioForm.get('contrasena')?.hasError('minlength')">
                      Mínimo 8 caracteres
                    </mat-error>
                  </mat-form-field>

                  <mat-form-field appearance="outline" class="full-width">
                    <mat-label>Estado Actual</mat-label>
                    <input matInput [value]="usuario.estado" readonly disabled>
                    <mat-icon matPrefix>info</mat-icon>
                  </mat-form-field>
                </ng-container>

                <!-- Modo Estado - Solo cambiar estado -->
                <ng-container *ngIf="modo === 'estado'">
                  <mat-form-field appearance="outline" class="full-width">
                    <mat-label>Estado Actual</mat-label>
                    <input matInput [value]="usuario.estado" readonly disabled>
                    <mat-icon matPrefix>info</mat-icon>
                  </mat-form-field>

                  <mat-form-field appearance="outline" class="full-width">
                    <mat-label>Nuevo Estado</mat-label>
                    <mat-select formControlName="estado">
                      <mat-option value="Activo">Activo</mat-option>
                      <mat-option value="Inhabilitado">Inhabilitado</mat-option>
                      <mat-option value="Pendiente de asignacion">Pendiente de asignación</mat-option>
                    </mat-select>
                    <mat-icon matPrefix>toggle_on</mat-icon>
                  </mat-form-field>
                </ng-container>

                <div class="form-actions">
                  <button mat-raised-button color="primary" type="submit" [disabled]="submitting">
                    <mat-spinner diameter="20" *ngIf="submitting"></mat-spinner>
                    <span *ngIf="!submitting">Guardar Cambios</span>
                  </button>
                  <button mat-button type="button" (click)="regresar()">Cancelar</button>
                </div>
              </form>
            </div>

            <div *ngIf="success" class="success-message">
              <mat-icon>check_circle</mat-icon>
              <p>¡Operación realizada correctamente! Redirigiendo...</p>
            </div>
          </mat-card-content>
        </mat-card>
      </div>
    </div>
  `,
  styles: [`
    .editar-usuario-container {
      min-height: 100vh;
      background: linear-gradient(135deg, #07224c 0%, #124258 50%, #3b4a66 100%);
    }
    
    .content-wrapper {
      max-width: 600px;
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
    
    .full-width {
      width: 100%;
      margin-bottom: 1rem;
    }
    
    .loading-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 3rem;
    }
    
    .error-message {
      padding: 1rem;
      background: #f8d7da;
      color: #721c24;
      border-radius: 8px;
      margin-bottom: 1rem;
    }
    
    .success-message {
      margin-top: 1rem;
      padding: 1rem;
      background: #d4edda;
      color: #155724;
      border-radius: 8px;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      justify-content: center;
    }
    
    .form-actions {
      display: flex;
      gap: 1rem;
      justify-content: center;
      margin-top: 1.5rem;
    }
    
    @media (max-width: 768px) {
      .content-wrapper {
        padding: 1rem;
      }
    }
  `]
})
export class EditarUsuarioComponent implements OnInit {
  private fb = inject(FormBuilder);
  private route = inject(ActivatedRoute);
  private router = inject(Router);
  private apiService = inject(ApiService);
  private userService = inject(UserService);
  private snackBar = inject(MatSnackBar);
  
  usuarioForm: FormGroup;
  usuario: User | null = null;
  modo: 'actualizar' | 'estado' = 'actualizar';
  loading = true;
  submitting = false;
  success = false;
  error = '';
  hidePassword = true;
  
  ngOnInit(): void {
    this.route.params.subscribe(params => {
      const uuid = params['uuid'];
      this.route.queryParams.subscribe(queryParams => {
        this.modo = queryParams['modo'] === 'estado' ? 'estado' : 'actualizar';
        if (uuid) {
          this.cargarUsuario(uuid);
        }
      });
    });
  }
  
  private cargarUsuario(uuid: string): void {
    this.loading = true;
    this.error = '';
    
    this.apiService.get(API_ENDPOINTS.ADMIN_USER_BY_ID(uuid)).subscribe({
      next: (response) => {
        if (response.success && response.usuario) {
          this.usuario = response.usuario;
          this.inicializarFormulario();
        } else {
          this.error = response.message || 'Usuario no encontrado';
        }
        this.loading = false;
      },
      error: (err) => {
        this.error = err.message || 'Error al cargar usuario';
        this.loading = false;
      }
    });
  }
  
  private inicializarFormulario(): void {
    if (!this.usuario) return;
    
    if (this.modo === 'actualizar') {
      this.usuarioForm = this.fb.group({
        nombre: [this.usuario.nombre, Validators.required],
        apellido: [this.usuario.apellido, Validators.required],
        ci: [this.usuario.ci, [Validators.required, Validators.pattern(/^\d{10}$/)]],
        email: [this.usuario.email, [Validators.required, Validators.email]],
        usuario_asignado: [this.usuario.usuario_asignado, [Validators.required, Validators.maxLength(15)]],
        tipo: [this.usuario.tipo, Validators.required],
        especialidad: [this.usuario.Especialidad || ''],
        contrasena: ['', [Validators.minLength(8)]]
      });
    } else {
      this.usuarioForm = this.fb.group({
        estado: ['', Validators.required]
      });
    }
  }
  
  onSubmit(): void {
    if (this.usuarioForm.invalid) {
      this.snackBar.open('Complete todos los campos correctamente', 'Cerrar', { duration: 3000 });
      return;
    }
    
    if (!confirm('¿Está seguro de guardar los cambios?')) return;
    
    this.submitting = true;
    const uuid = this.route.snapshot.params['uuid'];
    
    if (this.modo === 'actualizar') {
      this.actualizarUsuarioCompleto(uuid);
    } else {
      this.actualizarEstadoUsuario(uuid);
    }
  }
  
  private actualizarUsuarioCompleto(uuid: string): void {
    const formData = this.usuarioForm.value;
    
    // Eliminar campos vacíos
    if (!formData.contrasena) {
      delete formData.contrasena;
    }
    
    this.apiService.put(API_ENDPOINTS.ADMIN_USER_BY_ID(uuid), formData).subscribe({
      next: (response) => {
        if (response.success) {
          this.success = true;
          this.snackBar.open('Usuario actualizado correctamente', 'Cerrar', { duration: 3000 });
          setTimeout(() => this.router.navigate(['/admin/consultar-usuarios']), 2000);
        } else {
          this.snackBar.open(response.message || 'Error al actualizar usuario', 'Cerrar', { duration: 3000 });
        }
        this.submitting = false;
      },
      error: (err) => {
        this.snackBar.open(err.message || 'Error al actualizar usuario', 'Cerrar', { duration: 3000 });
        this.submitting = false;
      }
    });
  }
  
  private actualizarEstadoUsuario(uuid: string): void {
    const nuevoEstado = this.usuarioForm.get('estado')?.value;
    
    this.apiService.patch(API_ENDPOINTS.ADMIN_USER_BY_ID(uuid), { estado: nuevoEstado }).subscribe({
      next: (response) => {
        if (response.success) {
          this.success = true;
          this.snackBar.open('Estado actualizado correctamente', 'Cerrar', { duration: 3000 });
          setTimeout(() => this.router.navigate(['/admin/consultar-usuarios']), 2000);
        } else {
          this.snackBar.open(response.message || 'Error al cambiar estado', 'Cerrar', { duration: 3000 });
        }
        this.submitting = false;
      },
      error: (err) => {
        this.snackBar.open(err.message || 'Error al cambiar estado', 'Cerrar', { duration: 3000 });
        this.submitting = false;
      }
    });
  }
  
  regresar(): void {
    this.router.navigate(['/admin/consultar-usuarios']);
  }
}