/**
 * @fileoverview Componente de Actualización de Perfil
 * @description Permite al usuario actualizar su información personal
 * @component ActualizarPerfilComponent
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header.component';
import { AuthService } from '@core/services/auth.service';
import { UserService } from '@core/services/user.service';
import { User } from '@core/models/user.model';

@Component({
  selector: 'app-actualizar-perfil',
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
    <div class="actualizar-perfil-container">
      <app-admin-header></app-admin-header>
      
      <div class="content-wrapper">
        <div class="page-header">
          <button mat-icon-button (click)="regresar()" class="back-button">
            <mat-icon>arrow_back</mat-icon>
          </button>
          <h2>Editar Perfil</h2>
        </div>

        <div *ngIf="loading" class="loading-container">
          <mat-spinner diameter="40"></mat-spinner>
          <p>Cargando datos...</p>
        </div>

        <div *ngIf="error" class="error-message">
          {{ error }}
        </div>

        <mat-card *ngIf="!loading && !error && usuario">
          <mat-card-content>
            <form [formGroup]="perfilForm" (ngSubmit)="onSubmit()">
              <!-- Cédula (solo lectura) -->
              <mat-form-field appearance="outline" class="full-width">
                <mat-label>Cédula</mat-label>
                <input matInput formControlName="ci" readonly>
                <mat-icon matPrefix>badge</mat-icon>
              </mat-form-field>

              <!-- Nombre -->
              <mat-form-field appearance="outline" class="full-width">
                <mat-label>Nombre</mat-label>
                <input matInput formControlName="nombre">
                <mat-icon matPrefix>person</mat-icon>
                <mat-error *ngIf="perfilForm.get('nombre')?.hasError('required')">
                  Nombre requerido
                </mat-error>
              </mat-form-field>

              <!-- Apellido -->
              <mat-form-field appearance="outline" class="full-width">
                <mat-label>Apellido</mat-label>
                <input matInput formControlName="apellido">
                <mat-icon matPrefix>person</mat-icon>
                <mat-error *ngIf="perfilForm.get('apellido')?.hasError('required')">
                  Apellido requerido
                </mat-error>
              </mat-form-field>

              <!-- Email -->
              <mat-form-field appearance="outline" class="full-width">
                <mat-label>Correo electrónico</mat-label>
                <input matInput formControlName="email" type="email">
                <mat-icon matPrefix>email</mat-icon>
                <mat-error *ngIf="perfilForm.get('email')?.hasError('required')">
                  Email requerido
                </mat-error>
                <mat-error *ngIf="perfilForm.get('email')?.hasError('email')">
                  Email inválido
                </mat-error>
              </mat-form-field>

              <!-- Usuario Asignado (solo lectura) -->
              <mat-form-field appearance="outline" class="full-width">
                <mat-label>Usuario asignado</mat-label>
                <input matInput formControlName="usuario_asignado" readonly>
                <mat-icon matPrefix>account_circle</mat-icon>
              </mat-form-field>

              <!-- Nueva Contraseña (opcional) -->
              <mat-form-field appearance="outline" class="full-width">
                <mat-label>Nueva contraseña (opcional)</mat-label>
                <input matInput [type]="hidePassword ? 'password' : 'text'" formControlName="contrasena">
                <mat-icon matPrefix>lock</mat-icon>
                <mat-icon matSuffix (click)="hidePassword = !hidePassword">
                  {{hidePassword ? 'visibility_off' : 'visibility'}}
                </mat-icon>
                <mat-hint>Dejar vacío para mantener la contraseña actual</mat-hint>
                <mat-error *ngIf="perfilForm.get('contrasena')?.hasError('minlength')">
                  Mínimo 8 caracteres
                </mat-error>
              </mat-form-field>

              <!-- Función del sistema (solo lectura) -->
              <mat-form-field appearance="outline" class="full-width">
                <mat-label>Función del sistema</mat-label>
                <mat-select formControlName="tipo" disabled>
                  <mat-option value="Administrador">Administrador del sistema</mat-option>
                  <mat-option value="Contabilidad">Área de Contabilidad</mat-option>
                  <mat-option value="Logistica">Logística</mat-option>
                  <mat-option value="Tecnico">Técnico</mat-option>
                </mat-select>
                <mat-icon matPrefix>work</mat-icon>
              </mat-form-field>

              <!-- Especialidad (solo para técnicos, solo lectura) -->
              <mat-form-field appearance="outline" class="full-width" *ngIf="usuario.tipo === 'Tecnico'">
                <mat-label>Especialidad</mat-label>
                <mat-select formControlName="especialidad" disabled>
                  <mat-option value="Ensamblador">Ensamblador</mat-option>
                  <mat-option value="Comprobador">Comprobador</mat-option>
                  <mat-option value="Mantenimiento">Mantenimiento</mat-option>
                </mat-select>
                <mat-icon matPrefix>build</mat-icon>
              </mat-form-field>

              <!-- Estado (solo lectura) -->
              <mat-form-field appearance="outline" class="full-width">
                <mat-label>Estado</mat-label>
                <mat-select formControlName="estado" disabled>
                  <mat-option value="Activo">Activo</mat-option>
                  <mat-option value="Inhabilitado">Inhabilitado</mat-option>
                  <mat-option value="Pendiente de asignacion">Pendiente de asignación</mat-option>
                </mat-select>
                <mat-icon matPrefix>info</mat-icon>
              </mat-form-field>

              <div class="form-actions">
                <button mat-raised-button color="primary" type="submit" [disabled]="submitting">
                  <mat-spinner diameter="20" *ngIf="submitting"></mat-spinner>
                  <span *ngIf="!submitting">Actualizar Perfil</span>
                </button>
                <button mat-button type="button" (click)="regresar()">Cancelar</button>
              </div>
            </form>
          </mat-card-content>
        </mat-card>

        <div *ngIf="success" class="success-message">
          <mat-icon>check_circle</mat-icon>
          <p>¡Perfil actualizado correctamente! Redirigiendo...</p>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .actualizar-perfil-container {
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
      background: white;
      border-radius: 12px;
    }
    
    .error-message {
      padding: 1rem;
      background: #f8d7da;
      color: #721c24;
      border-radius: 8px;
      margin-bottom: 1rem;
      text-align: center;
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
      
      .form-actions {
        flex-direction: column;
      }
      
      .form-actions button {
        width: 100%;
      }
    }
  `]
})
export class ActualizarPerfilComponent implements OnInit {
  private fb = inject(FormBuilder);
  private router = inject(Router);
  private authService = inject(AuthService);
  private userService = inject(UserService);
  private snackBar = inject(MatSnackBar);
  
  perfilForm: FormGroup;
  usuario: User | null = null;
  loading = true;
  submitting = false;
  success = false;
  error = '';
  hidePassword = true;
  
  ngOnInit(): void {
    this.cargarPerfil();
  }
  
  private cargarPerfil(): void {
    this.loading = true;
    this.error = '';
    
    const currentUser = this.authService.getCurrentUser();
    if (!currentUser || !currentUser.ID_Usuario) {
      this.error = 'Usuario no autenticado';
      this.loading = false;
      return;
    }
    
    this.userService.getProfile(currentUser.ID_Usuario).subscribe({
      next: (usuario) => {
        if (usuario) {
          this.usuario = usuario;
          this.inicializarFormulario();
          this.registrarActividad();
        } else {
          this.error = 'No se encontró el perfil del usuario';
        }
        this.loading = false;
      },
      error: (err) => {
        this.error = err.message || 'Error al cargar el perfil';
        this.loading = false;
      }
    });
  }
  
  private inicializarFormulario(): void {
    if (!this.usuario) return;
    
    this.perfilForm = this.fb.group({
      ci: [{ value: this.usuario.ci, disabled: true }],
      nombre: [this.usuario.nombre, Validators.required],
      apellido: [this.usuario.apellido, Validators.required],
      email: [this.usuario.email, [Validators.required, Validators.email]],
      usuario_asignado: [{ value: this.usuario.usuario_asignado, disabled: true }],
      contrasena: ['', [Validators.minLength(8)]],
      tipo: [{ value: this.usuario.tipo, disabled: true }],
      especialidad: [{ value: this.usuario.Especialidad || '', disabled: true }],
      estado: [{ value: this.usuario.estado, disabled: true }]
    });
  }
  
  private registrarActividad(): void {
    const currentUser = this.authService.getCurrentUser();
    if (currentUser?.ID_Usuario) {
      this.userService.registrarActividad(
        currentUser.ID_Usuario,
        'El usuario accedió a actualizar su perfil'
      ).subscribe();
    }
  }
  
  onSubmit(): void {
    if (this.perfilForm.invalid) {
      this.snackBar.open('Complete todos los campos correctamente', 'Cerrar', { duration: 3000 });
      return;
    }
    
    if (!confirm('¿Está seguro de guardar los cambios?')) return;
    
    this.submitting = true;
    const formValue = this.perfilForm.getRawValue();
    const currentUser = this.authService.getCurrentUser();
    
    const updateData = {
      id: currentUser?.ID_Usuario,
      nombre: formValue.nombre,
      apellido: formValue.apellido,
      email: formValue.email,
      ci: formValue.ci,
      tipo: formValue.tipo,
      estado: formValue.estado,
      especialidad: formValue.especialidad || null
    };
    
    if (formValue.contrasena && formValue.contrasena.trim() !== '') {
      updateData['contrasena'] = formValue.contrasena;
    }
    
    this.userService.updateProfile(updateData).subscribe({
      next: (response) => {
        if (response.success) {
          this.success = true;
          this.snackBar.open('Perfil actualizado correctamente', 'Cerrar', { duration: 3000 });
          
          // Actualizar usuario en localStorage
          if (currentUser) {
            const updatedUser = {
              ...currentUser,
              nombre: updateData.nombre,
              apellido: updateData.apellido,
              email: updateData.email
            };
            localStorage.setItem('user', JSON.stringify(updatedUser));
          }
          
          setTimeout(() => {
            this.router.navigate(['/usuario/perfil']);
          }, 2000);
        } else {
          this.snackBar.open(response.message || 'Error al actualizar perfil', 'Cerrar', { duration: 3000 });
        }
        this.submitting = false;
      },
      error: (err) => {
        this.snackBar.open(err.message || 'Error al actualizar perfil', 'Cerrar', { duration: 3000 });
        this.submitting = false;
      }
    });
  }
  
  regresar(): void {
    this.router.navigate(['/usuario/perfil']);
  }
}