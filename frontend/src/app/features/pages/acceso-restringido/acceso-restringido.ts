/**
 * @fileoverview Página de Acceso Restringido
 * @description Muestra un mensaje para usuarios con cuenta inhabilitada o pendiente
 * @component AccesoRestringidoComponent
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { GestionReportesComponent } from '@features/reportes/pages/gestion-reportes/gestion-reportes';

@Component({
  selector: 'app-acceso-restringido',
  standalone: true,
  imports: [CommonModule, MatCardModule, MatButtonModule, MatIconModule, GestionReportesComponent],
  templateUrl: './acceso-restringido.html',
  styleUrls: ['./acceso-restringido.css']
})
export class AccesoRestringidoComponent implements OnInit {
  private router = inject(Router);
  
  userData: any = null;
  mensajeMotivo = '';
  mostrarContacto = true;
  showReportForm = false;
  
  ngOnInit(): void {
    const navigation = this.router.getCurrentNavigation();
    const state = navigation?.extras.state as any;
    
    if (state) {
      this.userData = state.userData;
      if (state.isDisabledUser) {
        this.mensajeMotivo = 'Su cuenta ha sido inhabilitada. Por favor contacte al administrador para reactivarla.';
      } else if (state.userData?.estado === 'Pendiente de asignacion') {
        this.mensajeMotivo = 'Su cuenta está pendiente de asignación. Por favor espere a que un administrador active su cuenta.';
        this.mostrarContacto = true;
      }
    } else {
      const storedUser = localStorage.getItem('user');
      if (storedUser) {
        this.userData = JSON.parse(storedUser);
        if (this.userData.estado === 'Inhabilitado') {
          this.mensajeMotivo = 'Su cuenta ha sido inhabilitada. Por favor contacte al administrador para reactivarla.';
        } else if (this.userData.estado === 'Pendiente de asignacion') {
          this.mensajeMotivo = 'Su cuenta está pendiente de asignación. Por favor espere a que un administrador active su cuenta.';
        }
      } else {
        this.volverLogin();
      }
    }
  }
  
  volverLogin(): void { this.router.navigate(['/auth/login']); }
  contactarAdmin(): void { this.showReportForm = true; }
}