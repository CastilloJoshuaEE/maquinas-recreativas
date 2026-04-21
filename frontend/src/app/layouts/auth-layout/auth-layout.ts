/**
 * @fileoverview Layout de Autenticación
 * @description Layout para páginas de login/registro sin header principal.
 *
 * FIX: se importa ChatbotFloatingComponent para que el chatbot funcione
 * en todas las páginas públicas (sin necesidad de ruta /auth/chatbot).
 */

import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterOutlet } from '@angular/router';
import { ChatbotFloatingComponent } from '@shared/ui/chatbot-floating/chatbot-floating';
import { AccessibilityWidgetComponent } from '@shared/ui/accessibility-widget/accessibility-widget';

@Component({
  selector: 'app-auth-layout',
  standalone: true,
  imports: [CommonModule, RouterOutlet, ChatbotFloatingComponent, AccessibilityWidgetComponent],
  templateUrl: './auth-layout.html',
  styleUrls: ['./auth-layout.css']
})
export class AuthLayoutComponent {}