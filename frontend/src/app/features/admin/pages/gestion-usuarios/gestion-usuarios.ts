/**
 * @fileoverview Gestión de Usuarios
 * @description Página principal para la gestión de usuarios del sistema
 * @component GestionUsuariosComponent
 */

import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header';
import { UserService } from '@core/services/user';

@Component({
  selector: 'app-gestion-usuarios',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatButtonModule,
    AdminHeaderComponent
  ],
  templateUrl: './gestion-usuarios.html',
  styleUrls: ['./gestion-usuarios.css']
})
export class GestionUsuariosComponent {
  private router = inject(Router);
  private userService = inject(UserService);
  
  constructor() {
    this.registrarActividad();
  }
  
  private registrarActividad(): void {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    if (user.id) {
      this.userService.registrarActividad(user.id, 'El usuario estuvo en la gestión de usuarios').subscribe();
    }
  }
  
  regresar(): void {
    this.router.navigate(['/admin/dashboard']);
  }
  
  irARegistrarUsuario(): void {
    this.router.navigate(['/admin/registrar-usuario']);
  }
  
  irAConsultarUsuarios(): void {
    this.router.navigate(['/admin/consultar-usuarios']);
  }
}