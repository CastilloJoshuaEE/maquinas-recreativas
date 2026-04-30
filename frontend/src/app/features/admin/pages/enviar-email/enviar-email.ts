/**
 * @fileoverview Enviar Email — Panel de Administrador
 * @description Formulario para que el administrador envíe emails a cualquier destinatario
 * @component EnviarEmailComponent
 *
 * Ruta: /admin/enviar-email
 */

import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBar } from '@angular/material/snack-bar';
import { AdminHeaderComponent } from '@shared/ui/admin-header/admin-header';
import { ApiService } from '@core/services/api';

@Component({
  selector: 'app-enviar-email',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatCardModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    AdminHeaderComponent
  ],
  templateUrl: './enviar-email.html',
  styleUrls: ['./enviar-email.css']
})
export class EnviarEmailComponent {
  private fb        = inject(FormBuilder);
  private apiService = inject(ApiService);
  private snackBar  = inject(MatSnackBar);
  private router    = inject(Router);

  enviando   = false;
  enviado    = false;
  errorMsg   = '';

  emailForm: FormGroup = this.fb.group({
    destinatarioEmail:  ['', [Validators.required, Validators.email]],
    destinatarioNombre: [''],
    asunto:             ['', [Validators.required, Validators.minLength(3)]],
    mensaje:            ['', [Validators.required, Validators.minLength(10)]]
  });

  enviar(): void {
    if (this.emailForm.invalid) {
      this.emailForm.markAllAsTouched();
      return;
    }

    this.enviando = true;
    this.errorMsg = '';
    this.enviado  = false;

    const payload = this.emailForm.value;

    this.apiService.post('/administrador/enviar-email', payload).subscribe({
      next: (response) => {
        if (response?.success) {
          this.enviado = true;
          this.snackBar.open(' Email enviado correctamente', 'Cerrar', { duration: 4000 });
          this.emailForm.reset();
        } else {
          this.errorMsg = response?.['error'] || 'No se pudo enviar el email';
        }
        this.enviando = false;
      },
      error: (err) => {
        this.errorMsg = err?.message || 'Error al conectar con el servidor';
        this.enviando = false;
      }
    });
  }

  limpiar(): void {
    this.emailForm.reset();
    this.errorMsg = '';
    this.enviado  = false;
  }

  regresar(): void {
    this.router.navigate(['/admin/dashboard']);
  }
}