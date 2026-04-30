/**
 * @fileoverview Chatbot Flotante
 * @description Botón flotante que abre/cierra el chatbot inline sin navegación.
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
  templateUrl: './chatbot-floating.html',
  styleUrls: ['./chatbot-floating.css']
})
export class ChatbotFloatingComponent {
  isOpen = signal(false);
  toggle(): void { this.isOpen.update(v => !v); }
}