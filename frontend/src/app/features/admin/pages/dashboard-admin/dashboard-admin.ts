/**
 * @fileoverview Dashboard de Administrador — con botón de Enviar Email
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
  private router      = inject(Router);
  private authService = inject(AuthService);

  currentUser = this.authService.getCurrentUser();

  irAGestionUsuarios():  void { this.router.navigate(['/admin/gestion-usuarios']); }
  irARegistrarUsuario(): void { this.router.navigate(['/admin/registrar-usuario']); }
  irAConsultarUsuarios():void { this.router.navigate(['/admin/consultar-usuarios']); }
  irAEnviarEmail():      void { this.router.navigate(['/admin/enviar-email']); }
}