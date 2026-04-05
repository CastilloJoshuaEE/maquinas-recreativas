/**
 * @fileoverview Página de No Autorizado (403)
 * @description Muestra un mensaje cuando un usuario intenta acceder a una página sin permisos
 * @component NoAutorizadoComponent
 */

import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-no-autorizado',
  standalone: true,
  imports: [CommonModule, RouterLink, MatCardModule, MatButtonModule, MatIconModule],
  templateUrl: './no-autorizado.html',
  styleUrls: ['./no-autorizado.css']
})
export class NoAutorizadoComponent {
  goBack(): void { window.history.back(); }
}