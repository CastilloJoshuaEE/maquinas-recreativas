/**
 * @fileoverview Formulario de Comercio
 * @description Componente reutilizable para registrar nuevos comercios
 * @component ComercioFormComponent
 */

import { Component, Output, EventEmitter, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { LogisticaService } from '../../services/logistica';

@Component({
  selector: 'app-comercio-form',
  standalone: true,
  imports: [
    CommonModule, ReactiveFormsModule, MatFormFieldModule, MatInputModule,
    MatSelectModule, MatButtonModule, MatIconModule, MatProgressSpinnerModule
  ],
  templateUrl: './comercio-form.html',
  styleUrls: ['./comercio-form.css']
})
export class ComercioFormComponent {
  @Output() onClose = new EventEmitter<void>();
  @Output() onSuccess = new EventEmitter<void>();
  
  private fb = inject(FormBuilder);
  private logisticaService = inject(LogisticaService);
  private snackBar = inject(MatSnackBar);
  
  comercioForm: FormGroup;
  submitting = false;
  error = '';
  
  constructor() {
    this.comercioForm = this.fb.group({
      nombre: ['', Validators.required],
      tipo: ['Minorista', Validators.required],
      direccion: ['', Validators.required],
      telefono: ['', [Validators.required, Validators.pattern(/^\d{10}$/)]]
    });
  }
  
  onSubmit(): void {
    if (this.comercioForm.invalid) {
      this.snackBar.open('Complete todos los campos correctamente', 'Cerrar', { duration: 3000 });
      return;
    }
    this.submitting = true;
    this.error = '';
    
    this.logisticaService.registrarComercio(this.comercioForm.value).subscribe({
      next: (success) => {
        if (success) {
          this.snackBar.open('Comercio registrado correctamente', 'Cerrar', { duration: 3000 });
          this.onSuccess.emit();
        } else {
          this.error = 'Error al registrar el comercio';
        }
        this.submitting = false;
      },
      error: (err) => {
        if (err.message && err.message.includes('Duplicate entry')) {
          this.error = 'Ya existe un comercio con ese nombre';
        } else {
          this.error = err.message || 'Error al registrar el comercio';
        }
        this.submitting = false;
      }
    });
  }
}