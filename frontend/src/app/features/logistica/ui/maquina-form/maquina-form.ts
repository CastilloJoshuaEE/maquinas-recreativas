/**
 * @fileoverview Formulario de Máquina
 * @description Componente para registrar nuevas máquinas recreativas con pasos
 * @component MaquinaFormComponent
 */

import { Component, Output, EventEmitter, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatStepperModule } from '@angular/material/stepper';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { LogisticaService } from '../../services/logistica.service';
import { AuthService } from '@core/services/auth.service';
import { Comercio } from '@core/models/recaudacion.model';
import { User } from '@core/models/user.model';

@Component({
  selector: 'app-maquina-form',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatStepperModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule
  ],
  template: `
    <div class="modal-overlay" (click)="onClose.emit()">
      <div class="modal-content" (click)="$event.stopPropagation()">
        <div class="modal-header">
          <h2>Registrar Nueva Máquina Recreativa</h2>
          <button class="modal-close" (click)="onClose.emit()">×</button>
        </div>
        
        <div class="modal-body">
          <div *ngIf="error" class="error-message">
            {{ error }}
          </div>
          
          <mat-stepper [linear]="true" #stepper>
            <!-- Paso 1: Componentes Logísticos -->
            <mat-step>
              <ng-template matStepLabel>Crear Componentes</ng-template>
              
              <div class="step-content">
                <p class="step-info">
                  Por favor, cree primero una placa y una carcasa antes de registrar la máquina.
                </p>
                
                <div class="componente-item">
                  <h4>Placa base Logística</h4>
                  <button mat-raised-button 
                          (click)="crearPlaca()" 
                          [disabled]="placaCreada || creandoPlaca"
                          color="primary">
                    <mat-spinner diameter="20" *ngIf="creandoPlaca"></mat-spinner>
                    <span *ngIf="!creandoPlaca && !placaCreada">Crear Placa</span>
                    <span *ngIf="!creandoPlaca && placaCreada">✓ Placa creada: {{ placaId | slice:0:8 }}...</span>
                  </button>
                  <div *ngIf="errorPlaca" class="component-error">{{ errorPlaca }}</div>
                </div>

                <div class="componente-item">
                  <h4>Carcasa estándar</h4>
                  <mat-form-field appearance="outline" class="full-width">
                    <mat-label>Seleccione una carcasa</mat-label>
                    <mat-select [(ngModel)]="carcasaSeleccionada" [disabled]="carcasaAsignada">
                      <mat-option *ngFor="let carcasa of carcasasDisponibles" [value]="carcasa.ID_Componente">
                        {{ carcasa.nombre }} (${{ carcasa.precio }})
                      </mat-option>
                    </mat-select>
                  </mat-form-field>
                  <button mat-raised-button 
                          (click)="asignarCarcasa()" 
                          [disabled]="carcasaAsignada || !carcasaSeleccionada || asignandoCarcasa"
                          color="primary">
                    <mat-spinner diameter="20" *ngIf="asignandoCarcasa"></mat-spinner>
                    <span *ngIf="!asignandoCarcasa && !carcasaAsignada">Asignar Carcasa</span>
                    <span *ngIf="!asignandoCarcasa && carcasaAsignada">✓ Carcasa asignada</span>
                  </button>
                  <div *ngIf="errorCarcasa" class="component-error">{{ errorCarcasa }}</div>
                </div>

                <div class="step-actions">
                  <button mat-button matStepperNext [disabled]="!placaCreada || !carcasaAsignada">
                    Siguiente
                  </button>
                  <button mat-button type="button" (click)="onClose.emit()">Cancelar</button>
                </div>
              </div>
            </mat-step>

            <!-- Paso 2: Datos de la Máquina -->
            <mat-step [stepControl]="maquinaForm">
              <form [formGroup]="maquinaForm">
                <ng-template matStepLabel>Datos de la Máquina</ng-template>
                
                <div class="step-content">
                  <mat-form-field appearance="outline" class="full-width">
                    <mat-label>Nombre de la Máquina</mat-label>
                    <input matInput formControlName="nombre" placeholder="Ingrese el nombre">
                    <mat-icon matPrefix>videogame_asset</mat-icon>
                    <mat-error *ngIf="maquinaForm.get('nombre')?.hasError('required')">
                      Nombre requerido
                    </mat-error>
                  </mat-form-field>

                  <mat-form-field appearance="outline" class="full-width">
                    <mat-label>Tipo de Máquina</mat-label>
                    <input matInput formControlName="tipo" placeholder="Ej: Arcade, Pinball, etc.">
                    <mat-icon matPrefix>category</mat-icon>
                    <mat-error *ngIf="maquinaForm.get('tipo')?.hasError('required')">
                      Tipo requerido
                    </mat-error>
                  </mat-form-field>

                  <mat-form-field appearance="outline" class="full-width">
                    <mat-label>Comercio</mat-label>
                    <mat-select formControlName="idComercio">
                      <mat-option value="">-- Seleccione un comercio --</mat-option>
                      <mat-option *ngFor="let comercio of comercios" [value]="comercio.ID_Comercio">
                        {{ comercio.Nombre }} ({{ comercio.Tipo }})
                      </mat-option>
                    </mat-select>
                    <mat-icon matPrefix>store</mat-icon>
                    <mat-error *ngIf="maquinaForm.get('idComercio')?.hasError('required')">
                      Comercio requerido
                    </mat-error>
                  </mat-form-field>

                  <div class="tecnicos-info">
                    <mat-form-field appearance="outline" class="full-width">
                      <mat-label>Técnico Ensamblador</mat-label>
                      <input matInput [value]="ensambladorNombre" readonly>
                      <mat-icon matPrefix>engineering</mat-icon>
                    </mat-form-field>

                    <mat-form-field appearance="outline" class="full-width">
                      <mat-label>Técnico Comprobador</mat-label>
                      <input matInput [value]="comprobadorNombre" readonly>
                      <mat-icon matPrefix>engineering</mat-icon>
                    </mat-form-field>
                  </div>
                </div>
                
                <div class="step-actions">
                  <button mat-button matStepperPrevious>Atrás</button>
                  <button mat-raised-button color="primary" 
                          [disabled]="maquinaForm.invalid || registrando"
                          (click)="registrarMaquina(stepper)">
                    <mat-spinner diameter="20" *ngIf="registrando"></mat-spinner>
                    <span *ngIf="!registrando">Registrar Máquina</span>
                  </button>
                </div>
              </form>
            </mat-step>
          </mat-stepper>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.75);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }
    
    .modal-content {
      background: white;
      border-radius: 12px;
      width: 90%;
      max-width: 550px;
      max-height: 90vh;
      overflow-y: auto;
    }
    
    .modal-header {
      padding: 1rem 1.5rem;
      background: linear-gradient(135deg, #4f6bed, #3d55c3);
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-radius: 12px 12px 0 0;
    }
    
    .modal-header h2 {
      margin: 0;
      color: white;
      font-size: 1.25rem;
    }
    
    .modal-close {
      background: none;
      border: none;
      font-size: 1.5rem;
      cursor: pointer;
      color: white;
    }
    
    .modal-body {
      padding: 1.5rem;
    }
    
    .step-content {
      padding: 1rem 0;
    }
    
    .step-info {
      color: #666;
      margin-bottom: 1rem;
      padding: 0.5rem;
      background: #f8f9fa;
      border-radius: 8px;
    }
    
    .componente-item {
      margin-bottom: 1.5rem;
      padding: 1rem;
      background: #f8f9fa;
      border-radius: 8px;
    }
    
    .componente-item h4 {
      color: #4f6bed;
      margin-bottom: 0.75rem;
    }
    
    .full-width {
      width: 100%;
      margin-bottom: 1rem;
    }
    
    .component-error {
      color: #dc3545;
      font-size: 0.8rem;
      margin-top: 0.5rem;
    }
    
    .tecnicos-info {
      margin-top: 1rem;
      padding-top: 1rem;
      border-top: 1px solid #e0e0e0;
    }
    
    .step-actions {
      display: flex;
      justify-content: flex-end;
      gap: 1rem;
      margin-top: 1rem;
    }
    
    .error-message {
      padding: 0.75rem;
      background: #f8d7da;
      color: #721c24;
      border-radius: 8px;
      margin-bottom: 1rem;
    }
    
    @media (max-width: 768px) {
      .step-actions {
        flex-direction: column;
      }
      
      .step-actions button {
        width: 100%;
      }
    }
  `]
})
export class MaquinaFormComponent implements OnInit {
  @Output() onClose = new EventEmitter<void>();
  @Output() onSuccess = new EventEmitter<void>();
  
  private fb = inject(FormBuilder);
  private logisticaService = inject(LogisticaService);
  private authService = inject(AuthService);
  private snackBar = inject(MatSnackBar);
  
  maquinaForm: FormGroup;
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
    // Cargar comercios
    this.logisticaService.getComercios().subscribe({
      next: (data) => {
        this.comercios = data;
      },
      error: () => {
        this.snackBar.error('Error al cargar comercios', 'Cerrar');
      }
    });
    
    // Cargar carcasas disponibles
    this.logisticaService.getComponentesDisponibles('Logistico').subscribe({
      next: (data) => {
        this.carcasasDisponibles = data.filter(c => c.nombre.includes('Carcasa'));
      },
      error: () => {
        this.snackBar.error('Error al cargar carcasas', 'Cerrar');
      }
    });
    
    // Cargar técnicos
    this.logisticaService.getTecnicosPorEspecialidad('Ensamblador').subscribe({
      next: (data) => {
        this.ensambladores = data;
      },
      error: () => {
        this.snackBar.error('Error al cargar técnicos ensambladores', 'Cerrar');
      }
    });
    
    this.logisticaService.getTecnicosPorEspecialidad('Comprobador').subscribe({
      next: (data) => {
        this.comprobadores = data;
      },
      error: () => {
        this.snackBar.error('Error al cargar técnicos comprobadores', 'Cerrar');
      }
    });
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
    if (!user?.ID_Usuario) {
      this.errorPlaca = 'Usuario no autenticado';
      return;
    }
    
    this.creandoPlaca = true;
    this.errorPlaca = '';
    
    this.logisticaService.generarPlaca(user.ID_Usuario).subscribe({
      next: (data) => {
        if (data) {
          this.placaCreada = true;
          this.placaId = data.id_componente;
          this.snackBar.open('Placa creada correctamente', 'Cerrar', { duration: 3000 });
        } else {
          this.errorPlaca = 'Error al crear la placa';
        }
        this.creandoPlaca = false;
      },
      error: (err) => {
        this.errorPlaca = err.message || 'Error al crear la placa';
        this.creandoPlaca = false;
      }
    });
  }
  
  asignarCarcasa(): void {
    if (!this.carcasaSeleccionada) {
      this.errorCarcasa = 'Seleccione una carcasa';
      return;
    }
    
    const user = this.authService.getCurrentUser();
    if (!user?.ID_Usuario) {
      this.errorCarcasa = 'Usuario no autenticado';
      return;
    }
    
    this.asignandoCarcasa = true;
    this.errorCarcasa = '';
    
    this.logisticaService.asignarCarcasa(this.carcasaSeleccionada, user.ID_Usuario).subscribe({
      next: (success) => {
        if (success) {
          this.carcasaAsignada = true;
          this.snackBar.open('Carcasa asignada correctamente', 'Cerrar', { duration: 3000 });
        } else {
          this.errorCarcasa = 'Error al asignar la carcasa';
        }
        this.asignandoCarcasa = false;
      },
      error: (err) => {
        this.errorCarcasa = err.message || 'Error al asignar la carcasa';
        this.asignandoCarcasa = false;
      }
    });
  }
  
  registrarMaquina(stepper: any): void {
    if (this.maquinaForm.invalid) {
      this.snackBar.open('Complete todos los campos', 'Cerrar', { duration: 3000 });
      return;
    }
    
    if (!this.placaCreada || !this.carcasaAsignada) {
      this.error = 'Debe crear la placa y asignar la carcasa primero';
      return;
    }
    
    if (this.ensambladores.length === 0 || this.comprobadores.length === 0) {
      this.error = 'No hay técnicos disponibles para asignar';
      return;
    }
    
    if (!confirm('¿Está seguro de registrar esta máquina?')) return;
    
    this.registrando = true;
    this.error = '';
    
    const user = this.authService.getCurrentUser();
    const formValue = this.maquinaForm.value;
    
    const maquinaData = {
      nombre: formValue.nombre,
      tipo: formValue.tipo,
      idComercio: formValue.idComercio,
      idUsuarioLogistica: user?.ID_Usuario,
      idPlaca: this.placaId,
      idCarcasa: this.carcasaSeleccionada
    };
    
    this.logisticaService.registrarMaquina(maquinaData).subscribe({
      next: (success) => {
        if (success) {
          this.snackBar.open('Máquina registrada correctamente', 'Cerrar', { duration: 3000 });
          this.onSuccess.emit();
        } else {
          this.error = 'Error al registrar la máquina';
        }
        this.registrando = false;
      },
      error: (err) => {
        this.error = err.message || 'Error al registrar la máquina';
        this.registrando = false;
      }
    });
  }
}