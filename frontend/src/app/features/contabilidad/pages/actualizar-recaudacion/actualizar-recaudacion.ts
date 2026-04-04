/**
 * @fileoverview Actualización de Recaudación
 * @description Formulario para editar una recaudación existente
 * @component ActualizarRecaudacionComponent
 */

import { Component, OnInit, OnDestroy, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header.component';
import { ContabilidadService } from '../../services/contabilidad.service';
import { Recaudacion, Maquina } from '@core/models/recaudacion.model';
import { Subscription } from 'rxjs';

@Component({
  selector: 'app-actualizar-recaudacion',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatCardModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    AdminHeaderComponent
  ],
  template: `
    <div class="actualizar-container">
      <app-admin-header></app-admin-header>
      
      <div class="content-wrapper">
        <div class="page-header">
          <button mat-icon-button (click)="regresar()" class="back-button">
            <mat-icon>arrow_back</mat-icon>
          </button>
          <h2>Actualizar Recaudación</h2>
        </div>

        <mat-card>
          <mat-card-content>
            <div *ngIf="loading" class="loading-container">
              <mat-spinner diameter="40"></mat-spinner>
              <p>Cargando datos...</p>
            </div>

            <div *ngIf="error" class="error-message">
              {{ error }}
            </div>

            <div *ngIf="!loading && recaudacion">
              <form [formGroup]="recaudacionForm" (ngSubmit)="onSubmit()">
                <mat-form-field appearance="outline" class="full-width">
                  <mat-label>Máquina</mat-label>
                  <mat-select formControlName="ID_Maquina" disabled>
                    <mat-option *ngFor="let maquina of maquinas" [value]="maquina.ID_Maquina">
                      {{ maquina.Nombre_Maquina }}
                    </mat-option>
                  </mat-select>
                  <mat-icon matPrefix>videogame_asset</mat-icon>
                </mat-form-field>

                <mat-form-field appearance="outline" class="full-width">
                  <mat-label>Tipo de Comercio</mat-label>
                  <input matInput formControlName="Tipo_Comercio" readonly>
                  <mat-icon matPrefix>store</mat-icon>
                </mat-form-field>

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
                    <input matInput type="number" step="0.01" formControlName="Porcentaje_Comercio">
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

                <div class="form-actions">
                  <button mat-raised-button color="primary" type="submit" [disabled]="submitting">
                    <mat-spinner diameter="20" *ngIf="submitting"></mat-spinner>
                    <span *ngIf="!submitting">Actualizar Recaudación</span>
                  </button>
                  <button mat-button type="button" (click)="regresar()">Cancelar</button>
                </div>
              </form>
            </div>

            <div *ngIf="success" class="success-message">
              <mat-icon>check_circle</mat-icon>
              <p>¡Recaudación actualizada correctamente! Redirigiendo...</p>
            </div>
          </mat-card-content>
        </mat-card>
      </div>
    </div>
  `,
  styles: [`
    .actualizar-container {
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
    
    .full-width {
      width: 100%;
      margin-bottom: 1rem;
    }
    
    .loading-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 3rem;
    }
    
    .error-message {
      padding: 1rem;
      background: #f8d7da;
      color: #721c24;
      border-radius: 8px;
      margin-bottom: 1rem;
    }
    
    .success-message {
      margin-top: 1rem;
      padding: 1rem;
      background: #d4edda;
      color: #155724;
      border-radius: 8px;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      justify-content: center;
    }
    
    .form-actions {
      display: flex;
      gap: 1rem;
      justify-content: center;
      margin-top: 1.5rem;
    }
    
    @media (max-width: 768px) {
      .content-wrapper {
        padding: 1rem;
      }
    }
  `]
})
export class ActualizarRecaudacionComponent implements OnInit, OnDestroy {
  private fb = inject(FormBuilder);
  private route = inject(ActivatedRoute);
  private router = inject(Router);
  private contabilidadService = inject(ContabilidadService);
  private snackBar = inject(MatSnackBar);
  
  recaudacionForm: FormGroup;
  recaudacion: Recaudacion | null = null;
  maquinas: Maquina[] = [];
  loading = true;
  submitting = false;
  success = false;
  error = '';
  private subscriptions: Subscription[] = [];
  
  ngOnInit(): void {
    this.initForm();
    this.cargarMaquinas();
    this.cargarRecaudacion();
    this.setupCalculosAutomaticos();
  }
  
  ngOnDestroy(): void {
    this.subscriptions.forEach(sub => sub.unsubscribe());
  }
  
  private initForm(): void {
    this.recaudacionForm = this.fb.group({
      ID_Recaudacion: [''],
      ID_Maquina: [{ value: '', disabled: true }, Validators.required],
      Tipo_Comercio: [{ value: '', disabled: true }],
      Porcentaje_Comercio: [20, [Validators.min(0), Validators.max(100)]],
      Monto_Total: ['', [Validators.required, Validators.min(0.01)]],
      Monto_Comercio: [{ value: '0.00', disabled: true }],
      Monto_Empresa: [{ value: '0.00', disabled: true }],
      fecha: ['', Validators.required],
      detalle: ['']
    });
  }
  
  private cargarMaquinas(): void {
    this.contabilidadService.getMaquinasRecaudacion().subscribe({
      next: (data) => {
        this.maquinas = data;
      },
      error: () => {
        this.snackBar.error('Error al cargar máquinas', 'Cerrar');
      }
    });
  }
  
  private cargarRecaudacion(): void {
    const uuid = this.route.snapshot.params['uuid'];
    if (!uuid) {
      this.error = 'ID de recaudación no válido';
      this.loading = false;
      return;
    }
    
    this.contabilidadService.getRecaudacionById(uuid).subscribe({
      next: (data) => {
        if (data) {
          this.recaudacion = data;
          this.recaudacionForm.patchValue({
            ID_Recaudacion: data.ID_Recaudacion,
            ID_Maquina: data.ID_Maquina,
            Tipo_Comercio: data.Tipo_Comercio,
            Porcentaje_Comercio: data.Porcentaje_Comercio || 20,
            Monto_Total: data.Monto_Total,
            Monto_Comercio: data.Monto_Comercio,
            Monto_Empresa: data.Monto_Empresa,
            fecha: new Date(data.fecha).toISOString().slice(0, 16),
            detalle: data.detalle || ''
          });
        } else {
          this.error = 'Recaudación no encontrada';
        }
        this.loading = false;
      },
      error: (err) => {
        this.error = err.message || 'Error al cargar recaudación';
        this.loading = false;
      }
    });
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
  
  onSubmit(): void {
    if (this.recaudacionForm.invalid) {
      this.snackBar.open('Complete todos los campos correctamente', 'Cerrar', { duration: 3000 });
      return;
    }
    
    if (!confirm('¿Está seguro de guardar los cambios?')) return;
    
    this.submitting = true;
    const formValue = this.recaudacionForm.getRawValue();
    
    const data = {
      ID_Recaudacion: formValue.ID_Recaudacion,
      ID_Maquina: formValue.ID_Maquina,
      Tipo_Comercio: formValue.Tipo_Comercio,
      Porcentaje_Comercio: formValue.Tipo_Comercio === 'Mayorista' ? formValue.Porcentaje_Comercio : 0,
      Monto_Total: parseFloat(formValue.Monto_Total),
      Monto_Comercio: parseFloat(formValue.Monto_Comercio),
      Monto_Empresa: parseFloat(formValue.Monto_Empresa),
      fecha: new Date(formValue.fecha).toISOString().slice(0, 19).replace('T', ' '),
      detalle: formValue.detalle
    };
    
    this.contabilidadService.actualizarRecaudacion(data).subscribe({
      next: (response) => {
        if (response.success) {
          this.success = true;
          this.snackBar.open('Recaudación actualizada correctamente', 'Cerrar', { duration: 3000 });
          setTimeout(() => {
            this.router.navigate(['/contabilidad/consultar-recaudaciones']);
          }, 2000);
        } else {
          this.snackBar.open(response.message || 'Error al actualizar recaudación', 'Cerrar', { duration: 3000 });
        }
        this.submitting = false;
      },
      error: (err) => {
        this.snackBar.open(err.message || 'Error al actualizar recaudación', 'Cerrar', { duration: 3000 });
        this.submitting = false;
      }
    });
  }
  
  regresar(): void {
    this.router.navigate(['/contabilidad/consultar-recaudaciones']);
  }
}