/**
 * @fileoverview Formulario de Comercio
 * @description Componente reutilizable para registrar nuevos comercios
 * @component ComercioFormComponent
 */

import { Component, Output, EventEmitter, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { MatDialogModule, MatDialogRef } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { LogisticaService } from '../../services/logistica.service';

@Component({
  selector: 'app-comercio-form',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatDialogModule,
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
          <h2>Registrar Nuevo Comercio</h2>
          <button class="modal-close" (click)="onClose.emit()">×</button>
        </div>
        
        <div class="modal-body">
          <div *ngIf="error" class="error-message">
            {{ error }}
          </div>
          
          <form [formGroup]="comercioForm" (ngSubmit)="onSubmit()">
            <mat-form-field appearance="outline" class="full-width">
              <mat-label>Nombre</mat-label>
              <input matInput formControlName="nombre" placeholder="Ingrese el nombre del comercio">
              <mat-icon matPrefix>store</mat-icon>
              <mat-error *ngIf="comercioForm.get('nombre')?.hasError('required')">
                Nombre requerido
              </mat-error>
            </mat-form-field>

            <mat-form-field appearance="outline" class="full-width">
              <mat-label>Tipo</mat-label>
              <mat-select formControlName="tipo">
                <mat-option value="Minorista">Minorista</mat-option>
                <mat-option value="Mayorista">Mayorista</mat-option>
              </mat-select>
              <mat-icon matPrefix>category</mat-icon>
              <mat-error *ngIf="comercioForm.get('tipo')?.hasError('required')">
                Tipo requerido
              </mat-error>
            </mat-form-field>

            <mat-form-field appearance="outline" class="full-width">
              <mat-label>Dirección</mat-label>
              <input matInput formControlName="direccion" placeholder="Ingrese la dirección completa">
              <mat-icon matPrefix>location_on</mat-icon>
              <mat-error *ngIf="comercioForm.get('direccion')?.hasError('required')">
                Dirección requerida
              </mat-error>
            </mat-form-field>

            <mat-form-field appearance="outline" class="full-width">
              <mat-label>Teléfono</mat-label>
              <input matInput formControlName="telefono" placeholder="0987654321" maxlength="10">
              <mat-icon matPrefix>phone</mat-icon>
              <mat-error *ngIf="comercioForm.get('telefono')?.hasError('required')">
                Teléfono requerido
              </mat-error>
              <mat-error *ngIf="comercioForm.get('telefono')?.hasError('pattern')">
                Teléfono debe tener 10 dígitos
              </mat-error>
            </mat-form-field>

            <div class="form-actions">
              <button mat-raised-button color="primary" type="submit" [disabled]="comercioForm.invalid || submitting">
                <mat-spinner diameter="20" *ngIf="submitting"></mat-spinner>
                <span *ngIf="!submitting">Registrar Comercio</span>
              </button>
              <button mat-button type="button" (click)="onClose.emit()">Cancelar</button>
            </div>
          </form>
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
      max-width: 500px;
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
    
    .error-message {
      padding: 0.75rem;
      background: #f8d7da;
      color: #721c24;
      border-radius: 8px;
      margin-bottom: 1rem;
    }
    
    @media (max-width: 768px) {
      .form-actions {
        flex-direction: column;
      }
      
      .form-actions button {
        width: 100%;
      }
    }
  `]
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