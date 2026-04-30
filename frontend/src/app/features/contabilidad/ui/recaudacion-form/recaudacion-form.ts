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
import { ContabilidadService } from '../../services/contabilidad';
import { Comercio, Recaudacion } from '@core/models/recaudacion.model';
import { Maquina } from '@core/models/maquina.model';

@Component({
  selector: 'app-recaudacion-form',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, MatFormFieldModule, MatInputModule, MatSelectModule, MatButtonModule, MatIconModule],
  templateUrl: './recaudacion-form.html',
  styleUrls: ['./recaudacion-form.css']
})
export class RecaudacionFormComponent implements OnInit, OnDestroy {
  @Input() recaudacionData?: Recaudacion;
  @Input() submitLabel = 'Guardar';
  @Output() submitForm = new EventEmitter<any>();
  @Output() onCancel = new EventEmitter<void>();
  
  private fb = inject(FormBuilder);
  private contabilidadService = inject(ContabilidadService);
  
  recaudacionForm!: FormGroup;
  comercios: Comercio[] = [];
  maquinas: Maquina[] = [];
  cargandoMaquinas = false;
  submitting = false;
  private subscriptions: Subscription[] = [];
  
  ngOnInit(): void {
    this.initForm();
    this.cargarComercios();
    this.setupCalculosAutomaticos();
    if (this.recaudacionData) this.cargarDatosParaEdicion();
  }
  
  ngOnDestroy(): void { this.subscriptions.forEach(sub => sub.unsubscribe()); }
  
  private initForm(): void {
    this.recaudacionForm = this.fb.group({
      ID_Comercio: ['', Validators.required], ID_Maquina: ['', Validators.required],
      Tipo_Comercio: [{ value: '', disabled: true }],
      Porcentaje_Comercio: [20, [Validators.min(0), Validators.max(100)]],
      Monto_Total: ['', [Validators.required, Validators.min(0.01)]],
      Monto_Comercio: [{ value: '0.00', disabled: true }],
      Monto_Empresa: [{ value: '0.00', disabled: true }],
      fecha: [this.getFechaHoraActual(), Validators.required], detalle: ['']
    });
  }
  
  private getFechaHoraActual(): string {
    const ahora = new Date();
    ahora.setMinutes(ahora.getMinutes() - ahora.getTimezoneOffset());
    return ahora.toISOString().slice(0, 16);
  }
  
  private cargarComercios(): void {
    this.contabilidadService.getComercios().subscribe({ next: (data) => { this.comercios = data; } });
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
      next: (data) => { this.maquinas = data; this.cargandoMaquinas = false; },
      error: () => { this.maquinas = []; this.cargandoMaquinas = false; }
    });
  }
  
  private cargarDatosParaEdicion(): void {
    if (this.recaudacionData) {
      this.recaudacionForm.patchValue({
        ID_Comercio: this.recaudacionData.ID_Comercio, ID_Maquina: this.recaudacionData.ID_Maquina,
        Tipo_Comercio: this.recaudacionData.Tipo_Comercio,
        Porcentaje_Comercio: this.recaudacionData.Porcentaje_Comercio || 20,
        Monto_Total: this.recaudacionData.Monto_Total, Monto_Comercio: this.recaudacionData.Monto_Comercio,
        Monto_Empresa: this.recaudacionData.Monto_Empresa,
        fecha: new Date(this.recaudacionData.fecha).toISOString().slice(0, 16),
        detalle: this.recaudacionData.detalle || ''
      });
      if (this.recaudacionData.ID_Comercio) this.cargarMaquinasPorComercio(this.recaudacionData.ID_Comercio);
    }
  }
  
  private setupCalculosAutomaticos(): void {
    const montoTotalSub = this.recaudacionForm.get('Monto_Total')?.valueChanges.subscribe(() => this.calcularMontos());
    const porcentajeSub = this.recaudacionForm.get('Porcentaje_Comercio')?.valueChanges.subscribe(() => this.calcularMontos());
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
      this.recaudacionForm.patchValue({ Monto_Comercio: montoComercio.toFixed(2), Monto_Empresa: montoEmpresa.toFixed(2) }, { emitEvent: false });
    } else {
      this.recaudacionForm.patchValue({ Monto_Comercio: '0.00', Monto_Empresa: montoTotal.toFixed(2) }, { emitEvent: false });
    }
  }
  
  onSubmit(): void {
    if (this.recaudacionForm.valid) this.submitForm.emit(this.recaudacionForm.getRawValue());
  }
}