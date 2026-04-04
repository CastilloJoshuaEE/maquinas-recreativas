/**
 * @fileoverview Gestión de Recaudación
 * @description Página principal para gestionar recaudaciones (registrar y consultar)
 * @component GestionRecaudacionComponent
 */

import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header.component';

@Component({
  selector: 'app-gestion-recaudacion',
  standalone: true,
  imports: [CommonModule, MatCardModule, MatButtonModule, MatIconModule, AdminHeaderComponent],
  templateUrl: './gestion-recaudacion.html',
  styleUrls: ['./gestion-recaudacion.css']
})
export class GestionRecaudacionComponent {
  private router = inject(Router);
  
  regresar(): void { this.router.navigate(['/contabilidad/dashboard']); }
  irARegistrarRecaudacion(): void { this.router.navigate(['/contabilidad/registrar-recaudacion']); }
  irAConsultarRecaudaciones(): void { this.router.navigate(['/contabilidad/consultar-recaudaciones']); }
}