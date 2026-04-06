/**
 * @fileoverview Dashboard de Administrador
 * @description Panel principal del administrador
 * @component DashboardAdminComponent
 */

import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header';
import { AuthService } from '@core/services/auth';

@Component({
  selector: 'app-dashboard-admin',
  standalone: true,
  imports: [CommonModule, MatCardModule, AdminHeaderComponent],
  templateUrl: './dashboard-admin.html',
  styleUrls: ['./dashboard-admin.css']
})
export class DashboardAdminComponent {
  private router = inject(Router);
  private authService = inject(AuthService);
  
  currentUser = this.authService.getCurrentUser();
  showReportes = false;
  showNotificaciones = false;
  showChat = false;
  
  irAGestionUsuarios(): void {
    this.router.navigate(['/admin/gestion-usuarios']);
  }
  
  irARegistrarUsuario(): void {
    this.router.navigate(['/admin/registrar-usuario']);
  }
  
  irAConsultarUsuarios(): void {
    this.router.navigate(['/admin/consultar-usuarios']);
  }
  
  regresar(): void {
    this.router.navigate(['/']);
  }
}