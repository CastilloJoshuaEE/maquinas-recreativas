/**
 * @fileoverview Gestión de Componentes para Técnicos
 * @description Permite a los técnicos ver y gestionar componentes disponibles y en uso
 * @component GestionComponentesComponent
 */

import { Component, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatTableModule } from '@angular/material/table';
import { MatPaginatorModule, PageEvent } from '@angular/material/paginator';
import { MatSelectModule } from '@angular/material/select';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header';
import { TecnicoService } from '../../services/tecnico';
import { AuthService } from '@core/services/auth';
import { Componente } from '@core/models/componente.model';
import { Maquina } from '@core/models/maquina.model';
import { User } from '@core/models/user.model';

@Component({
  selector: 'app-gestion-componentes',
  standalone: true,
  imports: [CommonModule, FormsModule, MatCardModule, MatButtonModule, MatIconModule, MatTableModule, MatPaginatorModule, MatSelectModule, MatProgressSpinnerModule, AdminHeaderComponent],
  templateUrl: './gestion-componentes.html',
  styleUrls: ['./gestion-componentes.css']
})
export class GestionComponentesComponent implements OnInit {
  private router = inject(Router);
  private tecnicoService = inject(TecnicoService);
  private authService = inject(AuthService);
  private snackBar = inject(MatSnackBar);
  
  user: User | null = null;
  selectedMachine: Maquina | null = null;
  componentesDisponibles: Componente[] = [];
  displayedColumnsDisponibles: string[] = ['ID_Componente', 'nombre', 'tipo', 'precio', 'acciones'];
  cargandoDisponibles = false;
  totalDisponibles = 0;
  pageSize = 10;
  currentPage = 0;
  filtroTipo = '';
  componentesEnUso: Componente[] = [];
  displayedColumnsEnUso: string[] = ['ID_Componente', 'nombre', 'tipo', 'Nombre_Maquina', 'acciones'];
  cargandoEnUso = false;
  
  ngOnInit(): void {
    this.user = this.authService.getCurrentUser();
    this.cargarMquinaSeleccionada();
    if (this.user?.ID_Usuario) {
      this.cargarComponentesDisponibles();
      this.cargarComponentesEnUso();
    }
  }
  
  private cargarMquinaSeleccionada(): void {
    const storedMachine = localStorage.getItem('selectedMachine');
    if (storedMachine) { this.selectedMachine = JSON.parse(storedMachine); }
    else { this.snackBar.warning('No hay máquina seleccionada. Seleccione una máquina primero.', 'Cerrar'); }
  }
  
  cargarComponentesDisponibles(): void {
    this.cargandoDisponibles = true;
    const tipoParam = this.filtroTipo || undefined;
    this.tecnicoService.getComponentesDisponibles(tipoParam, this.currentPage + 1, this.pageSize).subscribe({
      next: (response) => { this.componentesDisponibles = response.componentes; this.totalDisponibles = response.total; this.cargandoDisponibles = false; },
      error: () => { this.cargandoDisponibles = false; this.snackBar.error('Error al cargar componentes disponibles', 'Cerrar'); }
    });
  }
  
  cargarComponentesEnUso(): void {
    this.cargandoEnUso = true;
    this.tecnicoService.getComponentesEnUso(this.user!.ID_Usuario, this.selectedMachine?.ID_Maquina).subscribe({
      next: (componentes) => { this.componentesEnUso = componentes; this.cargandoEnUso = false; },
      error: () => { this.cargandoEnUso = false; this.snackBar.error('Error al cargar componentes en uso', 'Cerrar'); }
    });
  }
  
  componenteEnUso(componente: Componente): boolean {
    return this.componentesEnUso.some(c => c.ID_Componente === componente.ID_Componente);
  }
  
  usarComponente(componente: Componente): void {
    if (!this.selectedMachine) { this.snackBar.warning('Por favor, seleccione una máquina primero', 'Cerrar'); return; }
    if (!confirm(`¿Está seguro de usar el componente ${componente.nombre} en la máquina ${this.selectedMachine.Nombre_Maquina}?`)) return;
    this.tecnicoService.usarComponente({ ID_Componente: componente.ID_Componente, ID_Usuario: this.user!.ID_Usuario, ID_Maquina: this.selectedMachine.ID_Maquina }).subscribe({
      next: (success) => { if (success) { this.snackBar.success('Componente usado correctamente', 'Éxito'); this.cargarComponentesDisponibles(); this.cargarComponentesEnUso(); } else { this.snackBar.error('Error al usar el componente', 'Cerrar'); } },
      error: () => { this.snackBar.error('Error al usar el componente', 'Cerrar'); }
    });
  }
  
  liberarComponente(componente: Componente): void {
    if (!confirm(`¿Está seguro de liberar el componente ${componente.nombre}?`)) return;
    this.tecnicoService.liberarComponente({ ID_Componente: componente.ID_Componente, ID_Usuario: this.user!.ID_Usuario }).subscribe({
      next: (success) => { if (success) { this.snackBar.success('Componente liberado correctamente', 'Éxito'); this.cargarComponentesDisponibles(); this.cargarComponentesEnUso(); } else { this.snackBar.error('Error al liberar el componente', 'Cerrar'); } },
      error: () => { this.snackBar.error('Error al liberar el componente', 'Cerrar'); }
    });
  }
  
  onPageChangeDisponibles(event: PageEvent): void {
    this.currentPage = event.pageIndex;
    this.pageSize = event.pageSize;
    this.cargarComponentesDisponibles();
  }
  
  regresar(): void {
    const user = this.authService.getCurrentUser();
    if (user?.Especialidad) { this.router.navigate([`/tecnico/${user.Especialidad.toLowerCase()}`]); }
    else { this.router.navigate(['/tecnico/ensamblador']); }
  }
}