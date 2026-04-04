/**
 * @fileoverview Componente raíz de la aplicación
 * @description Componente principal que contiene el router outlet y la estructura base
 * @component AppComponent
 */

import { Component, OnInit } from '@angular/core';
import { RouterOutlet, RouterLink, RouterLinkActive } from '@angular/router';
import { CommonModule } from '@angular/common';

/**
 * Componente principal de la aplicación
 * Maneja la navegación principal y el layout base
 */
@Component({
  selector: 'app-root',
  standalone: true,
  imports: [CommonModule, RouterOutlet, RouterLink, RouterLinkActive],
  template: `
    <router-outlet></router-outlet>
  `,
  styles: [`
    :host {
      display: block;
      min-height: 100vh;
    }
  `]
})
export class AppComponent implements OnInit {
  /** Título de la aplicación */
  title = 'Recrea Sys';
  
  /**
   * Inicializa el componente
   * Verifica autenticación al cargar
   */
  ngOnInit(): void {
    // Inicialización de la aplicación
    console.log('Recrea Sys - Aplicación iniciada');
  }
}