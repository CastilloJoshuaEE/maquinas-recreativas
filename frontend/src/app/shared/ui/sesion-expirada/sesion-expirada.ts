/**
 * @fileoverview Componente de Sesión Expirada
 * @description Modal que informa al usuario que su sesión ha expirado
 * @component SesionExpiradaComponent
 */

import { Component, Inject, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatDialogRef, MAT_DIALOG_DATA, MatDialogModule } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { Router } from '@angular/router';
import { AuthService } from '@core/services/auth';

export interface SesionExpiradaData {
  mensaje?: string;
  tiempoRestante?: number;
}

@Component({
  selector: 'app-sesion-expirada',
  standalone: true,
  imports: [CommonModule, MatDialogModule, MatButtonModule, MatIconModule],
  templateUrl: './sesion-expirada.html',
  styleUrls: ['./sesion-expirada.css']
})
export class SesionExpiradaComponent {
  private dialogRef = inject(MatDialogRef<SesionExpiradaComponent>);
  private router = inject(Router);
  private authService = inject(AuthService);
  
  constructor(@Inject(MAT_DIALOG_DATA) public data: SesionExpiradaData = {}) {}
  
  irALogin(): void {
    // Limpiar sesión
    this.authService.clearSession();
    this.dialogRef.afterClosed();
    // Cerrar el modal
    this.dialogRef.close();
    
    // Redirigir al login
    this.router.navigate(['/auth/login']);
  }
}