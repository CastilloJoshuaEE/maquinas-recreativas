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
import { ToastrService } from 'ngx-toastr';
import { AuthService } from '@core/services/auth';
import { UserService } from '@core/services/user';
import { TECNICO_ESPECIALIDADES } from '@core/constants/app.constants';
import { LoadingSpinnerComponent } from '@shared/ui/loading-spinner/loading-spinner';

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
    LoadingSpinnerComponent  // ← Importar el componente de carga
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
  loginError: string = '';  // ← Variable para mostrar error en el formulario
  
  constructor() {
    this.loginForm = this.fb.group({
      usuario_asignado: ['', [Validators.required, Validators.maxLength(15)]],
      contrasena: ['', [Validators.required]]
    });
  }
  
onSubmit(): void {
    if (this.loginForm.invalid) {
        Object.keys(this.loginForm.controls).forEach(key => {
            this.loginForm.get(key)?.markAsTouched();
        });
        return;
    }
    
    this.loading = true;
    this.loginError = '';
    const credentials = this.loginForm.value;
    
    this.authService.login(credentials).subscribe({
        next: (response: any) => {
            this.loading = false;
            
            // Verificar que response existe
            if (!response) {
                this.loginError = 'Error de conexión con el servidor';
                this.toastr.error(this.loginError, 'Error');
                return;
            }
            
            // Verificar que tiene la propiedad success
            if (response.success && response.usuario) {
                this.toastr.success('Inicio de sesión exitoso', 'Bienvenido');
                this.redirigirPorRol(response.usuario);
            } else {
                this.loginError = response.message || 'Credenciales incorrectas';
                this.toastr.error(this.loginError, 'Error de autenticación');
            }
        },
        error: (error) => {
            this.loading = false;
            console.error('Error detallado:', error);
            
            // Extraer mensaje de error correctamente
            let errorMessage = 'Error al conectar con el servidor';
            
            if (error.error) {
                // Si el error es del backend con estructura { success: false, message: ... }
                if (typeof error.error === 'object' && error.error.message) {
                    errorMessage = error.error.message;
                } 
                // Si es string
                else if (typeof error.error === 'string') {
                    errorMessage = error.error;
                }
            } else if (error.message) {
                errorMessage = error.message;
            }
            
            this.loginError = errorMessage;
            this.toastr.error(errorMessage, 'Error');
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
        const especialidad = usuario.Especialidad || usuario.especialidad || '';
        
        let rutaEspecialidad = '';
        switch (especialidad) {
          case TECNICO_ESPECIALIDADES.ENSAMBLADOR:
            rutaEspecialidad = 'ensamblador';
            break;
          case TECNICO_ESPECIALIDADES.COMPROBADOR:
            rutaEspecialidad = 'comprobador';
            break;
          case TECNICO_ESPECIALIDADES.MANTENIMIENTO:
            rutaEspecialidad = 'mantenimiento';
            break;
          default:
            rutaEspecialidad = 'ensamblador';
        }
        
        this.router.navigate([`/tecnico/${rutaEspecialidad}`]);
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