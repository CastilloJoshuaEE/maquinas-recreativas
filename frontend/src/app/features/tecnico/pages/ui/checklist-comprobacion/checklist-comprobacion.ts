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
  imports: [CommonModule, FormsModule, MatCheckboxModule, MatButtonModule, MatIconModule],
  templateUrl: './checklist-comprobacion.html',
  styleUrls: ['./checklist-comprobacion.css']
})
export class ChecklistComprobacionComponent {
  @Input() maquinaNombre: string = '';
  @Output() onAprobar = new EventEmitter<ChecklistState>();
  @Output() onRechazar = new EventEmitter<void>();
  
  checklist: ChecklistState = { placaFuncional: false, carcasaBuenEstado: false, experienciaJuegoAcorde: false };
  
  allChecksPassed(): boolean {
    return this.checklist.placaFuncional && this.checklist.carcasaBuenEstado && this.checklist.experienciaJuegoAcorde;
  }
  
  resetChecklist(): void {
    this.checklist = { placaFuncional: false, carcasaBuenEstado: false, experienciaJuegoAcorde: false };
  }
}