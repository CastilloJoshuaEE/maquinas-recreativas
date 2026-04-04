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
import { API_ENDPOINTS } from '@core/constants/app.constants';

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
  templateUrl: './registrar-usuario.html',
  styleUrls: ['./registrar-usuario.css']
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
    
    delete userData.confirmar_contrasena;
    
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