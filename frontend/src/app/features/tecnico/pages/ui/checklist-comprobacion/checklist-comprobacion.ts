/**
 * @fileoverview Componente de Checklist para Comprobación
 * @description Componente reutilizable para el checklist de comprobación de máquinas
 * @component ChecklistComprobacionComponent
 */

import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';

export interface ChecklistState {
  placaFuncional: boolean;
  carcasaBuenEstado: boolean;
  experienciaJuegoAcorde: boolean;
}

@Component({
  selector: 'app-checklist-comprobacion',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    MatCheckboxModule,
    MatButtonModule,
    MatIconModule
  ],
  template: `
    <div class="checklist-container">
      <h3>Checklist de comprobación</h3>
      
      <div class="checklist-item">
        <mat-checkbox [(ngModel)]="checklist.placaFuncional">
          ¿Placa funcional?
        </mat-checkbox>
      </div>
      
      <div class="checklist-item">
        <mat-checkbox [(ngModel)]="checklist.carcasaBuenEstado">
          ¿Carcasa en buen estado?
        </mat-checkbox>
      </div>
      
      <div class="checklist-item">
        <mat-checkbox [(ngModel)]="checklist.experienciaJuegoAcorde">
          ¿Experiencia de juego acorde?
        </mat-checkbox>
      </div>
      
      <div class="action-buttons">
        <button mat-raised-button color="primary" 
                (click)="onAprobar.emit(checklist)"
                [disabled]="!allChecksPassed()">
          <mat-icon>check_circle</mat-icon>
          Mandar a distribución
        </button>
        <button mat-raised-button color="warn" 
                (click)="onRechazar.emit()">
          <mat-icon>cancel</mat-icon>
          Mandar a reensamblar
        </button>
      </div>
    </div>
  `,
  styles: [`
    .checklist-container {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 12px;
      padding: 1.5rem;
      margin-top: 1rem;
    }
    
    .checklist-container h3 {
      color: white;
      margin-bottom: 1rem;
    }
    
    .checklist-item {
      margin-bottom: 1rem;
      color: white;
    }
    
    .action-buttons {
      display: flex;
      gap: 1rem;
      margin-top: 1.5rem;
    }
    
    @media (max-width: 768px) {
      .action-buttons {
        flex-direction: column;
      }
      
      .action-buttons button {
        width: 100%;
      }
    }
  `]
})
export class ChecklistComprobacionComponent {
  @Input() maquinaNombre: string = '';
  @Output() onAprobar = new EventEmitter<ChecklistState>();
  @Output() onRechazar = new EventEmitter<void>();
  
  checklist: ChecklistState = {
    placaFuncional: false,
    carcasaBuenEstado: false,
    experienciaJuegoAcorde: false
  };
  
  allChecksPassed(): boolean {
    return this.checklist.placaFuncional && 
           this.checklist.carcasaBuenEstado && 
           this.checklist.experienciaJuegoAcorde;
  }
  
  resetChecklist(): void {
    this.checklist = {
      placaFuncional: false,
      carcasaBuenEstado: false,
      experienciaJuegoAcorde: false
    };
  }
}