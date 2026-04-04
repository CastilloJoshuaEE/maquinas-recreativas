/**
 * @fileoverview Componente de Recuperación de Contraseña
 * @description Permite a los usuarios recuperar su contraseña mediante email
 * @component RecuperarContrasenaComponent
 */

import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators, AbstractControl, ValidationErrors } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { ToastrService } from 'ngx-toastr';
import { UserService } from '@core/services/user.service';

@Component({
  selector: 'app-recuperar-contrasena',
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
    <div class="recuperacion-container">
      <mat-card>
        <mat-card-header>
          <mat-card-title>
            <h2>Recuperar Contraseña</h2>
          </mat-card-title>
        </mat-card-header>
        
        <mat-card-content>
          <form [formGroup]="recuperacionForm" (ngSubmit)="onSubmit()">
            <mat-form-field appearance="outline" class="full-width">
              <mat-label>Correo electrónico registrado</mat-label>
              <input matInput formControlName="email" placeholder="ejemplo@correo.com" type="email">
              <mat-icon matPrefix>email</mat-icon>
              <mat-error *ngIf="recuperacionForm.get('email')?.hasError('required')">
                Email requerido
              </mat-error>
              <mat-error *ngIf="recuperacionForm.get('email')?.hasError('email')">
                Email inválido
              </mat-error>
            </mat-form-field>

            <mat-form-field appearance="outline" class="full-width">
              <mat-label>Nueva contraseña</mat-label>
              <input matInput [type]="hidePassword ? 'password' : 'text'" formControlName="nueva_contrasena">
              <mat-icon matPrefix>lock</mat-icon>
              <mat-icon matSuffix (click)="hidePassword = !hidePassword">
                {{hidePassword ? 'visibility_off' : 'visibility'}}
              </mat-icon>
              <mat-error *ngIf="recuperacionForm.get('nueva_contrasena')?.hasError('required')">
                Nueva contraseña requerida
              </mat-error>
              <mat-error *ngIf="recuperacionForm.get('nueva_contrasena')?.hasError('minlength')">
                Mínimo 8 caracteres
              </mat-error>
            </mat-form-field>

            <mat-form-field appearance="outline" class="full-width">
              <mat-label>Repetir contraseña</mat-label>
              <input matInput [type]="hideConfirmPassword ? 'password' : 'text'" formControlName="repetir_contrasena">
              <mat-icon matPrefix>lock</mat-icon>
              <mat-icon matSuffix (click)="hideConfirmPassword = !hideConfirmPassword">
                {{hideConfirmPassword ? 'visibility_off' : 'visibility'}}
              </mat-icon>
              <mat-error *ngIf="recuperacionForm.get('repetir_contrasena')?.hasError('required')">
                Confirmar contraseña requerida
              </mat-error>
              <mat-error *ngIf="recuperacionForm.hasError('passwordMismatch')">
                Las contraseñas no coinciden
              </mat-error>
            </mat-form-field>

            <div class="form-actions">
              <button mat-raised-button color="primary" type="submit" [disabled]="recuperacionForm.invalid || loading">
                <mat-spinner diameter="20" *ngIf="loading"></mat-spinner>
                <span *ngIf="!loading">Actualizar contraseña</span>
              </button>
              <button mat-button type="button" routerLink="/auth/login">Volver al inicio</button>
            </div>
          </form>
        </mat-card-content>
      </mat-card>
    </div>
  `,
  styles: [`
    .recuperacion-container {
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
    
    @media (max-width: 768px) {
      .recuperacion-container {
        margin: 1rem;
      }
    }
  `]
})
export class RecuperarContrasenaComponent {
  private fb = inject(FormBuilder);
  private userService = inject(UserService);
  private router = inject(Router);
  private toastr = inject(ToastrService);
  
  recuperacionForm: FormGroup;
  loading = false;
  hidePassword = true;
  hideConfirmPassword = true;
  
  constructor() {
    this.recuperacionForm = this.fb.group({
      email: ['', [Validators.required, Validators.email]],
      nueva_contrasena: ['', [Validators.required, Validators.minLength(8)]],
      repetir_contrasena: ['', [Validators.required]]
    }, { validators: this.passwordMatchValidator });
  }
  
  private passwordMatchValidator(group: AbstractControl): ValidationErrors | null {
    const password = group.get('nueva_contrasena')?.value;
    const confirm = group.get('repetir_contrasena')?.value;
    return password === confirm ? null : { passwordMismatch: true };
  }
  
  onSubmit(): void {
    if (this.recuperacionForm.invalid) return;
    
    this.loading = true;
    const { email, nueva_contrasena } = this.recuperacionForm.value;
    
    this.userService.recoverPassword(email, nueva_contrasena).subscribe({
      next: (response) => {
        if (response.success) {
          this.toastr.success('Contraseña actualizada correctamente', 'Éxito');
          setTimeout(() => this.router.navigate(['/auth/login']), 2000);
        } else {
          this.toastr.error(response.message || 'Error al actualizar la contraseña', 'Error');
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