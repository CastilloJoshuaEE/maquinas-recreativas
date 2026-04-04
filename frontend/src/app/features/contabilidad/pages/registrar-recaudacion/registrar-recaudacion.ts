/**
 * @fileoverview Registro de Recaudación
 * @description Formulario para registrar nuevas recaudaciones de máquinas
 * @component RegistrarRecaudacionComponent
 */

import { Component, OnInit, OnDestroy, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { MatStepperModule } from '@angular/material/stepper';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header.component';
import { ContabilidadService } from '../../services/contabilidad.service';
import { AuthService } from '@core/services/auth.service';
import { Comercio, Recaudacion } from '@core/models/recaudacion.model';
import { Maquina } from '@core/models/maquina.model';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-registrar-recaudacion',
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
    MatProgressSpinnerModule,
    AdminHeaderComponent
  ],
  template: `
    <div class="registrar-recaudacion-container">
      <app-admin-header></app-admin-header>
      
      <div class="content-wrapper">
        <div class="page-header">
          <button mat-icon-button (click)="regresar()" class="back-button">
            <mat-icon>arrow_back</mat-icon>
          </button>
          <h2>Registrar Recaudación</h2>
        </div>

        <mat-stepper [linear]="true" #stepper>
          <!-- Paso 1: Seleccionar Comercio y Máquina -->
          <mat-step [stepControl]="recaudacionForm">
            <form [formGroup]="recaudacionForm">
              <ng-template matStepLabel>Seleccionar Comercio y Máquina</ng-template>
              
              <div class="step-content">
                <mat-form-field appearance="outline" class="full-width">
                  <mat-label>Comercio</mat-label>
                  <mat-select formControlName="ID_Comercio" (selectionChange)="onComercioChange()">
                    <mat-option value="">-- Seleccione un comercio --</mat-option>
                    <mat-option *ngFor="let comercio of comercios" [value]="comercio.ID_Comercio">
                      {{ comercio.Nombre }} ({{ comercio.Tipo }})
                    </mat-option>
                  </mat-select>
                  <mat-icon matPrefix>store</mat-icon>
                  <mat-error *ngIf="recaudacionForm.get('ID_Comercio')?.hasError('required')">
                    Comercio requerido
                  </mat-error>
                </mat-form-field>

                <mat-form-field appearance="outline" class="full-width">
                  <mat-label>Tipo de Comercio</mat-label>
                  <input matInput formControlName="Tipo_Comercio" readonly>
                  <mat-icon matPrefix>info</mat-icon>
                </mat-form-field>

                <mat-form-field appearance="outline" class="full-width">
                  <mat-label>Máquina Recreativa</mat-label>
                  <mat-select formControlName="ID_Maquina" (selectionChange)="onMaquinaChange()">
                    <mat-option value="">-- Seleccione una máquina --</mat-option>
                    <mat-option *ngIf="cargandoMaquinas" value="" disabled>Cargando máquinas...</mat-option>
                    <mat-option *ngFor="let maquina of maquinas" [value]="maquina.ID_Maquina">
                      {{ maquina.Nombre_Maquina }}
                    </mat-option>
                  </mat-select>
                  <mat-icon matPrefix>videogame_asset</mat-icon>
                  <mat-error *ngIf="recaudacionForm.get('ID_Maquina')?.hasError('required')">
                    Máquina requerida
                  </mat-error>
                </mat-form-field>
              </div>
              
              <div class="step-actions">
                <button mat-button matStepperNext [disabled]="recaudacionForm.get('ID_Maquina')?.invalid">Siguiente</button>
              </div>
            </form>
          </mat-step>

          <!-- Paso 2: Datos de Recaudación -->
          <mat-step [stepControl]="recaudacionForm">
            <form [formGroup]="recaudacionForm">
              <ng-template matStepLabel>Datos de Recaudación</ng-template>
              
              <div class="step-content">
                <mat-form-field appearance="outline" class="full-width">
                  <mat-label>Monto Total</mat-label>
                  <input matInput type="number" step="0.01" formControlName="Monto_Total" placeholder="0.00">
                  <mat-icon matPrefix>attach_money</mat-icon>
                  <mat-error *ngIf="recaudacionForm.get('Monto_Total')?.hasError('required')">
                    Monto total requerido
                  </mat-error>
                  <mat-error *ngIf="recaudacionForm.get('Monto_Total')?.hasError('min')">
                    Monto debe ser mayor a 0
                  </mat-error>
                </mat-form-field>

                <div *ngIf="recaudacionForm.get('Tipo_Comercio')?.value === 'Mayorista'">
                  <mat-form-field appearance="outline" class="full-width">
                    <mat-label>Porcentaje para Comercio (%)</mat-label>
                    <input matInput type="number" step="0.01" formControlName="Porcentaje_Comercio" placeholder="20">
                    <mat-icon matPrefix>percent</mat-icon>
                    <mat-error *ngIf="recaudacionForm.get('Porcentaje_Comercio')?.hasError('required')">
                      Porcentaje requerido
                    </mat-error>
                    <mat-error *ngIf="recaudacionForm.get('Porcentaje_Comercio')?.hasError('min')">
                      Porcentaje debe ser mayor a 0
                    </mat-error>
                    <mat-error *ngIf="recaudacionForm.get('Porcentaje_Comercio')?.hasError('max')">
                      Porcentaje no puede superar 100
                    </mat-error>
                  </mat-form-field>

                  <mat-form-field appearance="outline" class="full-width">
                    <mat-label>Monto para Comercio</mat-label>
                    <input matInput formControlName="Monto_Comercio" readonly>
                    <mat-icon matPrefix>store</mat-icon>
                  </mat-form-field>

                  <mat-form-field appearance="outline" class="full-width">
                    <mat-label>Monto para Empresa</mat-label>
                    <input matInput formControlName="Monto_Empresa" readonly>
                    <mat-icon matPrefix>business</mat-icon>
                  </mat-form-field>
                </div>

                <mat-form-field appearance="outline" class="full-width">
                  <mat-label>Fecha y Hora</mat-label>
                  <input matInput type="datetime-local" formControlName="fecha">
                  <mat-icon matPrefix>calendar_today</mat-icon>
                  <mat-error *ngIf="recaudacionForm.get('fecha')?.hasError('required')">
                    Fecha requerida
                  </mat-error>
                </mat-form-field>

                <mat-form-field appearance="outline" class="full-width">
                  <mat-label>Detalles</mat-label>
                  <textarea matInput formControlName="detalle" rows="3" placeholder="Detalles adicionales..."></textarea>
                  <mat-icon matPrefix>description</mat-icon>
                </mat-form-field>
              </div>
              
              <div class="step-actions">
                <button mat-button matStepperPrevious>Atrás</button>
                <button mat-raised-button color="primary" [disabled]="recaudacionForm.invalid || submitting" (click)="registrarRecaudacion(stepper)">
                  <mat-spinner diameter="20" *ngIf="submitting"></mat-spinner>
                  <span *ngIf="!submitting">Registrar Recaudación</span>
                </button>
              </div>
            </form>
          </mat-step>
        </mat-stepper>

        <div *ngIf="success" class="success-message">
          <mat-icon>check_circle</mat-icon>
          <p>¡Recaudación registrada correctamente! Redirigiendo...</p>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .registrar-recaudacion-container {
      min-height: 100vh;
      background: linear-gradient(135deg, #07224c 0%, #124258 50%, #3b4a66 100%);
    }
    
    .content-wrapper {
      max-width: 600px;
      margin: 0 auto;
      padding: 2rem;
    }
    
    .page-header {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 2rem;
    }
    
    .page-header h2 {
      color: white;
      margin: 0;
    }
    
    .back-button {
      color: white;
      background: rgba(255, 255, 255, 0.15);
    }
    
    .step-content {
      display: flex;
      flex-direction: column;
      gap: 1rem;
      padding: 1.5rem 0;
    }
    
    .full-width {
      width: 100%;
    }
    
    .step-actions {
      display: flex;
      justify-content: flex-end;
      gap: 1rem;
      margin-top: 1rem;
    }
    
    .success-message {
      margin-top: 2rem;
      padding: 1rem;
      background: #d4edda;
      color: #155724;
      border-radius: 8px;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      justify-content: center;
    }
    
    @media (max-width: 768px) {
      .content-wrapper {
        padding: 1rem;
      }
    }
  `]
})
export class RegistrarRecaudacionComponent implements OnInit, OnDestroy {
  private fb = inject(FormBuilder);
  private router = inject(Router);
  private contabilidadService = inject(ContabilidadService);
  private authService = inject(AuthService);
  private snackBar = inject(MatSnackBar);
  
  recaudacionForm: FormGroup;
  comercios: Comercio[] = [];
  maquinas: Maquina[] = [];
  cargandoMaquinas = false;
  submitting = false;
  success = false;
  private subscriptions: Subscription[] = [];
  
  ngOnInit(): void {
    this.initForm();
    this.cargarComercios();
    this.setupCalculosAutomaticos();
  }
  
  ngOnDestroy(): void {
    this.subscriptions.forEach(sub => sub.unsubscribe());
  }
  
  private initForm(): void {
    this.recaudacionForm = this.fb.group({
      ID_Comercio: ['', Validators.required],
      ID_Maquina: ['', Validators.required],
      Tipo_Comercio: [{ value: '', disabled: true }],
      Porcentaje_Comercio: [20, [Validators.min(0), Validators.max(100)]],
      Monto_Total: ['', [Validators.required, Validators.min(0.01)]],
      Monto_Comercio: [{ value: '0.00', disabled: true }],
      Monto_Empresa: [{ value: '0.00', disabled: true }],
      fecha: [this.getFechaHoraActual(), Validators.required],
      detalle: ['']
    });
  }
  
  private getFechaHoraActual(): string {
    const ahora = new Date();
    ahora.setMinutes(ahora.getMinutes() - ahora.getTimezoneOffset());
    return ahora.toISOString().slice(0, 16);
  }
  
  private cargarComercios(): void {
    this.contabilidadService.getComercios().subscribe({
      next: (data) => {
        this.comercios = data;
      },
      error: (err) => {
        this.snackBar.open('Error al cargar comercios', 'Cerrar', { duration: 3000 });
      }
    });
  }
  
  onComercioChange(): void {
    const idComercio = this.recaudacionForm.get('ID_Comercio')?.value;
    const comercio = this.comercios.find(c => c.ID_Comercio === idComercio);
    
    if (comercio) {
      this.recaudacionForm.patchValue({ Tipo_Comercio: comercio.Tipo });
      this.cargarMaquinasPorComercio(idComercio);
    }
  }
  
  private cargarMaquinasPorComercio(idComercio: string): void {
    this.cargandoMaquinas = true;
    this.recaudacionForm.patchValue({ ID_Maquina: '' });
    
    this.contabilidadService.getMaquinasOperativasPorComercio(idComercio).subscribe({
      next: (data) => {
        this.maquinas = data;
        this.cargandoMaquinas = false;
        if (data.length === 0) {
          this.snackBar.warning('No hay máquinas operativas para este comercio', 'Cerrar');
        }
      },
      error: () => {
        this.maquinas = [];
        this.cargandoMaquinas = false;
        this.snackBar.error('Error al cargar máquinas', 'Cerrar');
      }
    });
  }
  
  onMaquinaChange(): void {
    // Aquí se podría cargar información adicional de la máquina si es necesario
  }
  
  private setupCalculosAutomaticos(): void {
    const montoTotalSub = this.recaudacionForm.get('Monto_Total')?.valueChanges.subscribe(() => {
      this.calcularMontos();
    });
    
    const porcentajeSub = this.recaudacionForm.get('Porcentaje_Comercio')?.valueChanges.subscribe(() => {
      this.calcularMontos();
    });
    
    if (montoTotalSub) this.subscriptions.push(montoTotalSub);
    if (porcentajeSub) this.subscriptions.push(porcentajeSub);
  }
  
  private calcularMontos(): void {
    const tipo = this.recaudacionForm.get('Tipo_Comercio')?.value;
    const montoTotal = parseFloat(this.recaudacionForm.get('Monto_Total')?.value) || 0;
    
    if (tipo === 'Mayorista') {
      const porcentaje = parseFloat(this.recaudacionForm.get('Porcentaje_Comercio')?.value) || 0;
      const montoComercio = montoTotal * (porcentaje / 100);
      const montoEmpresa = montoTotal - montoComercio;
      
      this.recaudacionForm.patchValue({
        Monto_Comercio: montoComercio.toFixed(2),
        Monto_Empresa: montoEmpresa.toFixed(2)
      }, { emitEvent: false });
    } else {
      this.recaudacionForm.patchValue({
        Monto_Comercio: '0.00',
        Monto_Empresa: montoTotal.toFixed(2)
      }, { emitEvent: false });
    }
  }
  
  registrarRecaudacion(stepper: any): void {
    if (this.recaudacionForm.invalid) {
      this.snackBar.open('Complete todos los campos correctamente', 'Cerrar', { duration: 3000 });
      return;
    }
    
    if (!confirm('¿Está seguro de registrar esta recaudación?')) return;
    
    this.submitting = true;
    const currentUser = this.authService.getCurrentUser();
    const formValue = this.recaudacionForm.getRawValue();
    
    const data = {
      ID_Comercio: formValue.ID_Comercio,
      ID_Maquina: formValue.ID_Maquina,
      Tipo_Comercio: formValue.Tipo_Comercio,
      Porcentaje_Comercio: formValue.Tipo_Comercio === 'Mayorista' ? formValue.Porcentaje_Comercio : 0,
      Monto_Total: parseFloat(formValue.Monto_Total),
      Monto_Comercio: parseFloat(formValue.Monto_Comercio),
      Monto_Empresa: parseFloat(formValue.Monto_Empresa),
      fecha: new Date(formValue.fecha).toISOString().slice(0, 19).replace('T', ' '),
      detalle: formValue.detalle,
      ID_Usuario: currentUser?.ID_Usuario
    };
    
    this.contabilidadService.registrarRecaudacion(data).subscribe({
      next: (response) => {
        if (response.success) {
          this.success = true;
          this.snackBar.open('Recaudación registrada correctamente', 'Cerrar', { duration: 3000 });
          setTimeout(() => {
            this.router.navigate(['/contabilidad/gestion-recaudacion']);
          }, 2000);
        } else {
          this.snackBar.open(response.message || 'Error al registrar recaudación', 'Cerrar', { duration: 3000 });
        }
        this.submitting = false;
      },
      error: (err) => {
        this.snackBar.open(err.message || 'Error al registrar recaudación', 'Cerrar', { duration: 3000 });
        this.submitting = false;
      }
    });
  }
  
  regresar(): void {
    this.router.navigate(['/contabilidad/gestion-recaudacion']);
  }
}