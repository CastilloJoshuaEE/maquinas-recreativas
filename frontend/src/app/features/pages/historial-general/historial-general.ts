/**
 * @fileoverview Página de Historial General
 * @description Muestra el historial completo de actividades del sistema
 * @component HistorialGeneralComponent
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header';
import { HistorialMaquinaComponent } from '@features/tecnico/pages/ui/historial-maquina/historial-maquina';
import { AuthService } from '@core/services/auth';

@Component({
  selector: 'app-historial-general',
  standalone: true,
  imports: [CommonModule, MatCardModule, MatButtonModule, MatIconModule, AdminHeaderComponent, HistorialMaquinaComponent],
  templateUrl: './historial-general.html',
  styleUrls: ['./historial-general.css']
})
export class HistorialGeneralComponent implements OnInit {
  private router = inject(Router);
  private authService = inject(AuthService);
  
  mostrarHistorial = false;
  esDenegado = false;
  esAdminOrLogistica = false;
  
  ngOnInit(): void {
    const currentUser = this.authService.getCurrentUser();
    if (!currentUser) { this.esDenegado = true; return; }
    this.esAdminOrLogistica = currentUser.tipo === 'Administrador' || currentUser.tipo === 'Logistica';
    if (!this.esAdminOrLogistica) { this.esDenegado = true; }
  }
  
  regresar(): void {
    const currentUser = this.authService.getCurrentUser();
    if (currentUser) {
      const userType = currentUser.tipo === 'Técnico' ? 'Tecnico' : currentUser.tipo;
      switch (userType) {
        case 'Logistica': this.router.navigate(['/logistica/dashboard']); break;
        case 'Tecnico': this.router.navigate([`/tecnico/${currentUser.Especialidad?.toLowerCase() || 'ensamblador'}`]); break;
        case 'Contabilidad': this.router.navigate(['/contabilidad/dashboard']); break;
        case 'Administrador': this.router.navigate(['/admin/dashboard']); break;
        default: this.router.navigate(['/auth/login']);
      }
    } else { this.router.navigate(['/auth/login']); }
  }
}