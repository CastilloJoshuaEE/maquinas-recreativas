/**
 * @fileoverview Componente de Login
 * @description Página de inicio de sesión con validación y redirección por rol
 * @component LoginComponent
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
import { AuthService } from '@core/services/auth.service';
import { UserService } from '@core/services/user.service';

@Component({
  selector: 'app-login',
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
  templateUrl: './login.html',
  styleUrls: ['./login.css']
})
export class LoginComponent {
  private fb = inject(FormBuilder);
  private authService = inject(AuthService);
  private userService = inject(UserService);
  private router = inject(Router);
  private toastr = inject(ToastrService);
  
  loginForm: FormGroup;
  loading = false;
  hidePassword = true;
  
  constructor() {
    this.loginForm = this.fb.group({
      usuario_asignado: ['', [Validators.required, Validators.maxLength(15)]],
      contrasena: ['', [Validators.required]]
    });
  }
  
  onSubmit(): void {
    if (this.loginForm.invalid) return;
    
    this.loading = true;
    const credentials = this.loginForm.value;
    
    this.authService.login(credentials).subscribe({
      next: (response) => {
        if (response.success && response.usuario) {
          this.toastr.success('Inicio de sesión exitoso', 'Bienvenido');
          this.redirigirPorRol(response.usuario);
        } else {
          this.toastr.error(response.message || 'Credenciales incorrectas', 'Error');
        }
        this.loading = false;
      },
      error: (error) => {
        this.toastr.error(error.message || 'Error al iniciar sesión', 'Error');
        this.loading = false;
      }
    });
  }
  
  private redirigirPorRol(usuario: any): void {
    const userType = usuario.tipo === 'Técnico' ? 'Tecnico' : usuario.tipo;
    
    switch (userType) {
      case 'Logistica':
        this.router.navigate(['/logistica/dashboard']);
        break;
      case 'Tecnico':
        if (usuario.Especialidad) {
          this.router.navigate([`/tecnico/${usuario.Especialidad.toLowerCase()}`]);
        } else {
          this.router.navigate(['/tecnico/ensamblador']);
        }
        break;
      case 'Contabilidad':
        this.router.navigate(['/contabilidad/dashboard']);
        break;
      case 'Administrador':
        this.router.navigate(['/admin/dashboard']);
        break;
      default:
        this.router.navigate(['/']);
    }
  }
  
  irARegistro(): void {
    this.router.navigate(['/auth/register']);
  }
  
  irAInformacion(): void {
    this.router.navigate(['/informacion']);
  }
}