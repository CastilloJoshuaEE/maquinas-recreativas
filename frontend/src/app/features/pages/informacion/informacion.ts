/**
 * @fileoverview Página de Información del Sistema
 * @description Muestra información sobre la empresa, procesos y funcionalidades del sistema
 * @component InformacionComponent
 */

import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-informacion',
  standalone: true,
  imports: [CommonModule, RouterLink, MatCardModule, MatButtonModule, MatIconModule],
  templateUrl: './informacion.html',
  styleUrls: ['./informacion.css']
})
export class InformacionComponent {}