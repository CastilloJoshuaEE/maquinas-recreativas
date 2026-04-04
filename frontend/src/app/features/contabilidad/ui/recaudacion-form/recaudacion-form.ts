/**
 * @fileoverview Formulario de Recaudación (Reutilizable)
 * @description Componente reutilizable para formularios de recaudación
 * @component RecaudacionFormComponent
 */

import { Component, Input, Output, EventEmitter, OnInit, OnDestroy, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { Subscription } from 'rxjs';
import { ContabilidadService } from '../../services/contabilidad.service';
import { Comercio, Recaudacion } from '@core/models/recaudacion.model';
import { Maquina } from '@core/models/maquina.model';

@Component({
  selector: 'app-recaudacion-form',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule,
    MatButtonModule,
    MatIconModule
  ],
  template: `
    <form [formGroup]="recaudacionForm" (ngSubmit)="onSubmit()">
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
        <mat-select formControlName="ID_Maquina">
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
        <button mat-raised-button color="primary" type="submit" [disabled]="recaudacionForm.invalid || submitting">
          <span>{{ submitLabel }}</span>
        </button>
        <button mat-button type="button" (click)="onCancel.emit()">Cancelar</button>
      </div>
    </form>
  `,
  styles: [`
    .full-width {
      width: 100%;
      margin-bottom: 1rem;
    }
    
    .form-actions {
      display: flex;
      gap: 1rem;
      justify-content: flex-end;
      margin-top: 1.5rem;
    }
  `]
})
export class RecaudacionFormComponent implements OnInit, OnDestroy {
  @Input() recaudacionData?: Recaudacion;
  @Input() submitLabel = 'Guardar';
  @Output() submitForm = new EventEmitter<any>();
  @Output() onCancel = new EventEmitter<void>();
  
  private fb = inject(FormBuilder);
  private contabilidadService = inject(ContabilidadService);
  
  recaudacionForm: FormGroup;
  comercios: Comercio[] = [];
  maquinas: Maquina[] = [];
  cargandoMaquinas = false;
  submitting = false;
  private subscriptions: Subscription[] = [];
  
  ngOnInit(): void {
    this.initForm();
    this.cargarComercios();
    this.setupCalculosAutomaticos();
    
    if (this.recaudacionData) {
      this.cargarDatosParaEdicion();
    }
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
      error: () => {
        console.error('Error al cargar comercios');
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
      },
      error: () => {
        this.maquinas = [];
        this.cargandoMaquinas = false;
      }
    });
  }
  
  private cargarDatosParaEdicion(): void {
    if (this.recaudacionData) {
      this.recaudacionForm.patchValue({
        ID_Comercio: this.recaudacionData.ID_Comercio,
        ID_Maquina: this.recaudacionData.ID_Maquina,
        Tipo_Comercio: this.recaudacionData.Tipo_Comercio,
        Porcentaje_Comercio: this.recaudacionData.Porcentaje_Comercio || 20,
        Monto_Total: this.recaudacionData.Monto_Total,
        Monto_Comercio: this.recaudacionData.Monto_Comercio,
        Monto_Empresa: this.recaudacionData.Monto_Empresa,
        fecha: new Date(this.recaudacionData.fecha).toISOString().slice(0, 16),
        detalle: this.recaudacionData.detalle || ''
      });
      
      if (this.recaudacionData.ID_Comercio) {
        this.cargarMaquinasPorComercio(this.recaudacionData.ID_Comercio);
      }
    }
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
    if (this.recaudacionForm.valid) {
      const formValue = this.recaudacionForm.getRawValue();
      this.submitForm.emit(formValue);
    }
  }
}