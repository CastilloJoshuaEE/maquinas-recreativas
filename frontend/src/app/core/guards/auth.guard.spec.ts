/**
 * @fileoverview Pruebas para AuthGuard
 * @description Pruebas unitarias del guard de autenticación
 */

import { TestBed } from '@angular/core/testing';
import { Router } from '@angular/router';
import { AuthGuard } from './auth.guard';
import { AuthService } from '@core/services/auth';

describe('AuthGuard', () => {
  let guard: typeof AuthGuard;
  let authServiceMock: any;
  let routerMock: any;

  beforeEach(() => {
    authServiceMock = {
      isAuthenticated: jest.fn()
    };
    
    routerMock = {
      navigate: jest.fn()
    };
    
    TestBed.configureTestingModule({
      providers: [
        { provide: AuthService, useValue: authServiceMock },
        { provide: Router, useValue: routerMock }
      ]
    });
    
    guard = AuthGuard;
  });

  it('should allow access when authenticated', () => {
    authServiceMock.isAuthenticated.mockReturnValue(true);
    
    const result = TestBed.runInInjectionContext(() => guard({} as any, {} as any));
    
    expect(result).toBe(true);
    expect(routerMock.navigate).not.toHaveBeenCalled();
  });

  it('should redirect to login when not authenticated', () => {
    authServiceMock.isAuthenticated.mockReturnValue(false);
    
    const result = TestBed.runInInjectionContext(() => guard({} as any, {} as any));
    
    expect(result).toBe(false);
    expect(routerMock.navigate).toHaveBeenCalledWith(['/auth/login']);
  });
});