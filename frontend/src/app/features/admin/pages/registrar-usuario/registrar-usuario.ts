/**
 * @fileoverview Componente de Registro de Usuario (Admin)
 * @description Permite al administrador registrar nuevos usuarios en el sistema
 * @component RegistrarUsuarioComponent
 */

import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { MatStepperModule } from '@angular/material/stepper';
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
import { USER_STATES, USER_ROLES, TECNICO_ESPECIALIDADES, API_ENDPOINTS } from '@core/constants/app.constants';

@Component({
  selector: 'app-registrar-usuario',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatStepperModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    AdminHeaderComponent
  ],
  template: `
    <div class="registrar-usuario-container">
      <app-admin-header></app-admin-header>
      
      <div class="content-wrapper">
        <div class="page-header">
          <button mat-icon-button (click)="regresar()" class="back-button">
            <mat-icon>arrow_back</mat-icon>
          </button>
          <h2>Registrar Nuevo Usuario</h2>
        </div>

        <mat-stepper [linear]="true" #stepper>
          <!-- Paso 1: Información Personal -->
          <mat-step [stepControl]="personalForm">
            <form [formGroup]="personalForm">
              <ng-template matStepLabel>Información Personal</ng-template>
              
              <div class="step-content">
                <mat-form-field appearance="outline">
                  <mat-label>Nombre</mat-label>
                  <input matInput formControlName="nombre" placeholder="Ingrese el nombre">
                  <mat-icon matPrefix>person</mat-icon>
                  <mat-error *ngIf="personalForm.get('nombre')?.hasError('required')">
                    Nombre requerido
                  </mat-error>
                </mat-form-field>

                <mat-form-field appearance="outline">
                  <mat-label>Apellido</mat-label>
                  <input matInput formControlName="apellido" placeholder="Ingrese el apellido">
                  <mat-icon matPrefix>person</mat-icon>
                  <mat-error *ngIf="personalForm.get('apellido')?.hasError('required')">
                    Apellido requerido
                  </mat-error>
                </mat-form-field>

                <mat-form-field appearance="outline">
                  <mat-label>Cédula</mat-label>
                  <input matInput formControlName="ci" placeholder="Número de cédula" maxlength="10">
                  <mat-icon matPrefix>badge</mat-icon>
                  <mat-error *ngIf="personalForm.get('ci')?.hasError('required')">
                    Cédula requerida
                  </mat-error>
                  <mat-error *ngIf="personalForm.get('ci')?.hasError('pattern')">
                    Cédula debe tener 10 dígitos
                  </mat-error>
                </mat-form-field>

                <mat-form-field appearance="outline">
                  <mat-label>Correo Electrónico</mat-label>
                  <input matInput formControlName="email" placeholder="ejemplo@correo.com" type="email">
                  <mat-icon matPrefix>email</mat-icon>
                  <mat-error *ngIf="personalForm.get('email')?.hasError('required')">
                    Email requerido
                  </mat-error>
                  <mat-error *ngIf="personalForm.get('email')?.hasError('email')">
                    Email inválido
                  </mat-error>
                </mat-form-field>
              </div>
              
              <div class="step-actions">
                <button mat-button matStepperNext [disabled]="personalForm.invalid">Siguiente</button>
              </div>
            </form>
          </mat-step>

          <!-- Paso 2: Credenciales -->
          <mat-step [stepControl]="credencialesForm">
            <form [formGroup]="credencialesForm">
              <ng-template matStepLabel>Credenciales</ng-template>
              
              <div class="step-content">
                <mat-form-field appearance="outline">
                  <mat-label>Usuario Asignado</mat-label>
                  <input matInput formControlName="usuario_asignado" placeholder="Nombre de usuario">
                  <mat-icon matPrefix>account_circle</mat-icon>
                  <mat-error *ngIf="credencialesForm.get('usuario_asignado')?.hasError('required')">
                    Usuario requerido
                  </mat-error>
                  <mat-error *ngIf="credencialesForm.get('usuario_asignado')?.hasError('maxlength')">
                    Máximo 15 caracteres
                  </mat-error>
                </mat-form-field>

                <mat-form-field appearance="outline">
                  <mat-label>Contraseña</mat-label>
                  <input matInput [type]="hidePassword ? 'password' : 'text'" formControlName="contrasena">
                  <mat-icon matPrefix>lock</mat-icon>
                  <mat-icon matSuffix (click)="hidePassword = !hidePassword">
                    {{hidePassword ? 'visibility_off' : 'visibility'}}
                  </mat-icon>
                  <mat-error *ngIf="credencialesForm.get('contrasena')?.hasError('required')">
                    Contraseña requerida
                  </mat-error>
                  <mat-error *ngIf="credencialesForm.get('contrasena')?.hasError('minlength')">
                    Mínimo 8 caracteres
                  </mat-error>
                </mat-form-field>

                <mat-form-field appearance="outline">
                  <mat-label>Confirmar Contraseña</mat-label>
                  <input matInput [type]="hideConfirmPassword ? 'password' : 'text'" formControlName="confirmar_contrasena">
                  <mat-icon matPrefix>lock</mat-icon>
                  <mat-icon matSuffix (click)="hideConfirmPassword = !hideConfirmPassword">
                    {{hideConfirmPassword ? 'visibility_off' : 'visibility'}}
                  </mat-icon>
                  <mat-error *ngIf="credencialesForm.hasError('passwordMismatch')">
                    Las contraseñas no coinciden
                  </mat-error>
                </mat-form-field>
              </div>
              
              <div class="step-actions">
                <button mat-button matStepperPrevious>Atrás</button>
                <button mat-button matStepperNext [disabled]="credencialesForm.invalid">Siguiente</button>
              </div>
            </form>
          </mat-step>

          <!-- Paso 3: Rol y Configuración -->
          <mat-step [stepControl]="rolForm">
            <form [formGroup]="rolForm">
              <ng-template matStepLabel>Rol y Configuración</ng-template>
              
              <div class="step-content">
                <mat-form-field appearance="outline">
                  <mat-label>Tipo de Usuario</mat-label>
                  <mat-select formControlName="tipo">
                    <mat-option value="Administrador">Administrador del sistema</mat-option>
                    <mat-option value="Contabilidad">Contabilidad</mat-option>
                    <mat-option value="Logistica">Logística</mat-option>
                    <mat-option value="Tecnico">Técnico</mat-option>
                  </mat-select>
                  <mat-icon matPrefix>work</mat-icon>
                  <mat-error *ngIf="rolForm.get('tipo')?.hasError('required')">
                    Tipo de usuario requerido
                  </mat-error>
                </mat-form-field>

                <mat-form-field appearance="outline" *ngIf="rolForm.get('tipo')?.value === 'Tecnico'">
                  <mat-label>Especialidad</mat-label>
                  <mat-select formControlName="especialidad">
                    <mat-option value="Ensamblador">Ensamblador</mat-option>
                    <mat-option value="Comprobador">Comprobador</mat-option>
                    <mat-option value="Mantenimiento">Mantenimiento</mat-option>
                  </mat-select>
                  <mat-icon matPrefix>build</mat-icon>
                  <mat-error *ngIf="rolForm.get('especialidad')?.hasError('required')">
                    Especialidad requerida
                  </mat-error>
                </mat-form-field>

                <mat-form-field appearance="outline">
                  <mat-label>Estado</mat-label>
                  <mat-select formControlName="estado">
                    <mat-option value="Activo">Activo</mat-option>
                    <mat-option value="Inhabilitado">Inhabilitado</mat-option>
                    <mat-option value="Pendiente de asignacion">Pendiente de asignación</mat-option>
                  </mat-select>
                  <mat-icon matPrefix>toggle_on</mat-icon>
                  <mat-error *ngIf="rolForm.get('estado')?.hasError('required')">
                    Estado requerido
                  </mat-error>
                </mat-form-field>
              </div>
              
              <div class="step-actions">
                <button mat-button matStepperPrevious>Atrás</button>
                <button mat-raised-button color="primary" [disabled]="rolForm.invalid || submitting" (click)="registrarUsuario(stepper)">
                  <mat-spinner diameter="20" *ngIf="submitting"></mat-spinner>
                  <span *ngIf="!submitting">Registrar Usuario</span>
                </button>
              </div>
            </form>
          </mat-step>
        </mat-stepper>

        <!-- Mensaje de éxito -->
        <div *ngIf="success" class="success-message">
          <mat-icon>check_circle</mat-icon>
          <p>¡Usuario registrado correctamente! Redirigiendo...</p>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .registrar-usuario-container {
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
    
    .step-content {
      display: flex;
      flex-direction: column;
      gap: 1rem;
      padding: 1.5rem 0;
    }
    
    .step-actions {
      display: flex;
      justify-content: flex-end;
      gap: 1rem;
      margin-top: 1rem;
    }
    
    .success-message {
      margin-top: 2rem;
      padding: 1rem;
      background: #d4edda;
      color: #155724;
      border-radius: 8px;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      justify-content: center;
    }
    
    @media (max-width: 768px) {
      .content-wrapper {
        padding: 1rem;
      }
    }
  `]
})
export class RegistrarUsuarioComponent {
  private fb = inject(FormBuilder);
  private router = inject(Router);
  private apiService = inject(ApiService);
  private userService = inject(UserService);
  private snackBar = inject(MatSnackBar);
  
  personalForm: FormGroup;
  credencialesForm: FormGroup;
  rolForm: FormGroup;
  
  hidePassword = true;
  hideConfirmPassword = true;
  submitting = false;
  success = false;
  
  constructor() {
    this.personalForm = this.fb.group({
      nombre: ['', Validators.required],
      apellido: ['', Validators.required],
      ci: ['', [Validators.required, Validators.pattern(/^\d{10}$/)]],
      email: ['', [Validators.required, Validators.email]]
    });
    
    this.credencialesForm = this.fb.group({
      usuario_asignado: ['', [Validators.required, Validators.maxLength(15)]],
      contrasena: ['', [Validators.required, Validators.minLength(8)]],
      confirmar_contrasena: ['', Validators.required]
    }, { validators: this.passwordMatchValidator });
    
    this.rolForm = this.fb.group({
      tipo: ['Logistica', Validators.required],
      especialidad: [''],
      estado: ['Activo', Validators.required]
    });
    
    // Actualizar validación de especialidad cuando cambia el tipo
    this.rolForm.get('tipo')?.valueChanges.subscribe(tipo => {
      const especialidadControl = this.rolForm.get('especialidad');
      if (tipo === 'Tecnico') {
        especialidadControl?.setValidators(Validators.required);
      } else {
        especialidadControl?.clearValidators();
        especialidadControl?.setValue('');
      }
      especialidadControl?.updateValueAndValidity();
    });
  }
  
  private passwordMatchValidator(group: FormGroup): { [key: string]: boolean } | null {
    const password = group.get('contrasena')?.value;
    const confirm = group.get('confirmar_contrasena')?.value;
    return password === confirm ? null : { passwordMismatch: true };
  }
  
  registrarUsuario(stepper: any): void {
    if (this.personalForm.invalid || this.credencialesForm.invalid || this.rolForm.invalid) {
      this.snackBar.open('Complete todos los campos correctamente', 'Cerrar', { duration: 3000 });
      return;
    }
    
    if (!confirm('¿Está seguro de registrar al usuario?')) return;
    
    this.submitting = true;
    
    const userData = {
      ...this.personalForm.value,
      ...this.credencialesForm.value,
      tipo: this.rolForm.get('tipo')?.value,
      estado: this.rolForm.get('estado')?.value
    };
    
    // Eliminar confirmar_contrasena del payload
    delete userData.confirmar_contrasena;
    
    // Agregar especialidad si es técnico
    if (userData.tipo === 'Tecnico') {
      userData.especialidad = this.rolForm.get('especialidad')?.value;
    }
    
    this.apiService.post(API_ENDPOINTS.ADMIN_USERS, userData).subscribe({
      next: (response) => {
        if (response.success) {
          const currentUser = JSON.parse(localStorage.getItem('user') || '{}');
          if (currentUser.ID_Usuario) {
            this.userService.registrarActividad(
              currentUser.ID_Usuario,
              `El usuario registró un nuevo usuario: ${userData.nombre} ${userData.apellido}`
            ).subscribe();
          }
          
          this.success = true;
          this.snackBar.open('Usuario registrado correctamente', 'Cerrar', { duration: 3000 });
          
          setTimeout(() => {
            this.router.navigate(['/admin/gestion-usuarios']);
          }, 2000);
        } else {
          this.snackBar.open(response.message || 'Error al registrar usuario', 'Cerrar', { duration: 3000 });
        }
        this.submitting = false;
      },
      error: (err) => {
        this.snackBar.open(err.message || 'Error al registrar usuario', 'Cerrar', { duration: 3000 });
        this.submitting = false;
      }
    });
  }
  
  regresar(): void {
    this.router.navigate(['/admin/gestion-usuarios']);
  }
}