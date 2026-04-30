/**
 * @fileoverview Componente de Informe de Recaudación
 * @description Componente reutilizable para mostrar informes de recaudación
 * @component InformeRecaudacionComponent
 */

import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { InformeRecaudacion, Recaudacion } from '@core/models/recaudacion.model';
import { Componente } from '@core/models/componente.model';

@Component({
  selector: 'app-informe-recaudacion',
  standalone: true,
  imports: [CommonModule, MatCardModule, MatButtonModule, MatIconModule],
  templateUrl: './informe-recaudacion.html',
  styleUrls: ['./informe-recaudacion.css']
})
export class InformeRecaudacionComponent {
  @Input() informe!: InformeRecaudacion;
  @Input() recaudacion: Recaudacion | null = null;
  @Input() componentes: Componente[] = [];
  @Output() onPrint = new EventEmitter<void>();
  @Output() onClose = new EventEmitter<void>();
  
  get totalComponentes(): number {
    return this.componentes.reduce((sum, comp) => sum + (comp.precio || 0), 0);
  }
}