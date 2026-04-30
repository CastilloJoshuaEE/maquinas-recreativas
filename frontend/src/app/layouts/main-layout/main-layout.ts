/**
 * @fileoverview Layout Principal
 * @description Layout principal con header y contenido
 * @component MainLayoutComponent
 */

import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterOutlet } from '@angular/router';
import { ChatbotFloatingComponent } from '@shared/ui/chatbot-floating/chatbot-floating';
import { AccessibilityWidgetComponent } from '@shared/ui/accessibility-widget/accessibility-widget';

@Component({
  selector: 'app-main-layout',
  standalone: true,
  imports: [CommonModule, RouterOutlet, ChatbotFloatingComponent, AccessibilityWidgetComponent],
  templateUrl: './main-layout.html',
  styleUrls: ['./main-layout.css']
})
export class MainLayoutComponent {}