/**
 * @fileoverview Pruebas para RoleGuard
 * @description Pruebas unitarias del guard de roles
 */

import { TestBed } from '@angular/core/testing';
import { Router } from '@angular/router';
import { RoleGuard } from './role.guard';
import { AuthService } from '@core/services/auth';

describe('RoleGuard', () => {
  let guard: typeof RoleGuard;
  let authServiceMock: any;
  let routerMock: any;

  beforeEach(() => {
    authServiceMock = {
      hasRole: jest.fn()
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
    
    guard = RoleGuard;
  });

  it('should allow access when no roles specified', () => {
    const route = { data: {} } as any;
    
    const result = TestBed.runInInjectionContext(() => guard(route, {} as any));
    
    expect(result).toBe(true);
  });

  it('should allow access when user has allowed role', () => {
    const route = { data: { roles: ['Administrador'] } } as any;
    authServiceMock.hasRole.mockReturnValue(true);
    
    const result = TestBed.runInInjectionContext(() => guard(route, {} as any));
    
    expect(result).toBe(true);
    expect(routerMock.navigate).not.toHaveBeenCalled();
  });

  it('should redirect when user does not have allowed role', () => {
    const route = { data: { roles: ['Administrador'] } } as any;
    authServiceMock.hasRole.mockReturnValue(false);
    
    const result = TestBed.runInInjectionContext(() => guard(route, {} as any));
    
    expect(result).toBe(false);
    expect(routerMock.navigate).toHaveBeenCalledWith(['/no-autorizado']);
  });
});