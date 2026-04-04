/**
 * @fileoverview Directiva de Roles
 * @description Muestra u oculta elementos según el rol del usuario
 * @directive HasRoleDirective
 */

import { Directive, Input, TemplateRef, ViewContainerRef, OnInit, inject } from '@angular/core';
import { AuthService } from '@core/services/auth.service';

/**
 * Directiva condicional basada en roles de usuario
 * Uso: *appHasRole="['Administrador']"
 */
@Directive({
  selector: '[appHasRole]',
  standalone: true
})
export class HasRoleDirective implements OnInit {
  private authService = inject(AuthService);
  private templateRef = inject(TemplateRef<any>);
  private viewContainer = inject(ViewContainerRef);
  
  @Input('appHasRole') allowedRoles: string[] = [];
  
  ngOnInit(): void {
    this.updateView();
  }
  
  private updateView(): void {
    const hasRole = this.authService.hasRole(this.allowedRoles);
    
    if (hasRole) {
      this.viewContainer.createEmbeddedView(this.templateRef);
    } else {
      this.viewContainer.clear();
    }
  }
}