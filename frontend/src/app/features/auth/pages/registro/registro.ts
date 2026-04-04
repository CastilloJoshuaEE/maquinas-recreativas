/**
 * @fileoverview Componente de Registro de Usuario
 * @description Página de registro para nuevos usuarios del sistema
 * @component RegistroComponent
 */

import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { ToastrService } from 'ngx-toastr';
import { AuthService } from '@core/services/auth.service';

@Component({
  selector: 'app-registro',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    RouterLink,
    MatCardModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatButtonModule,
    MatProgressSpinnerModule
  ],
  template: `
    <div class="registro-container">
      <mat-card>
        <mat-card-header>
          <mat-card-title>
            <h2>Formulario de Registro</h2>
          </mat-card-title>
        </mat-card-header>
        
        <mat-card-content>
          <form [formGroup]="registroForm" (ngSubmit)="onSubmit()">
            <!-- Nombre -->
            <mat-form-field appearance="outline" class="full-width">
              <mat-label>Nombre</mat-label>
              <input matInput formControlName="nombre" placeholder="Ingrese su nombre">
              <mat-icon matPrefix>person</mat-icon>
              <mat-error *ngIf="registroForm.get('nombre')?.hasError('required')">
                Nombre requerido
              </mat-error>
            </mat-form-field>

            <!-- Apellido -->
            <mat-form-field appearance="outline" class="full-width">
              <mat-label>Apellido</mat-label>
              <input matInput formControlName="apellido" placeholder="Ingrese su apellido">
              <mat-icon matPrefix>person</mat-icon>
              <mat-error *ngIf="registroForm.get('apellido')?.hasError('required')">
                Apellido requerido
              </mat-error>
            </mat-form-field>

            <!-- Cédula -->
            <mat-form-field appearance="outline" class="full-width">
              <mat-label>Cédula (CI)</mat-label>
              <input matInput formControlName="ci" placeholder="Ingrese su cédula" maxlength="10">
              <mat-icon matPrefix>badge</mat-icon>
              <mat-error *ngIf="registroForm.get('ci')?.hasError('required')">
                Cédula requerida
              </mat-error>
              <mat-error *ngIf="registroForm.get('ci')?.hasError('pattern')">
                Cédula debe tener 10 dígitos
              </mat-error>
            </mat-form-field>

            <!-- Email -->
            <mat-form-field appearance="outline" class="full-width">
              <mat-label>Correo electrónico</mat-label>
              <input matInput formControlName="email" placeholder="ejemplo@correo.com" type="email">
              <mat-icon matPrefix>email</mat-icon>
              <mat-error *ngIf="registroForm.get('email')?.hasError('required')">
                Email requerido
              </mat-error>
              <mat-error *ngIf="registroForm.get('email')?.hasError('email')">
                Email inválido
              </mat-error>
            </mat-form-field>

            <!-- Tipo de Usuario -->
            <mat-form-field appearance="outline" class="full-width">
              <mat-label>Área donde hay vacantes</mat-label>
              <mat-select formControlName="tipo">
                <mat-option value="Logistica">Área de Logística</mat-option>
                <mat-option value="Tecnico">Técnico</mat-option>
                <mat-option value="Contabilidad">Área de Contabilidad</mat-option>
                <mat-option value="Administrador">Administrador del sistema</mat-option>
              </mat-select>
              <mat-icon matPrefix>work</mat-icon>
              <mat-error *ngIf="registroForm.get('tipo')?.hasError('required')">
                Seleccione un área
              </mat-error>
            </mat-form-field>

            <!-- Especialidad (solo para técnicos) -->
            <mat-form-field appearance="outline" class="full-width" *ngIf="registroForm.get('tipo')?.value === 'Tecnico'">
              <mat-label>Especialidad</mat-label>
              <mat-select formControlName="especialidad">
                <mat-option value="Ensamblador">Ensamblador</mat-option>
                <mat-option value="Comprobador">Comprobador</mat-option>
                <mat-option value="Mantenimiento">Mantenimiento</mat-option>
              </mat-select>
              <mat-icon matPrefix>build</mat-icon>
              <mat-error *ngIf="registroForm.get('especialidad')?.hasError('required')">
                Seleccione una especialidad
              </mat-error>
            </mat-form-field>

            <!-- Usuario Asignado -->
            <mat-form-field appearance="outline" class="full-width">
              <mat-label>Nombre de usuario</mat-label>
              <input matInput formControlName="usuario_asignado" placeholder="Elija un nombre de usuario">
              <mat-icon matPrefix>account_circle</mat-icon>
              <mat-error *ngIf="registroForm.get('usuario_asignado')?.hasError('required')">
                Usuario requerido
              </mat-error>
              <mat-error *ngIf="registroForm.get('usuario_asignado')?.hasError('maxlength')">
                Máximo 15 caracteres
              </mat-error>
            </mat-form-field>

            <!-- Contraseña -->
            <mat-form-field appearance="outline" class="full-width">
              <mat-label>Contraseña</mat-label>
              <input matInput [type]="hidePassword ? 'password' : 'text'" formControlName="contrasena">
              <mat-icon matPrefix>lock</mat-icon>
              <mat-icon matSuffix (click)="hidePassword = !hidePassword">
                {{hidePassword ? 'visibility_off' : 'visibility'}}
              </mat-icon>
              <mat-error *ngIf="registroForm.get('contrasena')?.hasError('required')">
                Contraseña requerida
              </mat-error>
              <mat-error *ngIf="registroForm.get('contrasena')?.hasError('minlength')">
                Mínimo 8 caracteres
              </mat-error>
            </mat-form-field>

            <!-- Archivo CV -->
            <div class="file-upload">
              <label class="file-label">
                <mat-icon>attach_file</mat-icon>
                Subir Currículum (PDF)
                <input type="file" accept=".pdf" (change)="onFileSelected($event)" hidden>
              </label>
              <span *ngIf="fileName" class="file-name">{{ fileName }}</span>
            </div>

            <div class="form-actions">
              <button mat-raised-button color="primary" type="submit" [disabled]="registroForm.invalid || loading">
                <mat-spinner diameter="20" *ngIf="loading"></mat-spinner>
                <span *ngIf="!loading">Registrar</span>
              </button>
              <button mat-button type="button" routerLink="/auth/login">Volver al inicio</button>
            </div>
          </form>
        </mat-card-content>
      </mat-card>

      <!-- Sección de requisitos -->
      <section class="requisitos-postulante">
        <h2>¿Qué buscamos en nuestros postulantes?</h2>
        <div class="requisitos-tipos">
          <div class="requisito-card">
            <h3>🛠 Técnico</h3>
            <ul>
              <li>Conocimiento en mantenimiento de hardware o electrónica básica</li>
              <li>Capacidad para diagnosticar fallas en máquinas recreativas</li>
              <li>Actitud proactiva y resolutiva ante fallos técnicos</li>
            </ul>
          </div>
          <div class="requisito-card">
            <h3>📦 Logística</h3>
            <ul>
              <li>Buena comunicación con técnicos y contabilidad</li>
              <li>Capacidad para trabajar bajo presión y tiempos ajustados</li>
            </ul>
          </div>
          <div class="requisito-card">
            <h3>💰 Contabilidad</h3>
            <ul>
              <li>Conocimiento en manejo de recaudaciones por máquina</li>
              <li>Capacidad para elaborar reportes claros y transparentes</li>
              <li>Atención al detalle y confidencialidad</li>
            </ul>
          </div>
          <div class="requisito-card">
            <h3>🧠 Administrador del sistema</h3>
            <ul>
              <li>Conocimientos técnicos y administrativos del sistema web</li>
              <li>Capacidad de supervisión de usuarios y asignación de funciones</li>
              <li>Gestión de reportes y resolución de incidencias internas</li>
              <li>Comunicación efectiva con todos los perfiles</li>
            </ul>
          </div>
        </div>
      </section>
    </div>
  `,
  styles: [`
    .registro-container {
      max-width: 600px;
      margin: 2rem auto;
      padding: 1rem;
    }
    
    .full-width {
      width: 100%;
      margin-bottom: 1rem;
    }
    
    .form-actions {
      display: flex;
      gap: 1rem;
      justify-content: center;
      margin-top: 1.5rem;
    }
    
    .file-upload {
      margin: 1rem 0;
    }
    
    .file-label {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 1rem;
      background: #f0f0f0;
      border-radius: 4px;
      cursor: pointer;
    }
    
    .file-name {
      margin-left: 1rem;
      color: #666;
      font-size: 0.9rem;
    }
    
    .requisitos-postulante {
      margin-top: 3rem;
      padding: 2rem;
      background: white;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    .requisitos-postulante h2 {
      text-align: center;
      color: #2c3e50;
      margin-bottom: 1.5rem;
    }
    
    .requisitos-tipos {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
    }
    
    .requisito-card {
      background: #f8f9fa;
      border-radius: 8px;
      padding: 1rem;
    }
    
    .requisito-card h3 {
      color: #4f6bed;
      margin-bottom: 0.5rem;
    }
    
    .requisito-card ul {
      padding-left: 1.5rem;
    }
    
    .requisito-card li {
      margin-bottom: 0.5rem;
      color: #555;
    }
    
    @media (max-width: 768px) {
      .registro-container {
        margin: 1rem;
      }
      
      .requisitos-tipos {
        grid-template-columns: 1fr;
      }
    }
  `]
})
export class RegistroComponent {
  private fb = inject(FormBuilder);
  private authService = inject(AuthService);
  private router = inject(Router);
  private toastr = inject(ToastrService);
  
  registroForm: FormGroup;
  loading = false;
  hidePassword = true;
  fileName: string = '';
  
  constructor() {
    this.registroForm = this.fb.group({
      nombre: ['', [Validators.required]],
      apellido: ['', [Validators.required]],
      ci: ['', [Validators.required, Validators.pattern(/^\d{10}$/)]],
      email: ['', [Validators.required, Validators.email]],
      tipo: ['Logistica', [Validators.required]],
      especialidad: [''],
      usuario_asignado: ['', [Validators.required, Validators.maxLength(15)]],
      contrasena: ['', [Validators.required, Validators.minLength(8)]]
    });
    
    // Actualizar validación de especialidad cuando cambia el tipo
    this.registroForm.get('tipo')?.valueChanges.subscribe(tipo => {
      const especialidadControl = this.registroForm.get('especialidad');
      if (tipo === 'Tecnico') {
        especialidadControl?.setValidators([Validators.required]);
      } else {
        especialidadControl?.clearValidators();
        especialidadControl?.setValue('');
      }
      especialidadControl?.updateValueAndValidity();
    });
  }
  
  onFileSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
      const file = input.files[0];
      if (file.type === 'application/pdf') {
        this.fileName = file.name;
        this.toastr.success(`Currículum "${file.name}" seleccionado correctamente`);
      } else {
        this.toastr.error('Por favor, seleccione un archivo PDF válido');
        input.value = '';
        this.fileName = '';
      }
    }
  }
  
  onSubmit(): void {
    if (this.registroForm.invalid) return;
    
    if (!confirm('¿Seguro desea registrarse?')) return;
    
    this.loading = true;
    const formData = this.registroForm.value;
    
    this.authService.register(formData).subscribe({
      next: (response) => {
        if (response.success) {
          this.toastr.success(
            'No olvide su usuario y contraseña que acaba de registrar. Pronto nos pondremos en contacto con usted.',
            '¡Registro exitoso!'
          );
          setTimeout(() => this.router.navigate(['/auth/login']), 3000);
        } else {
          this.toastr.error(response.message || 'Error al registrar usuario', 'Error');
        }
        this.loading = false;
      },
      error: (error) => {
        this.toastr.error(error.message || 'Error de conexión con el servidor', 'Error');
        this.loading = false;
      }
    });
  }
}