import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-chatbot-floating',
  standalone: true,
  imports: [CommonModule, MatButtonModule, MatIconModule],
  template: `
    <div class="chatbot-floating" (click)="openChatbot()">
      <mat-icon>smart_toy</mat-icon>
      <span>¿Necesitas ayuda?</span>
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
      z-index: 1000;
      transition: transform 0.2s;
    }
    .chatbot-floating:hover {
      transform: scale(1.05);
    }
    .chatbot-floating mat-icon {
      font-size: 24px;
    }
  `]
})
export class ChatbotFloatingComponent {
  private router = inject(Router);
  
  openChatbot(): void {
    this.router.navigate(['/auth/chatbot']);
  }
}