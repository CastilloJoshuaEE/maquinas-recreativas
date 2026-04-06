/**
 * @fileoverview Componente de Recuperación de Contraseña
 * @description Permite a los usuarios recuperar su contraseña mediante email
 * @component RecuperarContrasenaComponent
 */

import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators, AbstractControl, ValidationErrors } from '@angular/forms';
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
  selector: 'app-recuperar-contrasena',
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
  templateUrl: './recuperar-contrasena.html',
  styleUrls: ['./recuperar-contrasena.css']
})
export class RecuperarContrasenaComponent {
  private fb = inject(FormBuilder);
  private userService = inject(UserService);
  private router = inject(Router);
  private toastr = inject(ToastrService);
  
  recuperacionForm: FormGroup;
  loading = false;
  hidePassword = true;
  hideConfirmPassword = true;
  
  constructor() {
    this.recuperacionForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      nueva_contrasena: ['', [Validators.required, Validators.minLength(8)]],
      repetir_contrasena: ['', [Validators.required]]
    }, { validators: this.passwordMatchValidator });
  }
  
  private passwordMatchValidator(group: AbstractControl): ValidationErrors | null {
    const password = group.get('nueva_contrasena')?.value;
    const confirm = group.get('repetir_contrasena')?.value;
    return password === confirm ? null : { passwordMismatch: true };
  }
  
  onSubmit(): void {
    if (this.recuperacionForm.invalid) return;
    
    this.loading = true;
    const { email, nueva_contrasena } = this.recuperacionForm.value;
    
    this.userService.recoverPassword(email, nueva_contrasena).subscribe({
      next: (response) => {
        if (response.success) {
          this.toastr.success('Contraseña actualizada correctamente', 'Éxito');
          setTimeout(() => this.router.navigate(['/auth/login']), 2000);
        } else {
          this.toastr.error(response.message || 'Error al actualizar la contraseña', 'Error');
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