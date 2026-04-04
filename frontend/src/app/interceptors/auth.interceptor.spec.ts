/**
 * @fileoverview Pruebas para AuthInterceptor
 * @description Pruebas unitarias del interceptor de autenticación
 */

import { TestBed } from '@angular/core/testing';
import { HttpRequest } from '@angular/common/http';
import { authInterceptor } from './auth.interceptor';
import { AuthService } from '@core/services/auth.service';

describe('authInterceptor', () => {
  let authServiceMock: any;

  beforeEach(() => {
    authServiceMock = {
      getToken: jest.fn()
    };
    
    TestBed.configureTestingModule({
      providers: [
        { provide: AuthService, useValue: authServiceMock }
      ]
    });
  });

  it('should add Authorization header when token exists', () => {
    authServiceMock.getToken.mockReturnValue('test-token');
    const req = new HttpRequest('GET', '/api/test');
    
    TestBed.runInInjectionContext(() => {
      authInterceptor(req, (handledReq) => {
        expect(handledReq.headers.has('Authorization')).toBe(true);
        expect(handledReq.headers.get('Authorization')).toBe('Bearer test-token');
        return handledReq;
      });
    });
  });

  it('should not add Authorization header for login requests', () => {
    authServiceMock.getToken.mockReturnValue('test-token');
    const req = new HttpRequest('POST', '/api/usuario/login');
    
    TestBed.runInInjectionContext(() => {
      authInterceptor(req, (handledReq) => {
        expect(handledReq.headers.has('Authorization')).toBe(false);
        return handledReq;
      });
    });
  });

  it('should not add Authorization header when token is missing', () => {
    authServiceMock.getToken.mockReturnValue(null);
    const req = new HttpRequest('GET', '/api/test');
    
    TestBed.runInInjectionContext(() => {
      authInterceptor(req, (handledReq) => {
        expect(handledReq.headers.has('Authorization')).toBe(false);
        return handledReq;
      });
    });
  });
});