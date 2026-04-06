/**
 * @fileoverview Componente de Lista de Máquinas
 * @description Muestra una lista colapsable de máquinas con selección y acciones
 * @component MaquinaListComponent
 */

import { Component, Input, Output, EventEmitter, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatTooltipModule } from '@angular/material/tooltip';
import { Maquina } from '@core/models/maquina.model';

@Component({
  selector: 'app-maquina-list',
  standalone: true,
  imports: [CommonModule, MatButtonModule, MatIconModule, MatTooltipModule],
  templateUrl: './maquina-list.html',
  styleUrls: ['./maquina-list.css']
})
export class MaquinaListComponent {
  @Input() title: string = '';
  @Input() maquinas: Maquina[] = [];
  @Input() selectedMaquina: Maquina | null = null;
  @Input() emptyMessage: string = 'No hay máquinas...';
  @Input() initiallyExpanded: boolean = false;
  @Input() actionButtons: Array<{ label: string; onClick: () => void; disabled?: boolean; style?: any }> = [];
  @Input() showComponentButton: boolean = true;
  
  @Output() onSelectMaquina = new EventEmitter<Maquina>();
  
  private router = inject(Router);
  
  mostrarMaquinas = this.initiallyExpanded;
  
  toggleMostrar(): void {
    this.mostrarMaquinas = !this.mostrarMaquinas;
  }
  
  seleccionarMaquina(maquina: Maquina): void {
    this.onSelectMaquina.emit(maquina);
  }
  
  abrirComponentes(): void {
    if (this.selectedMaquina) {
      localStorage.setItem('selectedMachine', JSON.stringify(this.selectedMaquina));
      this.router.navigate(['/tecnico/gestion-componentes']);
    }
  }
  
  isSelected(maquina: Maquina): boolean {
    return this.selectedMaquina?.ID_Maquina === maquina.ID_Maquina;
  }
}