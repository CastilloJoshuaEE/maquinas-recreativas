/**
 * @fileoverview Componente de Recuperación de Usuario
 * @description Permite a los usuarios recuperar su nombre de usuario mediante email
 * @component RecuperarUsuarioComponent
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
import { UserService } from '@core/services/user.service';

@Component({
  selector: 'app-recuperar-usuario',
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
  templateUrl: './recuperar-usuario.html',
  styleUrls: ['./recuperar-usuario.css']
})
export class RecuperarUsuarioComponent {
  private fb = inject(FormBuilder);
  private userService = inject(UserService);
  private router = inject(Router);
  private toastr = inject(ToastrService);
  
  recuperacionForm: FormGroup;
  loading = false;
  
  constructor() {
    this.recuperacionForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      nuevo_usuario: ['', [Validators.required, Validators.maxLength(15)]]
    });
  }
  
  onSubmit(): void {
    if (this.recuperacionForm.invalid) return;
    
    this.loading = true;
    const { email, nuevo_usuario } = this.recuperacionForm.value;
    
    this.userService.recoverUsername(email, nuevo_usuario).subscribe({
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