/**
 * @fileoverview Layout de Autenticación
 * @description Layout para páginas de autenticación (login, registro)
 * @component AuthLayoutComponent
 */

import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterOutlet } from '@angular/router';

@Component({
  selector: 'app-auth-layout',
  standalone: true,
  imports: [CommonModule, RouterOutlet],
  template: `
    <div class="auth-layout">
      <div class="auth-container">
        <router-outlet></router-outlet>
      </div>
    </div>
  `,
  styles: [`
    .auth-layout {
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 2rem;
    }
    
    .auth-container {
      width: 100%;
      max-width: 500px;
    }
  `]
})
export class AuthLayoutComponent {}