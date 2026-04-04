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
  imports: [
    CommonModule,
    RouterLink,
    MatCardModule,
    MatButtonModule,
    MatIconModule
  ],
  template: `
    <div class="informacion-page">
      <header>
        <h1>Recrea Sys</h1>
        <nav>
          <a routerLink="/auth/login">Volver al inicio</a>
        </nav>
      </header>

      <div class="informacion-container">
        <mat-card class="informacion-card">
          <mat-card-content>
            <h1>Máquinas Recreativas</h1>

            <!-- Proceso de Producción -->
            <section class="seccion-informacion">
              <h2>
                <mat-icon>factory</mat-icon>
                Proceso de Producción
              </h2>
              <p>
                La compañía para la que está desarrollada esta aplicación web se 
                especializa en el montaje, distribución y recaudación de máquinas 
                recreativas, las cuales poseen los siguientes componentes:
              </p>
              <ul>
                <li>Placas con la programación de la máquina</li>
                <li>Carcasas para las máquinas</li>
              </ul>
              <p>
                El proceso de ensamblaje es realizado por técnicos calificados, 
                seguido de una rigurosa fase de control de calidad.
              </p>
            </section>

            <!-- Distribución a Comercios -->
            <section class="seccion-informacion">
              <h2>
                <mat-icon>local_shipping</mat-icon>
                Distribución a Comercios
              </h2>
              <div class="grid-distribucion">
                <div class="tipo-comercio">
                  <h3>Minoristas</h3>
                  <p>(Ej: bares, pequeños establecimientos)</p>
                  <ul>
                    <li>Colocación de pocas máquinas</li>
                    <li>Pago mensual fijo al establecimiento</li>
                    <li>Recaudación completa para la compañía</li>
                  </ul>
                </div>
                <div class="tipo-comercio">
                  <h3>Mayoristas</h3>
                  <p>(Ej: salas recreativas, grandes establecimientos)</p>
                  <ul>
                    <li>Colocación de múltiples máquinas</li>
                    <li>Porcentaje de recaudación pactado</li>
                    <li>Renegociación mensual de términos</li>
                  </ul>
                </div>
              </div>
            </section>

            <!-- Mantenimiento -->
            <section class="seccion-informacion">
              <h2>
                <mat-icon>build</mat-icon>
                Mantenimiento
              </h2>
              <p>Nuestro sistema asigna técnicos según:</p>
              <ul>
                <li>Carga de trabajo equilibrada (menor cantidad de reparaciones)</li>
              </ul>
              <p>
                Las máquinas con fallos recurrentes son retiradas y sus piezas 
                útiles reutilizadas.
              </p>
            </section>

            <!-- Reportes y Análisis -->
            <section class="seccion-informacion">
              <h2>
                <mat-icon>assessment</mat-icon>
                Reportes y Análisis
              </h2>
              <p>Generamos informes detallados al final de cada período:</p>
              <ul>
                <li>Reportes individualizados por comercio</li>
                <li>Histórico de recaudaciones mensuales</li>
              </ul>
            </section>

            <!-- Ciclo de Vida de Máquinas -->
            <section class="seccion-informacion">
              <h2>
                <mat-icon>cycle</mat-icon>
                Ciclo de Vida de las Máquinas
              </h2>
              <div class="ciclo-vida">
                <div class="etapa">
                  <mat-icon>handyman</mat-icon>
                  <span>Ensamblaje</span>
                </div>
                <mat-icon class="flecha">arrow_forward</mat-icon>
                <div class="etapa">
                  <mat-icon>check_circle</mat-icon>
                  <span>Comprobación</span>
                </div>
                <mat-icon class="flecha">arrow_forward</mat-icon>
                <div class="etapa">
                  <mat-icon>local_shipping</mat-icon>
                  <span>Distribución</span>
                </div>
                <mat-icon class="flecha">arrow_forward</mat-icon>
                <div class="etapa">
                  <mat-icon>attach_money</mat-icon>
                  <span>Recaudación</span>
                </div>
                <mat-icon class="flecha">arrow_forward</mat-icon>
                <div class="etapa">
                  <mat-icon>build</mat-icon>
                  <span>Mantenimiento</span>
                </div>
              </div>
            </section>

            <div class="boton-volver-container">
              <a mat-raised-button color="primary" routerLink="/auth/login" class="boton-volver">
                <mat-icon>arrow_back</mat-icon>
                Volver al inicio
              </a>
            </div>
          </mat-card-content>
        </mat-card>
      </div>
    </div>
  `,
  styles: [`
    .informacion-page {
      min-height: 100vh;
      background: linear-gradient(135deg, #07224c 0%, #124258 50%, #3b4a66 100%);
    }
    
    header {
      background: rgba(255, 255, 255, 0.95);
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: fixed;
      width: 100%;
      top: 0;
      z-index: 100;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    header h1 {
      color: #2c3e50;
      margin: 0;
      font-size: 1.5rem;
    }
    
    header nav a {
      color: #4f6bed;
      text-decoration: none;
      font-weight: 600;
    }
    
    .informacion-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 100px 20px 40px;
    }
    
    .informacion-card {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 20px;
      overflow: hidden;
    }
    
    .seccion-informacion {
      margin-bottom: 2rem;
      padding-bottom: 1.5rem;
      border-bottom: 1px solid #e0e0e0;
    }
    
    .seccion-informacion:last-child {
      border-bottom: none;
      margin-bottom: 0;
      padding-bottom: 0;
    }
    
    .seccion-informacion h2 {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      color: #2c3e50;
      margin-bottom: 1rem;
      font-size: 1.3rem;
    }
    
    .seccion-informacion h2 mat-icon {
      color: #4f6bed;
    }
    
    .seccion-informacion p {
      color: #555;
      line-height: 1.6;
      margin-bottom: 0.75rem;
    }
    
    .seccion-informacion ul {
      padding-left: 1.5rem;
      margin-bottom: 0.75rem;
    }
    
    .seccion-informacion li {
      color: #555;
      margin-bottom: 0.25rem;
    }
    
    .grid-distribucion {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1.5rem;
      margin-top: 1rem;
    }
    
    .tipo-comercio {
      background: #f8f9fa;
      padding: 1rem;
      border-radius: 12px;
      border-left: 4px solid #4f6bed;
    }
    
    .tipo-comercio h3 {
      color: #4f6bed;
      margin-bottom: 0.5rem;
    }
    
    .tipo-comercio p {
      color: #666;
      font-size: 0.85rem;
      font-style: italic;
      margin-bottom: 0.5rem;
    }
    
    .tipo-comercio ul {
      padding-left: 1.25rem;
    }
    
    .tipo-comercio li {
      font-size: 0.9rem;
    }
    
    .ciclo-vida {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      align-items: center;
      gap: 0.5rem;
      margin-top: 1rem;
    }
    
    .etapa {
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 1rem;
      background: #f8f9fa;
      border-radius: 12px;
      min-width: 100px;
    }
    
    .etapa mat-icon {
      font-size: 2rem;
      width: auto;
      height: auto;
      color: #4f6bed;
      margin-bottom: 0.5rem;
    }
    
    .etapa span {
      font-size: 0.8rem;
      color: #555;
    }
    
    .flecha {
      color: #4f6bed;
    }
    
    .boton-volver-container {
      text-align: center;
      margin-top: 2rem;
      padding-top: 1.5rem;
      border-top: 1px solid #e0e0e0;
    }
    
    .boton-volver {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    @media (max-width: 768px) {
      header {
        padding: 0.75rem 1rem;
      }
      
      header h1 {
        font-size: 1.2rem;
      }
      
      .informacion-container {
        padding: 80px 15px 30px;
      }
      
      .ciclo-vida {
        flex-direction: column;
      }
      
      .flecha {
        transform: rotate(90deg);
      }
      
      .grid-distribucion {
        grid-template-columns: 1fr;
      }
    }
  `]
})
export class InformacionComponent {}