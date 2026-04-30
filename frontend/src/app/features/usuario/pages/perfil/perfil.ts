/**
 * @fileoverview Componente de Perfil de Usuario
 * @description Muestra la información del perfil del usuario actual
 * @component PerfilComponent
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router, RouterLink } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header';
import { AuthService } from '@core/services/auth';
import { UserService } from '@core/services/user';
import { User } from '@core/models/user.model';

@Component({
  selector: 'app-perfil',
  standalone: true,
  imports: [CommonModule, MatCardModule, MatButtonModule, MatIconModule, MatProgressSpinnerModule, AdminHeaderComponent],
  templateUrl: './perfil.html',
  styleUrls: ['./perfil.css']
})
export class PerfilComponent implements OnInit {
  private router = inject(Router);
  private authService = inject(AuthService);
  private userService = inject(UserService);
  private snackBar = inject(MatSnackBar);
  
  usuario: User | null = null;
  loading = true;
  error = '';
  
  ngOnInit(): void { this.cargarPerfil(); }
  
cargarPerfil(): void {
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
        // Normalizar: asegurar que Especialidad tenga el valor correcto
        if (usuario.especialidad && !usuario.Especialidad) {
          usuario.Especialidad = usuario.especialidad;
        }
        this.usuario = usuario; 
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
  private registrarActividad(): void {
    const currentUser = this.authService.getCurrentUser();
    if (currentUser?.id) { this.userService.registrarActividad(currentUser.id, 'El usuario visualizó su perfil').subscribe(); }
  }
  
  editarPerfil(): void { this.router.navigate(['/usuario/actualizar-perfil']); }
  
  cerrarSesion(): void {
    if (confirm('¿Está seguro de cerrar sesión?')) {
      this.authService.logout().subscribe({
        next: () => { this.snackBar.open('Sesión cerrada correctamente', 'Cerrar', { duration: 3000 }); },
        error: () => { this.snackBar.open('Error al cerrar sesión', 'Cerrar', { duration: 3000 }); }
      });
    }
  }
  
regresar(): void {
  const user = this.authService.getCurrentUser();
  if (user) {
    const userType = user.tipo === 'Técnico' ? 'Tecnico' : user.tipo;
    switch (userType) {
      case 'Logistica':
        this.router.navigate(['/logistica/dashboard']);
        break;
      case 'Tecnico':
        // Obtener especialidad normalizada
        const especialidad = user.Especialidad || user.especialidad || '';
        let ruta = 'ensamblador';
        if (especialidad === 'Comprobador') ruta = 'comprobador';
        if (especialidad === 'Mantenimiento') ruta = 'mantenimiento';
        this.router.navigate([`/tecnico/${ruta}`]);
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
  } else {
    this.router.navigate(['/']);
  }
}
}