/**
 * @fileoverview Formulario de Máquina
 * @description Componente para registrar nuevas máquinas recreativas con pasos
 * @component MaquinaFormComponent
 */

import { Component, Output, EventEmitter, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule,FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatStepperModule } from '@angular/material/stepper';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { LogisticaService } from '../../services/logistica';
import { AuthService } from '@core/services/auth';
import { Comercio } from '@core/models/recaudacion.model';
import { User } from '@core/models/user.model';

@Component({
  selector: 'app-maquina-form',
  standalone: true,
  imports: [
    CommonModule, FormsModule,ReactiveFormsModule, MatStepperModule, MatFormFieldModule,
    MatInputModule, MatSelectModule, MatButtonModule, MatIconModule, MatProgressSpinnerModule
  ],
  templateUrl: './maquina-form.html',
  styleUrls: ['./maquina-form.css']
})
export class MaquinaFormComponent implements OnInit {
  @Output() onClose = new EventEmitter<void>();
  @Output() onSuccess = new EventEmitter<void>();
  
  private fb = inject(FormBuilder);
  private logisticaService = inject(LogisticaService);
  private authService = inject(AuthService);
  private snackBar = inject(MatSnackBar);
  
  maquinaForm!: FormGroup;
  comercios: Comercio[] = [];
  carcasasDisponibles: any[] = [];
  ensambladores: User[] = [];
  comprobadores: User[] = [];
  
  placaCreada = false;
  placaId = '';
  carcasaAsignada = false;
  carcasaSeleccionada = '';
  
  creandoPlaca = false;
  asignandoCarcasa = false;
  registrando = false;
  
  errorPlaca = '';
  errorCarcasa = '';
  error = '';
  
  ngOnInit(): void {
    this.initForm();
    this.cargarDatos();
  }
  
  private initForm(): void {
    this.maquinaForm = this.fb.group({
      nombre: ['', Validators.required],
      tipo: ['', Validators.required],
      idComercio: ['', Validators.required]
    });
  }
  
  private cargarDatos(): void {
    this.logisticaService.getComercios().subscribe({ next: (data) => { this.comercios = data; } });
    this.logisticaService.getComponentesDisponibles('Logistico').subscribe({ next: (data) => { this.carcasasDisponibles = data.filter(c => c.nombre.includes('Carcasa')); } });
    this.logisticaService.getTecnicosPorEspecialidad('Ensamblador').subscribe({ next: (data) => { this.ensambladores = data; } });
    this.logisticaService.getTecnicosPorEspecialidad('Comprobador').subscribe({ next: (data) => { this.comprobadores = data; } });
  }
  
  get ensambladorNombre(): string {
    if (this.ensambladores.length === 0) return 'No hay técnicos disponibles';
    return `${this.ensambladores[0].nombre} ${this.ensambladores[0].apellido}`;
  }
  
  get comprobadorNombre(): string {
    if (this.comprobadores.length === 0) return 'No hay técnicos disponibles';
    return `${this.comprobadores[0].nombre} ${this.comprobadores[0].apellido}`;
  }
  
  crearPlaca(): void {
    const user = this.authService.getCurrentUser();
    if (!user?.ID_Usuario) { this.errorPlaca = 'Usuario no autenticado'; return; }
    this.creandoPlaca = true;
    this.errorPlaca = '';
    
    this.logisticaService.generarPlaca(user.ID_Usuario).subscribe({
      next: (data) => {
        if (data) { this.placaCreada = true; this.placaId = data.id_componente; this.snackBar.open('Placa creada correctamente', 'Cerrar', { duration: 3000 }); }
        else { this.errorPlaca = 'Error al crear la placa'; }
        this.creandoPlaca = false;
      },
      error: (err) => { this.errorPlaca = err.message || 'Error al crear la placa'; this.creandoPlaca = false; }
    });
  }
  
  asignarCarcasa(): void {
    if (!this.carcasaSeleccionada) { this.errorCarcasa = 'Seleccione una carcasa'; return; }
    const user = this.authService.getCurrentUser();
    if (!user?.ID_Usuario) { this.errorCarcasa = 'Usuario no autenticado'; return; }
    this.asignandoCarcasa = true;
    this.errorCarcasa = '';
    
    this.logisticaService.asignarCarcasa(this.carcasaSeleccionada, user.ID_Usuario).subscribe({
      next: (success) => {
        if (success) { this.carcasaAsignada = true; this.snackBar.open('Carcasa asignada correctamente', 'Cerrar', { duration: 3000 }); }
        else { this.errorCarcasa = 'Error al asignar la carcasa'; }
        this.asignandoCarcasa = false;
      },
      error: (err) => { this.errorCarcasa = err.message || 'Error al asignar la carcasa'; this.asignandoCarcasa = false; }
    });
  }
  
  registrarMaquina(stepper: any): void {
    if (this.maquinaForm.invalid) { this.snackBar.open('Complete todos los campos', 'Cerrar', { duration: 3000 }); return; }
    if (!this.placaCreada || !this.carcasaAsignada) { this.error = 'Debe crear la placa y asignar la carcasa primero'; return; }
    if (this.ensambladores.length === 0 || this.comprobadores.length === 0) { this.error = 'No hay técnicos disponibles para asignar'; return; }
    if (!confirm('¿Está seguro de registrar esta máquina?')) return;
    
    this.registrando = true;
    this.error = '';
    const user = this.authService.getCurrentUser();
    const formValue = this.maquinaForm.value;
    
    const maquinaData = {
      nombre: formValue.nombre, tipo: formValue.tipo, idComercio: formValue.idComercio,
      idUsuarioLogistica: user?.ID_Usuario, idPlaca: this.placaId, idCarcasa: this.carcasaSeleccionada
    };
    
    this.logisticaService.registrarMaquina(maquinaData).subscribe({
      next: (success) => {
        if (success) { this.snackBar.open('Máquina registrada correctamente', 'Cerrar', { duration: 3000 }); this.onSuccess.emit(); }
        else { this.error = 'Error al registrar la máquina'; }
        this.registrando = false;
      },
      error: (err) => { this.error = err.message || 'Error al registrar la máquina'; this.registrando = false; }
    });
  }
}