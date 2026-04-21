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
import { ApiService } from '@core/services/api';
import { API_ENDPOINTS } from '@core/constants/app.constants';

interface Message { text: string; sender: 'user' | 'bot'; isButton?: boolean; onClick?: () => void; }
interface UserInfo { 
  nombres: string; 
  apellidos: string; 
  email: string; 
  telefono: string; 
  ci: string; 
  detalle: string; 
}

@Component({
  selector: 'app-chatbot',
  standalone: true,
  imports: [CommonModule, FormsModule, MatButtonModule, MatIconModule, MatProgressSpinnerModule],
  templateUrl: './chatbot.html',
  styleUrls: ['./chatbot.css']
})
export class ChatbotComponent implements OnInit, OnDestroy {
  private router = inject(Router);
  private apiService = inject(ApiService);
  @ViewChild('scrollAnchor') scrollAnchor!: ElementRef;
  
  messages: Message[] = [];
  inputValue = '';
  cargando = false;
  minimized = false;
  showOptions = true;
  collectingInfo: 'email' | 'datos' | 'preguntar_registro' | 'email_existente' | 'confirmar_usuario' | 'ofrecer_registro' | 'datos_nuevo' | null = null;
  inputPlaceholder = 'Escribe tu mensaje...';
  userInfo: UserInfo = { nombres: '', apellidos: '', email: '', telefono: '', ci: '', detalle: '' };
  currentFlow: string | null = null;
  currentFlowText = '';
  usuarioRegistrado: boolean | null = null;

  options = [
    "No puedo iniciar sesión y ya hice la solicitud para ingresar a trabajar",
    "No puedo iniciar sesión y ya trabajo en la empresa",
    "Quiero información sobre lo que hacen sobre las máquinas recreativas",
    "Tengo un problema técnico con una máquina, quiero que me ayuden",
    "Quiero reportar un problema con un comercio asociado, quiero que me ayuden"
  ];
  
  responses: { [key: string]: string } = {
    "No puedo iniciar sesión y ya hice la solicitud para ingresar a trabajar": "Hola, aún estamos revisando tu solicitud. Por favor espera a que te contactemos. El proceso puede tardar hasta 5 días hábiles.",
    "No puedo iniciar sesión y ya trabajo en la empresa": "Proporciona tu email que has registrado en la empresa para buscarte en nuestra base de datos",
    "Quiero información sobre lo que hacen sobre las máquinas recreativas": "Nuestras máquinas recreativas pasan por un proceso de ensamblaje, comprobación y distribución. Actualmente tenemos modelos clásicos y modernos con tecnología de última generación.",
    "Tengo un problema técnico con una máquina, quiero que me ayuden": "¿Ya estás registrado en el sistema? Responde SI o NO",
    "Quiero reportar un problema con un comercio asociado, quiero que me ayuden": "¿Ya estás registrado en el sistema? Responde SI o NO"
  };
  
  ngOnInit(): void { this.messages.push({ text: "¡Hola! Soy el asistente virtual de RecreaSys. ¿En qué puedo ayudarte hoy?", sender: 'bot' }); }
  ngOnDestroy(): void {}
  toggleMinimize(): void { this.minimized = !this.minimized; }
  
  sendMessage(): void {
    if (!this.inputValue.trim()) return;
    const userMessage = this.inputValue.trim();
    this.messages.push({ text: userMessage, sender: 'user' });
    this.inputValue = '';
    this.scrollToBottom();
    this.processUserInput(userMessage);
  }
  
  handleOptionSelect(option: string): void { this.inputValue = option; this.sendMessage(); }
  
  private processUserInput(input: string): void {
    this.cargando = true;
    setTimeout(() => {
        this.cargando = false;
        
        if (this.collectingInfo === 'preguntar_registro') {
            this.handleRegistroCheck(input);
            return;
        }
        if (this.collectingInfo === 'email_existente') {
            this.handleEmailExistente(input);
            return;
        }
        if (this.collectingInfo === 'confirmar_usuario') {
            this.handleConfirmacionUsuario(input);
            return;
        }
        if (this.collectingInfo === 'ofrecer_registro') {
            this.handleOfertaRegistro(input);
            return;
        }
        if (this.collectingInfo === 'datos_nuevo') {
            this.handleDataCollection(input);
            return;
        }
        if (this.collectingInfo === 'email') { 
            this.handleEmailCollection(input); 
            return; 
        }
        if (this.collectingInfo === 'datos') { 
            this.handleDataCollection(input); 
            return; 
        }
        
        let matchedOption = this.options.find(opt => input.toLowerCase().includes(opt.toLowerCase()) || opt.toLowerCase().includes(input.toLowerCase()));
        if (matchedOption) this.handleOptionResponse(matchedOption);
        else { 
            this.messages.push({ text: "Lo siento, no entendí tu consulta. Por favor selecciona una de las opciones disponibles.", sender: 'bot' }); 
            this.showOptions = true; 
        }
        this.scrollToBottom();
    }, 500);
  }
  
  // Método para manejar la verificación de registro
  private handleRegistroCheck(input: string): void {
    const lowerInput = input.toLowerCase().trim();
    
    if (lowerInput === 'si' || lowerInput === 'sí') {
        this.usuarioRegistrado = true;
        this.collectingInfo = 'email_existente';
        this.messages.push({ text: "Por favor ingresa tu correo electrónico registrado:", sender: 'bot' });
    } 
    else if (lowerInput === 'no') {
        this.usuarioRegistrado = false;
        this.collectingInfo = 'datos_nuevo';
        // Iniciar la recolección de datos en el orden correcto
        this.messages.push({ text: "No te preocupes. Por favor ingresa tus nombres para registrarte:", sender: 'bot' });
    } 
    else {
        this.messages.push({ text: "Por favor responde SI o NO. ¿Ya estás registrado en el sistema?", sender: 'bot' });
    }
    this.scrollToBottom();
  }
  
  // Método para manejar email de usuario existente
  private handleEmailExistente(email: string): void {
    // Validar formato de email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        this.messages.push({ text: "❌ Correo electrónico inválido. Por favor ingresa un email válido (ejemplo: usuario@dominio.com):", sender: 'bot' });
        return;
    }
    
    this.apiService.post(API_ENDPOINTS.SEARCH_BY_EMAIL, { email }).subscribe({
        next: (response) => {
            if (response && response.success && response['usuario']) {
                const userData = response['usuario'];
                this.userInfo.email = email;
                this.userInfo.nombres = userData.nombre;
                this.userInfo.apellidos = userData.apellido;
                this.userInfo.ci = userData.ci;
                
                this.messages.push({ 
                    text: `✅ Usuario encontrado: ${userData.nombre} ${userData.apellido}. ¿Confirmas que eres tú? Responde SI o NO`, 
                    sender: 'bot' 
                });
                this.collectingInfo = 'confirmar_usuario';
            } else {
                this.messages.push({ 
                    text: "No se encontró ningún usuario con ese correo. ¿Deseas registrarte? Responde SI para registrarte o NO para intentar con otro correo.", 
                    sender: 'bot' 
                });
                this.collectingInfo = 'ofrecer_registro';
            }
            this.scrollToBottom();
        },
        error: () => {
            this.messages.push({ text: "Hubo un error al buscar tu información. Intenta más tarde.", sender: 'bot' });
            this.collectingInfo = null;
            this.showOptions = true;
            this.scrollToBottom();
        }
    });
  }
  
  private handleEmailCollection(email: string): void {
    this.apiService.post(API_ENDPOINTS.SEARCH_BY_EMAIL, { email }).subscribe({
      next: (response) => {
        if (response && response.success && response['usuario']) {
          const userData = response['usuario'];
          if (userData.estado === 'Inhabilitado') {
            this.messages.push({ 
              text: "Hemos detectado que tu cuenta ha sido inhabilitada. ¿Deseas hacer la solicitud para reactivarla?", 
              sender: 'bot' 
            });
            this.messages.push({ 
              text: "Haz clic aquí para iniciar el proceso de reactivación. El sistema generará nuevas credenciales si es necesario.", 
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
              text: `✅ Tu cuenta está activa. Tu usuario asignado es: ${userData.usuario_asignado}. Puedes iniciar sesión con tu contraseña actual.`, 
              sender: 'bot' 
            });
          }
        } else {
          this.messages.push({ 
            text: "No se encontró ningún usuario con ese correo. Por favor verifica que el correo sea correcto o contacta al administrador.", 
            sender: 'bot' 
          });
        }
        this.collectingInfo = null;
        this.showOptions = true;
        this.scrollToBottom();
      },
      error: () => { 
        this.messages.push({ text: "Hubo un error al buscar tu información. Intenta más tarde.", sender: 'bot' });
        this.collectingInfo = null;
        this.showOptions = true;
        this.scrollToBottom();
      }
    });
  }
  
  private handleDataCollection(input: string): void {
    const lowerInput = input.toLowerCase().trim();
    
    // Si el usuario confirma los datos
    if (lowerInput === 'si' || lowerInput === 'sí') {
        if (!this.usuarioRegistrado) {
            this.messages.push({ text: "Registrando tu información en el sistema...", sender: 'bot' });
            
            const registerData = {
                nombre: this.userInfo.nombres,
                apellido: this.userInfo.apellidos,
                ci: this.userInfo.ci,
                email: this.userInfo.email,
                tipo: 'Usuario',
                especialidad: null
            };
            
            this.apiService.post(API_ENDPOINTS.REGISTER, registerData).subscribe({
                next: (regResponse: any) => {
                    if (regResponse && regResponse.success) {
                        this.messages.push({ text: "✅ Usuario registrado correctamente.", sender: 'bot' });
                        this.sendReportToAdmin();
                    } else {
                        this.messages.push({ text: `❌ Error al registrar: ${regResponse?.message || 'Error desconocido'}. Por favor intenta más tarde.`, sender: 'bot' });
                    }
                },
                error: (error) => {
                    console.error('Error registrando usuario:', error);
                    this.messages.push({ text: "Hubo un error al registrar tu información. Por favor intenta más tarde.", sender: 'bot' });
                }
            });
        } else {
            this.sendReportToAdmin();
        }
        
        this.collectingInfo = null;
        setTimeout(() => { 
            this.messages.push({ text: "¿Necesitas ayuda con algo más?", sender: 'bot' }); 
            this.showOptions = true; 
            this.scrollToBottom(); 
        }, 2000);
        return;
    }
    
    // Si el usuario dice que los datos no son correctos
    if (lowerInput === 'no') {
        this.messages.push({ text: "Entendido. Por favor, vuelve a ingresar tus datos:", sender: 'bot' });
        this.userInfo = { nombres: '', apellidos: '', email: '', telefono: '', ci: '', detalle: '' };
        this.messages.push({ text: "Ingresa tus nombres:", sender: 'bot' });
        return;
    }
    
    // ============================================
    // RECOLECCIÓN DE DATOS EN ORDEN CORRECTO
    // ============================================
    
    // PASO 1: Nombres
    if (!this.userInfo.nombres) { 
        if (input.trim().length < 2) {
            this.messages.push({ text: "❌ El nombre debe tener al menos 2 caracteres. Por favor ingresa nuevamente:", sender: 'bot' });
            return;
        }
        this.userInfo.nombres = input; 
        this.messages.push({ text: "Gracias. Ahora por favor ingresa tus apellidos:", sender: 'bot' }); 
        this.scrollToBottom();
        return;
    }
    
    // PASO 2: Apellidos
    if (!this.userInfo.apellidos) { 
        if (input.trim().length < 2) {
            this.messages.push({ text: "❌ Los apellidos deben tener al menos 2 caracteres. Por favor ingresa nuevamente:", sender: 'bot' });
            return;
        }
        this.userInfo.apellidos = input; 
        this.messages.push({ text: "Perfecto. Ahora necesitamos tu email:", sender: 'bot' }); 
        this.scrollToBottom();
        return;
    }
    
    // PASO 3: Email
    if (!this.userInfo.email) { 
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(input)) {
            this.messages.push({ text: "❌ Correo electrónico inválido. Por favor ingresa un email válido (ejemplo: usuario@dominio.com):", sender: 'bot' });
            return;
        }
        this.userInfo.email = input; 
        this.messages.push({ text: "Ahora, por favor ingresa tu teléfono (10 dígitos, solo números):", sender: 'bot' }); 
        this.scrollToBottom();
        return;
    }
    
    // PASO 4: Teléfono
    if (!this.userInfo.telefono) { 
        const telefonoRegex = /^\d{10}$/;
        if (!telefonoRegex.test(input)) {
            this.messages.push({ text: "❌ Teléfono inválido. Debe contener exactamente 10 dígitos numéricos. Por favor ingresa nuevamente:", sender: 'bot' });
            return;
        }
        this.userInfo.telefono = input; 
        this.messages.push({ text: "Ahora describe el problema que tienes (mínimo 5 caracteres):", sender: 'bot' }); 
        this.scrollToBottom();
        return;
    }
    
    // PASO 5: Detalle del problema
    if (!this.userInfo.detalle) { 
        if (input.trim().length < 5) {
            this.messages.push({ text: "❌ Por favor describe el problema con más detalle (mínimo 5 caracteres):", sender: 'bot' });
            return;
        }
        this.userInfo.detalle = input; 
        this.messages.push({ text: "Por último, ingresa tu número de cédula (10 dígitos, solo números):", sender: 'bot' }); 
        this.scrollToBottom();
        return;
    }
    
    // PASO 6: Cédula
    if (!this.userInfo.ci) {
        const ciRegex = /^\d{10}$/;
        if (!ciRegex.test(input)) {
            this.messages.push({ text: "❌ Cédula inválida. Debe contener exactamente 10 dígitos numéricos. Por favor ingresa nuevamente:", sender: 'bot' });
            return;
        }
        this.userInfo.ci = input;
        this.messages.push({ text: `📋 ¿Esta información es correcta?\n\n• Nombres: ${this.userInfo.nombres}\n• Apellidos: ${this.userInfo.apellidos}\n• Email: ${this.userInfo.email}\n• Teléfono: ${this.userInfo.telefono}\n• Detalle: ${this.userInfo.detalle}\n• CI: ${this.userInfo.ci}\n\nResponde SI o NO`, sender: 'bot' });
        this.scrollToBottom();
        return;
    }
  }
  
  // Manejo de confirmación de usuario existente
  private handleConfirmacionUsuario(input: string): void {
    const lowerInput = input.toLowerCase().trim();
    
    if (lowerInput === 'si' || lowerInput === 'sí') {
        this.messages.push({ text: "Gracias por confirmar. Tu reporte ha sido enviado al administrador.", sender: 'bot' });
        this.sendReportToAdmin();
        this.collectingInfo = null;
        setTimeout(() => { 
            this.messages.push({ text: "¿Necesitas ayuda con algo más?", sender: 'bot' }); 
            this.showOptions = true; 
            this.scrollToBottom(); 
        }, 2000);
    } else {
        this.messages.push({ text: "Por favor ingresa el correo electrónico correcto:", sender: 'bot' });
        this.collectingInfo = 'email_existente';
    }
    this.scrollToBottom();
  }
  
  private handleOfertaRegistro(input: string): void {
    const lowerInput = input.toLowerCase().trim();
    
    if (lowerInput === 'si' || lowerInput === 'sí') {
        this.collectingInfo = 'datos_nuevo';
        this.messages.push({ text: "Perfecto. Por favor ingresa tus nombres para registrarte:", sender: 'bot' });
    } else {
        this.collectingInfo = 'email_existente';
        this.messages.push({ text: "Por favor ingresa el correo electrónico nuevamente:", sender: 'bot' });
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
    }
    else if (option === "Tengo un problema técnico con una máquina, quiero que me ayuden" || 
             option === "Quiero reportar un problema con un comercio asociado, quiero que me ayuden") { 
        this.currentFlow = option.includes('comercio') ? 'commerce_issue' : 'tech_issue'; 
        this.currentFlowText = option; 
        this.collectingInfo = 'preguntar_registro'; 
        this.showOptions = false; 
    }
    else { 
        setTimeout(() => { 
            this.messages.push({ text: "¿Necesitas ayuda con algo más?", sender: 'bot' }); 
            this.showOptions = true; 
            this.scrollToBottom(); 
        }, 1500); 
    }
    this.scrollToBottom();
  }
  private sendReportToAdmin(): void {
  // Primero intentar obtener administradores
  this.apiService.get('/usuarios/por-tipo?tipo=Administrador').subscribe({
    next: (response: any) => {
      console.log('Respuesta de administradores:', response);
      
      // Verificar si hay administradores
      let admin = null;
      
      if (response && response.success) {
        // Buscar en diferentes formatos de respuesta
        if (response.usuarios && response.usuarios.length > 0) {
          admin = response.usuarios[0];
        } else if (response.data && response.data.length > 0) {
          admin = response.data[0];
        } else if (Array.isArray(response) && response.length > 0) {
          admin = response[0];
        }
      }
      
      if (admin) {
        console.log('Administrador encontrado:', admin);
        
        let descripcion = `Nuevo reporte desde chatbot (${this.currentFlowText}):\n\n`;
        descripcion += `📋 DATOS DEL USUARIO:\n`;
        descripcion += `• Nombre: ${this.userInfo.nombres} ${this.userInfo.apellidos}\n`;
        descripcion += `• Email: ${this.userInfo.email}\n`;
        descripcion += `• Teléfono: ${this.userInfo.telefono}\n`;
        descripcion += `• CI: ${this.userInfo.ci}\n`;
        descripcion += `\n📝 DETALLE DEL PROBLEMA:\n${this.userInfo.detalle}\n\n`;
        descripcion += `El sistema generará automáticamente las credenciales de acceso para este usuario.`;
        
        this.apiService.post(API_ENDPOINTS.REPORTES_CREAR, {
          ID_Usuario_Emisor: 'chatbot_temp',
          ID_Usuario_Destinatario: admin.id || admin.ID_Usuario,
          descripcion: descripcion,
          estado: 'Pendiente'
        }).subscribe({
          next: (reporteResponse: any) => {
            console.log('Reporte creado:', reporteResponse);
            if (reporteResponse && reporteResponse.success) {
              this.messages.push({ 
                text: "✅ Hemos recibido tu reporte. El administrador revisará tu caso y nos contactaremos contigo  por correo electrónico.", 
                sender: 'bot' 
              });
            } else {
              this.messages.push({ 
                text: "⚠️ Hubo un problema al enviar tu reporte. Por favor intenta más tarde.", 
                sender: 'bot' 
              });
            }
          },
          error: (error) => {
            console.error('Error creando reporte:', error);
            this.messages.push({ 
              text: "⚠️ Hubo un error al enviar tu reporte. Por favor intenta más tarde o contacta al administrador directamente.", 
              sender: 'bot' 
            });
          }
        });
      } else {
        console.error('No se encontraron administradores');
        this.messages.push({ 
          text: "⚠️ No se pudo encontrar un administrador para procesar tu solicitud. Tu reporte ha sido guardado y será revisado manualmente. Por favor contacta al soporte si no recibes respuesta en 24 horas.", 
          sender: 'bot' 
        });
      }
    },
    error: (error) => { 
      console.error('Error obteniendo administrador:', error);
      this.messages.push({ text: "⚠️ Hubo un error al procesar tu solicitud. Por favor intenta más tarde o contacta al administrador directamente.", sender: 'bot' });
    }
  });
}
  
  private scrollToBottom(): void { 
    setTimeout(() => { 
      if (this.scrollAnchor) 
        this.scrollAnchor.nativeElement.scrollIntoView({ behavior: 'smooth' }); 
    }, 100); 
  }
}