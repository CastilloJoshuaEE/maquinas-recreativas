/**
 * @fileoverview Componente de Actualización de Usuario
 * @description Permite actualizar el nombre de usuario mediante email
 * @component ActualizarUsuarioComponent
 */

import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { ToastrService } from 'ngx-toastr';
import { UserService } from '@core/services/user';

@Component({
  selector: 'app-actualizar-usuario',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    RouterLink,
    MatCardModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule
  ],
  templateUrl: './actualizar-usuario.html',
  styleUrls: ['./actualizar-usuario.css']
})
export class ActualizarUsuarioComponent {
  private fb = inject(FormBuilder);
  private userService = inject(UserService);
  private router = inject(Router);
  private toastr = inject(ToastrService);
  
  actualizarForm: FormGroup;
  loading = false;
  
  constructor() {
    this.actualizarForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      usuario_asignado: ['', [Validators.required, Validators.maxLength(15)]]
    });
  }
  
  onSubmit(): void {
    if (this.actualizarForm.invalid) return;
    
    if (!confirm('¿Está seguro de guardar los cambios?')) return;
    
    this.loading = true;
    const { email, usuario_asignado } = this.actualizarForm.value;
    
    this.userService.recoverUsername(email, usuario_asignado).subscribe({
      next: (response) => {
        if (response.success) {
          this.toastr.success('Usuario actualizado correctamente', 'Éxito');
          setTimeout(() => this.router.navigate(['/auth/login']), 2000);
        } else {
          this.toastr.error(response.message || 'Error al actualizar el usuario', 'Error');
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