/**
 * @fileoverview Pruebas para AuthInterceptor
 * @description Pruebas unitarias del interceptor de autenticación
 */

import { TestBed } from '@angular/core/testing';
import { HttpRequest, HttpHandlerFn, HttpEvent } from '@angular/common/http';
import { authInterceptor } from './auth.interceptor';
import { AuthService } from '@core/services/auth';
import { Observable, of } from 'rxjs';

describe('authInterceptor', () => {
  let authServiceMock: any;

  beforeEach(() => {
    authServiceMock = { getToken: jest.fn() };
    TestBed.configureTestingModule({ 
      providers: [{ provide: AuthService, useValue: authServiceMock }] 
    });
  });

  it('should add Authorization header when token exists', (done) => {
    authServiceMock.getToken.mockReturnValue('test-token');
    const originalReq = new HttpRequest('GET', '/api/test');
    
    const mockHandler: HttpHandlerFn = (request: HttpRequest<unknown>) => {
      expect(request.headers.has('Authorization')).toBe(true);
      expect(request.headers.get('Authorization')).toBe('Bearer test-token');
      done();
      return of({} as HttpEvent<unknown>);
    };

    TestBed.runInInjectionContext(() => {
      authInterceptor(originalReq, mockHandler).subscribe();
    });
  });

  it('should not add Authorization header for login requests', (done) => {
    authServiceMock.getToken.mockReturnValue('test-token');
    const originalReq = new HttpRequest('POST', '/api/usuario/login', null);
    
    const mockHandler: HttpHandlerFn = (request: HttpRequest<unknown>) => {
      expect(request.headers.has('Authorization')).toBe(false);
      done();
      return of({} as HttpEvent<unknown>);
    };

    TestBed.runInInjectionContext(() => {
      authInterceptor(originalReq, mockHandler).subscribe();
    });
  });

  it('should not add Authorization header when token is missing', (done) => {
    authServiceMock.getToken.mockReturnValue(null);
    const originalReq = new HttpRequest('GET', '/api/test');
    
    const mockHandler: HttpHandlerFn = (request: HttpRequest<unknown>) => {
      expect(request.headers.has('Authorization')).toBe(false);
      done();
      return of({} as HttpEvent<unknown>);
    };

    TestBed.runInInjectionContext(() => {
      authInterceptor(originalReq, mockHandler).subscribe();
    });
  });
});