/**
 * @fileoverview Pruebas unitarias para SesionExpiradaComponent
 */

import { ComponentFixture, TestBed } from '@angular/core/testing';
import { MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { Router } from '@angular/router';
import { SesionExpiradaComponent } from './sesion-expirada';
import { AuthService } from '@core/services/auth';

describe('SesionExpiradaComponent', () => {
  let component: SesionExpiradaComponent;
  let fixture: ComponentFixture<SesionExpiradaComponent>;
  let dialogRefCloseSpy: jasmine.Spy;
  let routerNavigateSpy: jasmine.Spy;
  let authClearSessionSpy: jasmine.Spy;

  const mockData = {
    mensaje: 'Sesión expirada por prueba',
    tiempoRestante: 900
  };

  beforeEach(async () => {
    // Crear mocks manuales
    const mockDialogRef = { close: () => {} };
    const mockRouter = { navigate: () => {} };
    const mockAuthService = { clearSession: () => {} };

    dialogRefCloseSpy = spyOn(mockDialogRef, 'close');
    routerNavigateSpy = spyOn(mockRouter, 'navigate');
    authClearSessionSpy = spyOn(mockAuthService, 'clearSession');

    await TestBed.configureTestingModule({
      imports: [SesionExpiradaComponent],
      providers: [
        { provide: MatDialogRef, useValue: mockDialogRef },
        { provide: MAT_DIALOG_DATA, useValue: mockData },
        { provide: Router, useValue: mockRouter },
        { provide: AuthService, useValue: mockAuthService }
      ]
    }).compileComponents();

    fixture = TestBed.createComponent(SesionExpiradaComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should display provided message', () => {
    const messageElement = fixture.nativeElement.querySelector('p');
    expect(messageElement.textContent).toContain(mockData.mensaje);
  });

  it('should display default message when no data provided', () => {
    // Crear un nuevo TestBed sin datos
    const mockDialogRef = { close: () => {} };
    const mockRouter = { navigate: () => {} };
    const mockAuthService = { clearSession: () => {} };

    TestBed.resetTestingModule();
    TestBed.configureTestingModule({
      imports: [SesionExpiradaComponent],
      providers: [
        { provide: MatDialogRef, useValue: mockDialogRef },
        { provide: MAT_DIALOG_DATA, useValue: {} },
        { provide: Router, useValue: mockRouter },
        { provide: AuthService, useValue: mockAuthService }
      ]
    }).compileComponents();

    const newFixture = TestBed.createComponent(SesionExpiradaComponent);
    newFixture.detectChanges();

    const messageElement = newFixture.nativeElement.querySelector('p');
    expect(messageElement.textContent).toContain('Su sesión ha expirado por inactividad.');
  });

  it('should show tiempo-info when tiempoRestante is provided', () => {
    const tiempoInfo = fixture.nativeElement.querySelector('.tiempo-info');
    expect(tiempoInfo).toBeTruthy();
    expect(tiempoInfo.textContent).toContain('900 segundos');
  });

  it('should hide tiempo-info when tiempoRestante is not provided', () => {
    const mockDialogRef = { close: () => {} };
    const mockRouter = { navigate: () => {} };
    const mockAuthService = { clearSession: () => {} };

    TestBed.resetTestingModule();
    TestBed.configureTestingModule({
      imports: [SesionExpiradaComponent],
      providers: [
        { provide: MatDialogRef, useValue: mockDialogRef },
        { provide: MAT_DIALOG_DATA, useValue: { mensaje: 'Test' } },
        { provide: Router, useValue: mockRouter },
        { provide: AuthService, useValue: mockAuthService }
      ]
    }).compileComponents();

    const newFixture = TestBed.createComponent(SesionExpiradaComponent);
    newFixture.detectChanges();

    const tiempoInfo = newFixture.nativeElement.querySelector('.tiempo-info');
    expect(tiempoInfo).toBeNull();
  });

  it('should call clearSession, close dialog and navigate to login on irALogin', () => {
    component.irALogin();

    expect(authClearSessionSpy).toHaveBeenCalled();
    expect(dialogRefCloseSpy).toHaveBeenCalled();
    expect(routerNavigateSpy).toHaveBeenCalledWith(['/auth/login']);
  });
});