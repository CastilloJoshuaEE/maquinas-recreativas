/**
 * @fileoverview Chatbot Flotante
 * @description Botón flotante que abre/cierra el chatbot inline sin navegación.
 *
 * FIX: el componente anterior llamaba router.navigate('/auth/chatbot') que
 * caía en el wildcard **  →  /auth/login.
 * Ahora el botón flotante muestra u oculta el ChatbotComponent directamente
 * en el DOM, sin redirigir a ninguna ruta.
 */

import { Component, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { ChatbotComponent } from '@shared/ui/chatbot/chatbot';

@Component({
  selector: 'app-chatbot-floating',
  standalone: true,
  imports: [CommonModule, MatButtonModule, MatIconModule, ChatbotComponent],
  template: `
    <!-- Widget del chatbot (visible cuando está abierto) -->
    <app-chatbot *ngIf="isOpen()"></app-chatbot>

    <!-- Botón flotante -->
    <div class="chatbot-floating" (click)="toggle()">
      <mat-icon>{{ isOpen() ? 'close' : 'smart_toy' }}</mat-icon>
      <span *ngIf="!isOpen()">¿Necesitas ayuda?</span>
      <span *ngIf="isOpen()">Cerrar</span>
    </div>
  `,
  styles: [`
    .chatbot-floating {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background: linear-gradient(135deg, #4f6bed, #3d55c3);
      color: white;
      border-radius: 50px;
      padding: 12px 20px;
      display: flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      z-index: 1100;
      transition: transform 0.2s;
      user-select: none;
    }
    .chatbot-floating:hover { transform: scale(1.05); }
    .chatbot-floating mat-icon { font-size: 24px; width: 24px; height: 24px; }

    /* Desplazar el chatbot widget un poco arriba del botón */
    :host ::ng-deep .chatbot-container {
      bottom: 80px;
      right: 20px;
    }
  `]
})
export class ChatbotFloatingComponent {
  isOpen = signal(false);
  toggle(): void { this.isOpen.update(v => !v); }
}