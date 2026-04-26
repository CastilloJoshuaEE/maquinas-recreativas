/**
 * @fileoverview Componente de Carga Genérico
 * @description Muestra un spinner de carga con mensaje personalizable y overlay opcional
 * @component LoadingSpinnerComponent
 */

import { Component, Input } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';

@Component({
  selector: 'app-loading-spinner',
  standalone: true,
  imports: [CommonModule, MatProgressSpinnerModule],
  templateUrl: './loading-spinner.html',
  styleUrls: ['./loading-spinner.css']
})
export class LoadingSpinnerComponent {
  /** Mensaje a mostrar debajo del spinner */
  @Input() message: string = 'Cargando...';
  
  /** Diámetro del spinner en píxeles */
  @Input() diameter: number = 50;
  
  /** Grosor del spinner */
  @Input() strokeWidth: number = 4;
  
  /** Si el spinner debe ocupar toda la pantalla con overlay */
  @Input() fullScreen: boolean = false;
  
  /** Color del spinner (primary, accent, warn) */
  @Input() color: 'primary' | 'accent' | 'warn' = 'primary';
  
  /** Si muestra el mensaje */
  @Input() showMessage: boolean = true;
  
  /** Tamaño del texto del mensaje */
  @Input() messageSize: 'small' | 'normal' | 'large' = 'normal';
}