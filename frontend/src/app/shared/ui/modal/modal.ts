/**
 * @fileoverview Componente Modal Reutilizable
 * @description Modal genérico para mostrar contenido en ventanas emergentes
 * @component ModalComponent
 */

import { Component, Input, Output, EventEmitter, TemplateRef, ContentChild } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-modal',
  standalone: true,
  imports: [CommonModule, MatButtonModule, MatIconModule],
  templateUrl: './modal.html',
  styleUrls: ['./modal.css']
})
export class ModalComponent {
  @Input() isOpen = false;
  @Input() title = '';
  @Input() subtitle = '';
  @Input() modalClass = '';
  @Input() width = '500px';
  @Input() maxWidth = '90vw';
  @Input() showHeader = true;
  @Input() showFooter = true;
  @Input() showCloseButton = true;
  @Input() showCancelButton = true;
  @Input() showConfirmButton = true;
  @Input() cancelText = 'Cancelar';
  @Input() confirmText = 'Confirmar';
  @Input() confirmDisabled = false;
  @Input() fixed = true;
  @Input() closeOnBackdropClick = true;
  
  @Output() onClose = new EventEmitter<void>();
  @Output() onConfirm = new EventEmitter<void>();
  
  @ContentChild('modalContent') contentTemplate!: TemplateRef<any>;
  @ContentChild('modalFooter') customFooter!: TemplateRef<any>;
  
  close(): void { this.onClose.emit(); }
  confirm(): void { this.onConfirm.emit(); }
  onBackdropClick(): void { if (this.closeOnBackdropClick) this.close(); }
}