/**
 * @fileoverview Pruebas para AdminHeaderComponent
 */

import { ComponentFixture, TestBed } from '@angular/core/testing';
import { AdminHeaderComponent } from './admin-header.component';
import { AuthService } from '@core/services/auth.service';
import { NotificationService } from '@core/services/notification.service';
import { Router } from '@angular/router';

describe('AdminHeaderComponent', () => {
  let component: AdminHeaderComponent;
  let fixture: ComponentFixture<AdminHeaderComponent>;
  let authServiceMock: any;
  let notificationServiceMock: any;
  let routerMock: any;

  beforeEach(async () => {
    authServiceMock = {
      getCurrentUser: jest.fn().mockReturnValue({ ID_Usuario: 'test-id', usuario_asignado: 'testuser' }),
      logout: jest.fn().mockReturnValue({ subscribe: jest.fn() })
    };
    
    notificationServiceMock = {
      getUnreadCount: jest.fn().mockReturnValue({ subscribe: jest.fn() })
    };
    
    routerMock = {
      navigate: jest.fn()
    };
    
    await TestBed.configureTestingModule({
      imports: [AdminHeaderComponent],
      providers: [
        { provide: AuthService, useValue: authServiceMock },
        { provide: NotificationService, useValue: notificationServiceMock },
        { provide: Router, useValue: routerMock }
      ]
    }).compileComponents();

    fixture = TestBed.createComponent(AdminHeaderComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should navigate to profile', () => {
    component.verPerfil();
    expect(routerMock.navigate).toHaveBeenCalledWith(['/usuario/perfil']);
  });

  it('should navigate to edit profile', () => {
    component.editarPerfil();
    expect(routerMock.navigate).toHaveBeenCalledWith(['/usuario/actualizar-perfil']);
  });
});