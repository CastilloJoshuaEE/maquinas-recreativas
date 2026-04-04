/**
 * @fileoverview Componente de Actualización de Usuario
 * @description Permite actualizar el nombre de usuario mediante email
 * @component ActualizarUsuarioComponent
 */

import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { ToastrService } from 'ngx-toastr';
import { UserService } from '@core/services/user.service';

@Component({
  selector: 'app-actualizar-usuario',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    RouterLink,
    MatCardModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatProgressSpinnerModule
  ],
  template: `
    <div class="actualizar-container">
      <mat-card>
        <mat-card-header>
          <mat-card-title>
            <h2>Actualizar Usuario Asignado</h2>
          </mat-card-title>
        </mat-card-header>
        
        <mat-card-content>
          <form [formGroup]="actualizarForm" (ngSubmit)="onSubmit()">
            <mat-form-field appearance="outline" class="full-width">
              <mat-label>Correo electrónico registrado</mat-label>
              <input matInput formControlName="email" placeholder="ejemplo@correo.com" type="email">
              <mat-icon matPrefix>email</mat-icon>
              <mat-error *ngIf="actualizarForm.get('email')?.hasError('required')">
                Email requerido
              </mat-error>
              <mat-error *ngIf="actualizarForm.get('email')?.hasError('email')">
                Email inválido
              </mat-error>
            </mat-form-field>

            <mat-form-field appearance="outline" class="full-width">
              <mat-label>Nuevo usuario asignado</mat-label>
              <input matInput formControlName="usuario_asignado" placeholder="Nuevo nombre de usuario">
              <mat-icon matPrefix>account_circle</mat-icon>
              <mat-error *ngIf="actualizarForm.get('usuario_asignado')?.hasError('required')">
                Usuario requerido
              </mat-error>
              <mat-error *ngIf="actualizarForm.get('usuario_asignado')?.hasError('maxlength')">
                Máximo 15 caracteres
              </mat-error>
            </mat-form-field>

            <div class="form-actions">
              <button mat-raised-button color="primary" type="submit" [disabled]="actualizarForm.invalid || loading">
                <mat-spinner diameter="20" *ngIf="loading"></mat-spinner>
                <span *ngIf="!loading">Actualizar usuario</span>
              </button>
              <button mat-button type="button" routerLink="/auth/login">Volver al inicio</button>
            </div>
          </form>
        </mat-card-content>
      </mat-card>
    </div>
  `,
  styles: [`
    .actualizar-container {
      max-width: 500px;
      margin: 2rem auto;
      padding: 1rem;
    }
    
    .full-width {
      width: 100%;
      margin-bottom: 1rem;
    }
    
    .form-actions {
      display: flex;
      gap: 1rem;
      justify-content: center;
      margin-top: 1.5rem;
    }
  `]
})
export class ActualizarUsuarioComponent {
  private fb = inject(FormBuilder);
  private userService = inject(UserService);
  private router = inject(Router);
  private toastr = inject(ToastrService);
  
  actualizarForm: FormGroup;
  loading = false;
  
  constructor() {
    this.actualizarForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      usuario_asignado: ['', [Validators.required, Validators.maxLength(15)]]
    });
  }
  
  onSubmit(): void {
    if (this.actualizarForm.invalid) return;
    
    if (!confirm('¿Está seguro de guardar los cambios?')) return;
    
    this.loading = true;
    const { email, usuario_asignado } = this.actualizarForm.value;
    
    this.userService.recoverUsername(email, usuario_asignado).subscribe({
      next: (response) => {
        if (response.success) {
          this.toastr.success('Usuario actualizado correctamente', 'Éxito');
          setTimeout(() => this.router.navigate(['/auth/login']), 2000);
        } else {
          this.toastr.error(response.message || 'Error al actualizar el usuario', 'Error');
        }
        this.loading = false;
      },
      error: (error) => {
        this.toastr.error(error.message || 'Error de conexión con el servidor', 'Error');
        this.loading = false;
      }
    });
  }
}