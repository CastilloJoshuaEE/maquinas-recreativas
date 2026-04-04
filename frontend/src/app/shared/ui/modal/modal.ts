/**
 * @fileoverview Componente Modal Reutilizable
 * @description Modal genérico para mostrar contenido en ventanas emergentes
 * @component ModalComponent
 */

import { Component, Input, Output, EventEmitter, TemplateRef, ContentChild, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-modal',
  standalone: true,
  imports: [CommonModule, MatButtonModule, MatIconModule],
  template: `
    <div class="modal-overlay" *ngIf="isOpen" (click)="onBackdropClick()" [class.modal-fixed]="fixed">
      <div class="modal-container" [ngClass]="modalClass" (click)="$event.stopPropagation()" [style.width]="width" [style.max-width]="maxWidth">
        <!-- Cabecera -->
        <div class="modal-header" *ngIf="showHeader">
          <h2 *ngIf="title">{{ title }}</h2>
          <h3 *ngIf="subtitle">{{ subtitle }}</h3>
          <button class="modal-close" (click)="close()" aria-label="Cerrar" *ngIf="showCloseButton">
            <mat-icon>close</mat-icon>
          </button>
        </div>
        
        <!-- Contenido -->
        <div class="modal-body">
          <!-- Contenido proyectado -->
          <ng-content></ng-content>
          
          <!-- Template personalizado -->
          <ng-container *ngIf="contentTemplate">
            <ng-template [ngTemplateOutlet]="contentTemplate"></ng-template>
          </ng-container>
        </div>
        
        <!-- Pie -->
        <div class="modal-footer" *ngIf="showFooter">
          <ng-content select="[modal-footer]"></ng-content>
          <div class="footer-actions" *ngIf="!customFooter">
            <button mat-button (click)="close()" *ngIf="showCancelButton">{{ cancelText }}</button>
            <button mat-raised-button color="primary" (click)="confirm()" *ngIf="showConfirmButton" [disabled]="confirmDisabled">
              {{ confirmText }}
            </button>
          </div>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.75);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1050;
      backdrop-filter: blur(4px);
    }
    
    .modal-overlay.modal-fixed {
      position: fixed;
    }
    
    .modal-container {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      animation: modalFadeIn 0.3s ease;
      display: flex;
      flex-direction: column;
      max-height: 90vh;
    }
    
    @keyframes modalFadeIn {
      from {
        opacity: 0;
        transform: translateY(-30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .modal-header {
      padding: 1rem 1.5rem;
      background: linear-gradient(135deg, #4f6bed, #3d55c3);
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-shrink: 0;
    }
    
    .modal-header h2, .modal-header h3 {
      margin: 0;
      color: white;
    }
    
    .modal-header h2 {
      font-size: 1.25rem;
    }
    
    .modal-header h3 {
      font-size: 1rem;
      opacity: 0.9;
    }
    
    .modal-close {
      background: none;
      border: none;
      cursor: pointer;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 4px;
      border-radius: 50%;
      transition: background 0.2s;
    }
    
    .modal-close:hover {
      background: rgba(255, 255, 255, 0.2);
    }
    
    .modal-close mat-icon {
      font-size: 1.25rem;
      width: 1.25rem;
      height: 1.25rem;
    }
    
    .modal-body {
      padding: 1.5rem;
      overflow-y: auto;
      flex: 1;
    }
    
    .modal-footer {
      padding: 1rem 1.5rem;
      border-top: 1px solid #e0e0e0;
      display: flex;
      justify-content: flex-end;
      gap: 0.5rem;
      flex-shrink: 0;
    }
    
    .footer-actions {
      display: flex;
      gap: 0.5rem;
    }
    
    @media (max-width: 768px) {
      .modal-container {
        width: 95% !important;
        max-height: 95vh;
      }
      
      .modal-body {
        padding: 1rem;
      }
      
      .modal-footer {
        padding: 0.75rem 1rem;
      }
    }
  `]
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
  
  close(): void {
    this.onClose.emit();
  }
  
  confirm(): void {
    this.onConfirm.emit();
  }
  
  onBackdropClick(): void {
    if (this.closeOnBackdropClick) {
      this.close();
    }
  }
}