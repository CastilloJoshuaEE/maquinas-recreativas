/**
 * @fileoverview Componente de Actualización de Perfil
 * @description Permite al usuario actualizar su información personal (solo campos editables según rol)
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
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header';
import { AuthService } from '@core/services/auth';
import { UserService } from '@core/services/user';
import { User } from '@core/models/user.model';
import { VALIDATION_PATTERNS } from '@core/constants/app.constants';

@Component({
  selector: 'app-actualizar-perfil',
  standalone: true,
  imports: [
    CommonModule, ReactiveFormsModule, MatCardModule, MatFormFieldModule,
    MatInputModule, MatSelectModule, MatButtonModule, MatIconModule,
    MatProgressSpinnerModule, AdminHeaderComponent
  ],
  templateUrl: './actualizar-perfil.html',
  styleUrls: ['./actualizar-perfil.css']
})
export class ActualizarPerfilComponent implements OnInit {
  private fb = inject(FormBuilder);
  private router = inject(Router);
  private authService = inject(AuthService);
  private userService = inject(UserService);
  private snackBar = inject(MatSnackBar);
  
  perfilForm!: FormGroup;
  usuario: User | null = null;
  loading = true;
  submitting = false;
  success = false;
  error = '';
  hidePassword = true;
  
  // Determinar si el usuario es técnico
  esTecnico = false;
  especialidad = '';
  
  ngOnInit(): void { this.cargarPerfil(); }
  
  private cargarPerfil(): void {
    this.loading = true;
    this.error = '';
    const currentUser = this.authService.getCurrentUser();
    if (!currentUser || !currentUser.id) {
      this.error = 'Usuario no autenticado';
      this.loading = false;
      return;
    }
    
    this.userService.getProfile(currentUser.id).subscribe({
      next: (usuario) => {
        if (usuario) { 
          this.usuario = usuario; 
          this.esTecnico = usuario.tipo === 'Tecnico';
          
          // Normalizar especialidad: puede venir como Especialidad o especialidad
          this.especialidad = usuario.Especialidad || usuario.especialidad || '';
          
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
    
    // Campos deshabilitados (solo lectura - información del sistema)
    this.perfilForm = this.fb.group({
      usuario_asignado: [{ value: this.usuario.usuario_asignado, disabled: true }],
      tipo: [{ value: this.usuario.tipo, disabled: true }],
      estado: [{ value: this.usuario.estado, disabled: true }],
      especialidad: [{ value: this.especialidad, disabled: true }], // Solo lectura para técnicos
      
      // Campos editables por el usuario
      nombre: [this.usuario.nombre, [Validators.required, Validators.minLength(2), Validators.maxLength(50)]],
      apellido: [this.usuario.apellido, [Validators.required, Validators.minLength(2), Validators.maxLength(50)]],
      ci: [this.usuario.ci, [Validators.required, Validators.pattern(VALIDATION_PATTERNS.CI)]],
      email: [this.usuario.email, [Validators.required, Validators.email]],
      contrasena: ['', [Validators.minLength(8)]]
    });
  }
  
  private registrarActividad(): void {
    const currentUser = this.authService.getCurrentUser();
    if (currentUser?.id) {
      this.userService.registrarActividad(currentUser.id, 'El usuario accedió a actualizar su perfil').subscribe();
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
    
    const updateData: any = {
      id: currentUser?.id,
      nombre: formValue.nombre,
      apellido: formValue.apellido,
      email: formValue.email,
      ci: formValue.ci,
      tipo: formValue.tipo,
      estado: formValue.estado,
      especialidad: this.especialidad || null  // Mantener la especialidad original
    };
    
    if (formValue.contrasena && formValue.contrasena.trim() !== '') {
      updateData.contrasena = formValue.contrasena;
    }
    
    this.userService.updateProfile(updateData).subscribe({
      next: (response) => {
        if (response.success) {
          this.success = true;
          this.snackBar.open('Perfil actualizado correctamente', 'Cerrar', { duration: 3000 });
          
          // Actualizar localStorage con los nuevos datos
          if (currentUser) {
            const updatedUser = { 
              ...currentUser, 
              nombre: updateData.nombre, 
              apellido: updateData.apellido, 
              email: updateData.email,
              ci: updateData.ci
            };
            localStorage.setItem('user', JSON.stringify(updatedUser));
          }
          
          setTimeout(() => this.router.navigate(['/usuario/perfil']), 2000);
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
  
  soloNumeros(event: KeyboardEvent): boolean {
    const charCode = event.charCode;
    if (charCode >= 48 && charCode <= 57) {
      return true;
    } else {
      event.preventDefault();
      return false;
    }
  }
  
  regresar(): void { 
    this.router.navigate(['/usuario/perfil']); 
  }
}