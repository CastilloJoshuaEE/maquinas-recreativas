/**
 * @fileoverview Componente de Chatbot Asistente
 * @description Asistente virtual interactivo para ayudar a los usuarios
 * @component ChatbotComponent
 */

import { Component, OnInit, OnDestroy, ElementRef, ViewChild, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { ApiService } from '@core/services/api.service';
import { API_ENDPOINTS } from '@core/constants/app.constants';

interface Message {
  text: string;
  sender: 'user' | 'bot';
  isButton?: boolean;
  onClick?: () => void;
}

interface UserInfo {
  nombres: string;
  apellidos: string;
  email: string;
  telefono: string;
  ci: string;
}

@Component({
  selector: 'app-chatbot',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule
  ],
  template: `
    <div class="chatbot-container" [class.minimized]="minimized">
      <div class="chatbot-header" (click)="toggleMinimize()">
        <div class="header-content">
          <mat-icon>smart_toy</mat-icon>
          <h3>Asistente Virtual</h3>
        </div>
        <button mat-icon-button class="minimize-btn">
          <mat-icon>{{ minimized ? 'add' : 'remove' }}</mat-icon>
        </button>
      </div>
      
      <div class="chatbot-content" *ngIf="!minimized">
        <div class="chatbot-messages" #messagesContainer>
          <div *ngFor="let msg of messages" class="message" [ngClass]="msg.sender">
            <div class="message-text">{{ msg.text }}</div>
            <button *ngIf="msg.isButton" class="message-button" (click)="msg.onClick()">
              Solicitar reactivación
            </button>
          </div>
          <div *ngIf="cargando" class="message bot typing">
            <div class="typing-indicator">
              <span></span><span></span><span></span>
            </div>
          </div>
          <div #scrollAnchor></div>
        </div>
        
        <div class="chatbot-options" *ngIf="showOptions && !cargando">
          <button *ngFor="let opt of options" class="option-button" (click)="handleOptionSelect(opt)">
            {{ opt }}
          </button>
        </div>
        
        <div class="chatbot-input" *ngIf="!showOptions || collectingInfo">
          <input type="text" 
                 [(ngModel)]="inputValue" 
                 (keyup.enter)="sendMessage()"
                 [placeholder]="inputPlaceholder"
                 [disabled]="cargando">
          <button (click)="sendMessage()" [disabled]="cargando || !inputValue.trim()">
            <mat-icon>send</mat-icon>
          </button>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .chatbot-container {
      position: fixed;
      bottom: 20px;
      right: 20px;
      width: 350px;
      height: 500px;
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
      display: flex;
      flex-direction: column;
      overflow: hidden;
      z-index: 1000;
      transition: all 0.3s ease;
    }
    
    .chatbot-container.minimized {
      height: 50px;
    }
    
    .chatbot-header {
      background: linear-gradient(135deg, #4f6bed, #3d55c3);
      color: white;
      padding: 0.75rem 1rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      cursor: pointer;
    }
    
    .header-content {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .header-content h3 {
      margin: 0;
      font-size: 1rem;
    }
    
    .minimize-btn {
      color: white;
    }
    
    .chatbot-content {
      flex: 1;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }
    
    .chatbot-messages {
      flex: 1;
      overflow-y: auto;
      padding: 1rem;
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
      background: #f5f5f5;
    }
    
    .message {
      max-width: 85%;
      padding: 0.5rem 0.75rem;
      border-radius: 12px;
      font-size: 0.85rem;
      line-height: 1.4;
    }
    
    .message.user {
      align-self: flex-end;
      background: #4f6bed;
      color: white;
      border-bottom-right-radius: 4px;
    }
    
    .message.bot {
      align-self: flex-start;
      background: white;
      border: 1px solid #e0e0e0;
      border-bottom-left-radius: 4px;
      color: #333;
    }
    
    .message.typing {
      background: white;
      padding: 0.5rem 1rem;
    }
    
    .typing-indicator {
      display: flex;
      gap: 4px;
      align-items: center;
    }
    
    .typing-indicator span {
      width: 8px;
      height: 8px;
      background: #999;
      border-radius: 50%;
      animation: typing 1.4s infinite ease-in-out;
    }
    
    .typing-indicator span:nth-child(1) { animation-delay: 0s; }
    .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
    .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
    
    @keyframes typing {
      0%, 60%, 100% { transform: translateY(0); }
      30% { transform: translateY(-6px); }
    }
    
    .message-button {
      margin-top: 0.5rem;
      padding: 0.25rem 0.5rem;
      background: #4f6bed;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 0.75rem;
    }
    
    .chatbot-options {
      padding: 0.75rem;
      background: #f8f9fa;
      border-top: 1px solid #e0e0e0;
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
      max-height: 200px;
      overflow-y: auto;
    }
    
    .option-button {
      padding: 0.5rem 0.75rem;
      background: white;
      border: 1px solid #e0e0e0;
      border-radius: 20px;
      cursor: pointer;
      text-align: left;
      font-size: 0.8rem;
      transition: all 0.2s;
    }
    
    .option-button:hover {
      background: #4f6bed;
      color: white;
      border-color: #4f6bed;
    }
    
    .chatbot-input {
      display: flex;
      gap: 0.5rem;
      padding: 0.75rem;
      background: white;
      border-top: 1px solid #e0e0e0;
    }
    
    .chatbot-input input {
      flex: 1;
      padding: 0.5rem;
      border: 1px solid #e0e0e0;
      border-radius: 20px;
      outline: none;
    }
    
    .chatbot-input input:focus {
      border-color: #4f6bed;
    }
    
    .chatbot-input button {
      background: #4f6bed;
      color: white;
      border: none;
      border-radius: 50%;
      width: 36px;
      height: 36px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }
    
    @media (max-width: 768px) {
      .chatbot-container {
        width: 90%;
        right: 5%;
        bottom: 10px;
        height: 450px;
      }
    }
  `]
})
export class ChatbotComponent implements OnInit, OnDestroy {
  private router = inject(Router);
  private apiService = inject(ApiService);
  
  @ViewChild('messagesContainer') messagesContainer!: ElementRef;
  @ViewChild('scrollAnchor') scrollAnchor!: ElementRef;
  
  messages: Message[] = [];
  inputValue = '';
  cargando = false;
  minimized = false;
  showOptions = true;
  collectingInfo: 'email' | 'datos' | null = null;
  
  inputPlaceholder = 'Escribe tu mensaje...';
  
  userInfo: UserInfo = {
    nombres: '',
    apellidos: '',
    email: '',
    telefono: '',
    ci: ''
  };
  
  currentFlow: string | null = null;
  currentFlowText = '';
  
  options = [
    "No puedo iniciar sesión y ya hice la solicitud para ingresar a trabajar",
    "No puedo iniciar sesión y ya trabajo en la empresa",
    "Quiero información sobre lo que hacen sobre las máquinas recreativas",
    "Tengo un problema técnico con una máquina, quiero que me ayuden",
    "Quiero reportar un problema con un comercio asociado, quiero que me ayuden"
  ];
  
  responses: { [key: string]: string } = {
    "No puedo iniciar sesión y ya hice la solicitud para ingresar a trabajar":
      "Hola, aún estamos revisando tu solicitud. Por favor espera a que te contactemos. El proceso puede tardar hasta 5 días hábiles.",
    "No puedo iniciar sesión y ya trabajo en la empresa":
      "Proporciona tu email que has registrado en la empresa para buscarte en nuestra base de datos",
    "Quiero información sobre lo que hacen sobre las máquinas recreativas":
      "Nuestras máquinas recreativas pasan por un proceso de ensamblaje, comprobación y distribución. Actualmente tenemos modelos clásicos y modernos con tecnología de última generación.",
    "Tengo un problema técnico con una máquina, quiero que me ayuden":
      "Gracias por informarnos. Primero necesitamos tus datos para poder ayudarte.",
    "Quiero reportar un problema con un comercio asociado, quiero que me ayuden":
      "Entendido. Para procesar tu solicitud necesitamos tus datos."
  };
  
  ngOnInit(): void {
    this.messages.push({
      text: "¡Hola! Soy el asistente virtual de RecreaSys. ¿En qué puedo ayudarte hoy?",
      sender: 'bot'
    });
  }
  
  ngOnDestroy(): void {
    // Limpiar recursos
  }
  
  toggleMinimize(): void {
    this.minimized = !this.minimized;
  }
  
  sendMessage(): void {
    if (!this.inputValue.trim()) return;
    
    const userMessage = this.inputValue.trim();
    this.messages.push({ text: userMessage, sender: 'user' });
    this.inputValue = '';
    this.scrollToBottom();
    
    this.processUserInput(userMessage);
  }
  
  handleOptionSelect(option: string): void {
    this.inputValue = option;
    this.sendMessage();
  }
  
  private processUserInput(input: string): void {
    this.cargando = true;
    
    setTimeout(() => {
      this.cargando = false;
      
      // Manejo de flujo de recolección de información
      if (this.collectingInfo === 'email') {
        this.handleEmailCollection(input);
        return;
      }
      
      if (this.collectingInfo === 'datos') {
        this.handleDataCollection(input);
        return;
      }
      
      // Buscar opción coincidente
      let matchedOption = this.options.find(opt => 
        input.toLowerCase().includes(opt.toLowerCase()) || 
        opt.toLowerCase().includes(input.toLowerCase())
      );
      
      if (matchedOption) {
        this.handleOptionResponse(matchedOption);
      } else {
        this.messages.push({
          text: "Lo siento, no entendí tu consulta. Por favor selecciona una de las opciones disponibles.",
          sender: 'bot'
        });
        this.showOptions = true;
      }
      
      this.scrollToBottom();
    }, 500);
  }
  
  private handleEmailCollection(email: string): void {
    this.apiService.post(API_ENDPOINTS.SEARCH_BY_EMAIL, { email }).subscribe({
      next: (response) => {
        if (response.success && response.usuario) {
          const userData = response.usuario;
          
          if (userData.estado === 'Inhabilitado') {
            this.messages.push({
              text: "Hemos detectado que tu cuenta ha sido inhabilitada. ¿Deseas hacer la solicitud para reactivarla?",
              sender: 'bot'
            });
            this.messages.push({
              text: "Haz clic aquí para iniciar el proceso de reactivación.",
              sender: 'bot',
              isButton: true,
              onClick: () => {
                this.router.navigate(['/reportes/gestion'], {
                  state: { userData, isDisabledUser: true }
                });
              }
            });
          } else {
            this.messages.push({
              text: `Tu cuenta está activa. Intenta iniciar sesión con tu usuario asignado: ${userData.usuario_asignado}`,
              sender: 'bot'
            });
          }
        } else {
          this.messages.push({
            text: "No se encontró ningún usuario con ese correo.",
            sender: 'bot'
          });
        }
        this.collectingInfo = null;
        this.showOptions = true;
        this.scrollToBottom();
      },
      error: () => {
        this.messages.push({
          text: "Hubo un error al buscar tu información. Intenta más tarde.",
          sender: 'bot'
        });
        this.collectingInfo = null;
        this.showOptions = true;
        this.scrollToBottom();
      }
    });
  }
  
  private handleDataCollection(input: string): void {
    const lowerInput = input.toLowerCase().trim();
    
    if (lowerInput === 'si' || lowerInput === 'sí') {
      this.messages.push({
        text: "OK, hemos recibido tu información. Nuestro administrador se pondrá en contacto contigo pronto.",
        sender: 'bot'
      });
      this.sendReportToAdmin();
      this.collectingInfo = null;
      setTimeout(() => {
        this.messages.push({
          text: "¿Necesitas ayuda con algo más?",
          sender: 'bot'
        });
        this.showOptions = true;
        this.scrollToBottom();
      }, 2000);
      return;
    }
    
    if (lowerInput === 'no') {
      this.messages.push({
        text: "Entendido. Por favor, vuelve a ingresar tus datos: nombres, apellidos, email, teléfono y cédula.",
        sender: 'bot'
      });
      this.userInfo = { nombres: '', apellidos: '', email: '', telefono: '', ci: '' };
      return;
    }
    
    if (!this.userInfo.nombres) {
      this.userInfo.nombres = input;
      this.messages.push({ text: "Gracias. Ahora por favor ingresa tus apellidos:", sender: 'bot' });
    } else if (!this.userInfo.apellidos) {
      this.userInfo.apellidos = input;
      this.messages.push({ text: "Perfecto. Ahora necesitamos tu email:", sender: 'bot' });
    } else if (!this.userInfo.email) {
      this.userInfo.email = input;
      this.messages.push({ text: "Ahora, por favor ingresa tu teléfono:", sender: 'bot' });
    } else if (!this.userInfo.telefono) {
      this.userInfo.telefono = input;
      this.messages.push({ text: "Por último, ingresa tu número de cédula:", sender: 'bot' });
    } else if (!this.userInfo.ci) {
      this.userInfo.ci = input;
      this.messages.push({
        text: `¿Esta información es correcta?\nNombres: ${this.userInfo.nombres}\nApellidos: ${this.userInfo.apellidos}\nEmail: ${this.userInfo.email}\nTeléfono: ${this.userInfo.telefono}\nCI: ${this.userInfo.ci}\nResponde SI o NO`,
        sender: 'bot'
      });
    }
    
    this.scrollToBottom();
  }
  
  private handleOptionResponse(option: string): void {
    const responseText = this.responses[option];
    this.messages.push({ text: responseText, sender: 'bot' });
    
    if (option === "No puedo iniciar sesión y ya trabajo en la empresa") {
      this.collectingInfo = 'email';
      this.messages.push({ text: "Por favor ingresa tu correo electrónico:", sender: 'bot' });
      this.showOptions = false;
    } else if (option === "Tengo un problema técnico con una máquina, quiero que me ayuden" ||
               option === "Quiero reportar un problema con un comercio asociado, quiero que me ayuden") {
      this.currentFlow = option.includes('comercio') ? 'commerce_issue' : 'tech_issue';
      this.currentFlowText = option;
      this.collectingInfo = 'datos';
      this.messages.push({ text: "Por favor ingresa tus nombres:", sender: 'bot' });
      this.showOptions = false;
    } else {
      setTimeout(() => {
        this.messages.push({
          text: "¿Necesitas ayuda con algo más?",
          sender: 'bot'
        });
        this.showOptions = true;
        this.scrollToBottom();
      }, 1500);
    }
    
    this.scrollToBottom();
  }
  
  private sendReportToAdmin(): void {
    this.apiService.get('/usuarios/por-tipo?tipo=Administrador').subscribe({
      next: (response: any) => {
        if (response.success && response.usuarios?.length > 0) {
          const admin = response.usuarios[0];
          
          this.apiService.post(API_ENDPOINTS.REPORTES_CREAR, {
            ID_Usuario_Emisor: 'chatbot_temp',
            ID_Usuario_Destinatario: admin.ID_Usuario,
            descripcion: `Nuevo reporte desde chatbot (${this.currentFlowText}):\nNombre: ${this.userInfo.nombres} ${this.userInfo.apellidos}\nEmail: ${this.userInfo.email}\nTeléfono: ${this.userInfo.telefono}\nCI: ${this.userInfo.ci}`,
            estado: 'Pendiente'
          }).subscribe();
        }
      },
      error: () => {
        console.error('Error al enviar reporte al administrador');
      }
    });
  }
  
  private scrollToBottom(): void {
    setTimeout(() => {
      if (this.scrollAnchor) {
        this.scrollAnchor.nativeElement.scrollIntoView({ behavior: 'smooth' });
      }
    }, 100);
  }
}