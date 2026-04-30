/**
 * @fileoverview Componente de Chatbot Asistente
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
  detalle: string;
}

type CollectingInfoState =
  | 'email'
  | 'preguntar_registro'
  | 'email_existente'
  | 'confirmar_usuario'
  | 'detalle_reporte_si'
  | 'ofrecer_registro'
  | 'datos_nuevo'
  | null;

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
  collectingInfo: CollectingInfoState = null;
  inputPlaceholder = 'Escribe tu mensaje...';

  userInfo: UserInfo = { nombres: '', apellidos: '', email: '', telefono: '', ci: '', detalle: '' };

  private registeredUserId: string | null = null;
  private currentFlowText = '';
  private usuarioRegistrado: boolean | null = null;

  options = [
    'No puedo iniciar sesión y ya hice la solicitud para ingresar a trabajar',
    'No puedo iniciar sesión y ya trabajo en la empresa',
    'Quiero información sobre lo que hacen sobre las máquinas recreativas',
    'Tengo un problema técnico con una máquina, quiero que me ayuden'
  ];

  responses: { [key: string]: string } = {
    'No puedo iniciar sesión y ya hice la solicitud para ingresar a trabajar':
      'Hola, aún estamos revisando tu solicitud. Por favor espera a que te contactemos. El proceso puede tardar hasta 5 días hábiles.',
    'No puedo iniciar sesión y ya trabajo en la empresa':
      'Proporciona tu email registrado en la empresa para buscarte en nuestra base de datos.',
    'Quiero información sobre lo que hacen sobre las máquinas recreativas':
      'Nuestras máquinas recreativas pasan por un proceso de ensamblaje, comprobación y distribución. Tenemos modelos clásicos y modernos con tecnología de última generación.',
    'Tengo un problema técnico con una máquina, quiero que me ayuden':
      '¿Ya estás registrado en el sistema? Responde SI o NO'
    
  };

  ngOnInit(): void {
    this.messages.push({
      text: '¡Hola! Soy el asistente virtual de RecreaSys. ¿En qué puedo ayudarte hoy?',
      sender: 'bot'
    });
  }

  ngOnDestroy(): void { }

  toggleMinimize(): void { this.minimized = !this.minimized; }

  sendMessage(): void {
    if (!this.inputValue.trim()) return;
    const msg = this.inputValue.trim();
    this.messages.push({ text: msg, sender: 'user' });
    this.inputValue = '';
    this.scrollToBottom();
    this.processUserInput(msg);
  }

  handleOptionSelect(option: string): void {
    this.inputValue = option;
    this.sendMessage();
  }

  private processUserInput(input: string): void {
    this.cargando = true;
    setTimeout(() => {
      this.cargando = false;

      switch (this.collectingInfo) {
        case 'preguntar_registro': this.handleRegistroCheck(input); return;
        case 'email_existente': this.handleEmailExistente(input); return;
        case 'confirmar_usuario': this.handleConfirmacionUsuario(input); return;
        case 'detalle_reporte_si': this.handleDetalleReporteSI(input); return;
        case 'ofrecer_registro': this.handleOfertaRegistro(input); return;
        case 'datos_nuevo': this.handleDataCollection(input); return;
        case 'email': this.handleEmailCollection(input); return;
      }

      const matched = this.options.find(
        opt => input.toLowerCase().includes(opt.toLowerCase()) ||
          opt.toLowerCase().includes(input.toLowerCase())
      );
      if (matched) {
        this.handleOptionResponse(matched);
      } else {
        this.messages.push({
          text: 'Lo siento, no entendí tu consulta. Por favor selecciona una de las opciones disponibles.',
          sender: 'bot'
        });
        this.showOptions = true;
      }
      this.scrollToBottom();
    }, 500);
  }

  private handleRegistroCheck(input: string): void {
    const lower = input.toLowerCase().trim();
    if (lower === 'si' || lower === 'sí') {
      this.usuarioRegistrado = true;
      this.collectingInfo = 'email_existente';
      this.messages.push({ text: 'Por favor ingresa tu correo electrónico registrado:', sender: 'bot' });
    } else if (lower === 'no') {
      this.usuarioRegistrado = false;
      this.collectingInfo = 'datos_nuevo';
      this.messages.push({
        text: 'No te preocupes, te registraremos para atenderte. Por favor ingresa tus nombres:',
        sender: 'bot'
      });
    } else {
      this.messages.push({ text: 'Por favor responde SI o NO. ¿Ya estás registrado en el sistema?', sender: 'bot' });
    }
    this.scrollToBottom();
  }

  private handleEmailExistente(email: string): void {
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      this.messages.push({ text: ' Correo inválido. Ejemplo: usuario@dominio.com:', sender: 'bot' });
      return;
    }

    this.cargando = true;
    this.apiService.post(API_ENDPOINTS.SEARCH_BY_EMAIL, { email }).subscribe({
      next: (res: any) => {
        this.cargando = false;
        if (res?.success && res['usuario']) {
          const u = res['usuario'];
          this.userInfo.email = email;
          this.userInfo.nombres = u.nombre ?? u.Nombre ?? '';
          this.userInfo.apellidos = u.apellido ?? u.Apellido ?? '';
          this.userInfo.ci = u.ci ?? '';
          this.registeredUserId = u.id ?? u.ID_Usuario ?? null;

          this.messages.push({
            text: ` Usuario encontrado: ${this.userInfo.nombres} ${this.userInfo.apellidos}. ¿Eres tú? Responde SI o NO`,
            sender: 'bot'
          });
          this.collectingInfo = 'confirmar_usuario';
        } else {
          this.messages.push({
            text: ' No se encontró ningún usuario con ese correo. ¿Deseas registrarte? Responde SI o NO para intentar con otro correo.',
            sender: 'bot'
          });
          this.collectingInfo = 'ofrecer_registro';
        }
        this.scrollToBottom();
      },
      error: () => {
        this.cargando = false;
        this.messages.push({ text: 'Hubo un error al buscar tu información. Intenta más tarde.', sender: 'bot' });
        this.resetFlujo();
      }
    });
  }

  private handleConfirmacionUsuario(input: string): void {
    const lower = input.toLowerCase().trim();
    if (lower === 'si' || lower === 'sí') {
      this.collectingInfo = 'detalle_reporte_si';
      this.messages.push({
        text: 'Perfecto. Por favor describe el problema que tienes (mínimo 10 caracteres):',
        sender: 'bot'
      });
    } else {
      this.registeredUserId = null;
      this.userInfo = { nombres: '', apellidos: '', email: '', telefono: '', ci: '', detalle: '' };
      this.messages.push({ text: 'Entendido. Por favor ingresa el correo electrónico correcto:', sender: 'bot' });
      this.collectingInfo = 'email_existente';
    }
    this.scrollToBottom();
  }

  private handleDetalleReporteSI(input: string): void {
    if (input.trim().length < 10) {
      this.messages.push({
        text: ' Por favor describe el problema con más detalle (mínimo 10 caracteres):',
        sender: 'bot'
      });
      return;
    }

    this.userInfo.detalle = input.trim();
    this.collectingInfo = null;

    if (!this.registeredUserId) {
      this.messages.push({
        text: ' No se pudo verificar tu identidad. Por favor intenta de nuevo.',
        sender: 'bot'
      });
      this.resetFlujo();
      return;
    }

    this.messages.push({ text: 'Enviando tu reporte al administrador...', sender: 'bot' });
    this.sendReportToAdmin();
    this.scrollToBottom();
  }

  private handleOfertaRegistro(input: string): void {
    const lower = input.toLowerCase().trim();
    if (lower === 'si' || lower === 'sí') {
      this.collectingInfo = 'datos_nuevo';
      this.messages.push({ text: 'Perfecto. Por favor ingresa tus nombres:', sender: 'bot' });
    } else {
      this.collectingInfo = 'email_existente';
      this.messages.push({ text: 'Por favor ingresa el correo electrónico nuevamente:', sender: 'bot' });
    }
    this.scrollToBottom();
  }

  private handleDataCollection(input: string): void {
    const lower = input.toLowerCase().trim();

    if (lower === 'si' || lower === 'sí') {
      this.collectingInfo = null;
      this.registrarNuevoUsuarioYReportar();
      return;
    }

    if (lower === 'no') {
      this.userInfo = { nombres: '', apellidos: '', email: '', telefono: '', ci: '', detalle: '' };
      this.messages.push({ text: 'Entendido. Por favor ingresa tus nombres:', sender: 'bot' });
      return;
    }

    if (!this.userInfo.nombres) {
      if (input.trim().length < 2) {
        this.messages.push({ text: ' El nombre debe tener al menos 2 caracteres:', sender: 'bot' });
        return;
      }
      this.userInfo.nombres = input.trim();
      this.messages.push({ text: 'Gracias. Ingresa tus apellidos:', sender: 'bot' });
      this.scrollToBottom();
      return;
    }

    if (!this.userInfo.apellidos) {
      if (input.trim().length < 2) {
        this.messages.push({ text: ' Los apellidos deben tener al menos 2 caracteres:', sender: 'bot' });
        return;
      }
      this.userInfo.apellidos = input.trim();
      this.messages.push({ text: 'Perfecto. Ingresa tu email:', sender: 'bot' });
      this.scrollToBottom();
      return;
    }

    if (!this.userInfo.email) {
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input)) {
        this.messages.push({ text: ' Correo inválido. Ejemplo: usuario@dominio.com:', sender: 'bot' });
        return;
      }
      this.userInfo.email = input.trim();
      this.messages.push({ text: 'Ingresa tu teléfono (10 dígitos, solo números):', sender: 'bot' });
      this.scrollToBottom();
      return;
    }

    if (!this.userInfo.telefono) {
      if (!/^\d{10}$/.test(input)) {
        this.messages.push({ text: ' Teléfono inválido. Debe tener exactamente 10 dígitos:', sender: 'bot' });
        return;
      }
      this.userInfo.telefono = input.trim();
      this.messages.push({ text: 'Describe el problema que tienes (mínimo 10 caracteres):', sender: 'bot' });
      this.scrollToBottom();
      return;
    }

    if (!this.userInfo.detalle) {
      if (input.trim().length < 10) {
        this.messages.push({ text: ' Por favor describe el problema con más detalle (mínimo 10 caracteres):', sender: 'bot' });
        return;
      }
      this.userInfo.detalle = input.trim();
      this.messages.push({ text: 'Por último, ingresa tu número de cédula (10 dígitos, solo números):', sender: 'bot' });
      this.scrollToBottom();
      return;
    }

    if (!this.userInfo.ci) {
      if (!/^\d{10}$/.test(input)) {
        this.messages.push({ text: ' Cédula inválida. Debe tener exactamente 10 dígitos:', sender: 'bot' });
        return;
      }
      this.userInfo.ci = input.trim();
      this.messages.push({
        text:
          ` ¿Esta información es correcta?\n\n` +
          `• Nombres: ${this.userInfo.nombres}\n` +
          `• Apellidos: ${this.userInfo.apellidos}\n` +
          `• Email: ${this.userInfo.email}\n` +
          `• Teléfono: ${this.userInfo.telefono}\n` +
          `• Detalle: ${this.userInfo.detalle}\n` +
          `• CI: ${this.userInfo.ci}\n\n` +
          `Responde SI para confirmar o NO para reingresar los datos.`,
        sender: 'bot'
      });
      this.scrollToBottom();
    }
  }
private registrarNuevoUsuarioYReportar(): void {
    this.messages.push({ text: 'Registrando tu información en el sistema...', sender: 'bot' });
    this.cargando = true;

    this.apiService.post(API_ENDPOINTS.REGISTER, {
        nombre: this.userInfo.nombres,
        apellido: this.userInfo.apellidos,
        ci: this.userInfo.ci,
        email: this.userInfo.email,
        tipo: 'Usuario',
        especialidad: null
    }).subscribe({
        next: (regRes: any) => {
            this.cargando = false;

            if (!regRes?.success) {
                this.messages.push({
                    text: ` Error al registrar: ${regRes?.message ?? 'Error desconocido'}. Por favor intenta más tarde.`,
                    sender: 'bot'
                });
                this.resetFlujo();
                return;
            }

            this.messages.push({ text: ' Usuario registrado correctamente.', sender: 'bot' });

            // Obtener el ID del nuevo usuario
            const nuevoId = regRes.id ?? regRes.ID_Usuario ?? null;

            if (nuevoId && this.esUUIDValido(nuevoId)) {
                this.registeredUserId = nuevoId;
                // Pequeño retraso para asegurar que la BD haya terminado de persistir
                setTimeout(() => {
                    this.sendReportToAdmin();
                }, 500);
                return;
            }

            // Fallback: buscar por email
            this.messages.push({ text: 'Obteniendo tu identificador...', sender: 'bot' });
            this.apiService.post(API_ENDPOINTS.SEARCH_BY_EMAIL, { email: this.userInfo.email }).subscribe({
                next: (searchRes: any) => {
                    if (searchRes?.success && searchRes['usuario']) {
                        const u = searchRes['usuario'];
                        const uid = u.id ?? u.ID_Usuario ?? null;
                        if (uid && this.esUUIDValido(uid)) {
                            this.registeredUserId = uid;
                            setTimeout(() => {
                                this.sendReportToAdmin();
                            }, 500);
                            return;
                        }
                    }

                    this.messages.push({
                        text: ' El registro fue exitoso, pero no se pudo obtener tu identificador. Por favor contacta al administrador.',
                        sender: 'bot'
                    });
                    this.resetFlujo();
                },
                error: () => {
                    this.messages.push({
                        text: ' El registro fue exitoso, pero hubo un error al obtener tu identificador. Contacta al administrador.',
                        sender: 'bot'
                    });
                    this.resetFlujo();
                }
            });
        },
        error: (err: any) => {
            this.cargando = false;
            console.error('Error registrando usuario:', err);
            this.messages.push({
                text: 'Hubo un error al registrar tu información. Por favor intenta más tarde.',
                sender: 'bot'
            });
            this.resetFlujo();
        }
    });
} 
  private handleEmailCollection(email: string): void {
    this.apiService.post(API_ENDPOINTS.SEARCH_BY_EMAIL, { email }).subscribe({
      next: (res: any) => {
        if (res?.success && res['usuario']) {
          const u = res['usuario'];
          if (u.estado === 'Inhabilitado') {
            this.messages.push({ text: 'Tu cuenta ha sido inhabilitada. ¿Deseas solicitar reactivarla?', sender: 'bot' });
            this.messages.push({
              text: 'Haz clic aquí para iniciar el proceso de reactivación.',
              sender: 'bot',
              isButton: true,
              onClick: () => this.router.navigate(['/reportes/gestion'], { state: { userData: u, isDisabledUser: true } })
            });
          } else {
            this.messages.push({
              text: ` Tu cuenta está activa. Tu usuario asignado es: ${u.usuario_asignado}. Puedes iniciar sesión con tu contraseña actual.`,
              sender: 'bot'
            });
          }
        } else {
          this.messages.push({
            text: 'No se encontró ningún usuario con ese correo. Verifica que sea correcto o contacta al administrador.',
            sender: 'bot'
          });
        }
        this.collectingInfo = null;
        this.showOptions = true;
        this.scrollToBottom();
      },
      error: () => {
        this.messages.push({ text: 'Hubo un error al buscar tu información. Intenta más tarde.', sender: 'bot' });
        this.resetFlujo();
      }
    });
  }

  private handleOptionResponse(option: string): void {
    this.messages.push({ text: this.responses[option], sender: 'bot' });

    if (option === 'No puedo iniciar sesión y ya trabajo en la empresa') {
      this.collectingInfo = 'email';
      this.messages.push({ text: 'Por favor ingresa tu correo electrónico:', sender: 'bot' });
      this.showOptions = false;

    } else if (
      option === 'Tengo un problema técnico con una máquina, quiero que me ayuden'
    ) {
      this.registeredUserId = null;
      this.usuarioRegistrado = null;
      this.userInfo = { nombres: '', apellidos: '', email: '', telefono: '', ci: '', detalle: '' };
      this.currentFlowText = option;
      this.collectingInfo = 'preguntar_registro';
      this.showOptions = false;

    } else {
      setTimeout(() => {
        this.messages.push({ text: '¿Necesitas ayuda con algo más?', sender: 'bot' });
        this.showOptions = true;
        this.scrollToBottom();
      }, 1500);
    }
    this.scrollToBottom();
  }

  private sendReportToAdmin(): void {
    if (!this.registeredUserId) {
      this.messages.push({
        text: ' No se pudo identificar tu usuario. Por favor intenta de nuevo.',
        sender: 'bot'
      });
      this.resetFlujo();
      return;
    }

    this.apiService.get('/usuarios/por-tipo?tipo=Administrador').subscribe({
      next: (res: any) => {
        let admin: any = null;
        if (res?.success) {
          admin = (res.usuarios ?? res.data ?? [])[0] ?? null;
        } else if (Array.isArray(res) && res.length > 0) {
          admin = res[0];
        }

        if (admin) {
          this.enviarReporte(admin.id ?? admin.ID_Usuario);
        } else {
          this.mostrarErrorSinAdmin();
        }
      },
      error: () => this.mostrarErrorSinAdmin()
    });
  }

  private enviarReporte(adminId: string): void {
    const descripcion =
      `Reporte desde chatbot — ${this.currentFlowText}\n\n` +
      ` DATOS DEL SOLICITANTE:\n` +
      `• Nombre: ${this.userInfo.nombres} ${this.userInfo.apellidos}\n` +
      `• Email: ${this.userInfo.email}\n` +
      `• Teléfono: ${this.userInfo.telefono || 'No proporcionado'}\n` +
      `• CI: ${this.userInfo.ci || 'No proporcionado'}\n\n` +
      ` DETALLE DEL PROBLEMA:\n${this.userInfo.detalle}`;

    this.apiService.post(API_ENDPOINTS.REPORTES_CREAR, {
      ID_Usuario_Emisor: this.registeredUserId,
      ID_Usuario_Destinatario: adminId,
      descripcion: descripcion,
      estado: 'Pendiente'
    }).subscribe({
      next: (r: any) => {
        if (r?.success) {
          this.messages.push({
            text: ' Hemos recibido tu reporte. El administrador revisará tu caso y se contactará contigo por correo electrónico.',
            sender: 'bot'
          });
        } else {
          this.messages.push({
            text: ' Hubo un problema al enviar tu reporte. Por favor intenta más tarde.',
            sender: 'bot'
          });
        }
        this.finalizarFlujo();
      },
      error: (err: any) => {
        console.error('Error creando reporte:', err);
        this.messages.push({
          text: ' Hubo un error al enviar tu reporte. Por favor intenta más tarde o contacta al administrador directamente.',
          sender: 'bot'
        });
        this.finalizarFlujo();
      }
    });
  }

  private mostrarErrorSinAdmin(): void {
    this.messages.push({
      text: ' No se pudo encontrar un administrador disponible. Por favor contacta al soporte directamente.',
      sender: 'bot'
    });
    this.finalizarFlujo();
  }

  private finalizarFlujo(): void {
    this.collectingInfo = null;
    this.registeredUserId = null;
    this.usuarioRegistrado = null;
    setTimeout(() => {
      this.messages.push({ text: '¿Necesitas ayuda con algo más?', sender: 'bot' });
      this.showOptions = true;
      this.scrollToBottom();
    }, 1500);
    this.scrollToBottom();
  }

  private resetFlujo(): void {
    this.collectingInfo = null;
    this.registeredUserId = null;
    this.showOptions = true;
    this.scrollToBottom();
  }

  private esUUIDValido(val: string): boolean {
    return /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(val);
  }

  private scrollToBottom(): void {
    setTimeout(() => {
      if (this.scrollAnchor) {
        this.scrollAnchor.nativeElement.scrollIntoView({ behavior: 'smooth' });
      }
    }, 100);
  }
}