import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ReactiveFormsModule } from '@angular/forms';
import { NoopAnimationsModule } from '@angular/platform-browser/animations';
import { Router } from '@angular/router';
import { ToastrService } from 'ngx-toastr';
import { LoginComponent } from './login.component';
import { AuthService } from '@core/services/auth.service';

describe('LoginComponent', () => {
  let component: LoginComponent;
  let fixture: ComponentFixture<LoginComponent>;
  let authServiceMock: any;
  let routerMock: any;
  let toastrMock: any;

  beforeEach(async () => {
    authServiceMock = {
      login: jest.fn().mockReturnValue({ subscribe: jest.fn() })
    };
    
    routerMock = {
      navigate: jest.fn()
    };
    
    toastrMock = {
      success: jest.fn(),
      error: jest.fn()
    };
    
    await TestBed.configureTestingModule({
      imports: [LoginComponent, ReactiveFormsModule, NoopAnimationsModule],
      providers: [
        { provide: AuthService, useValue: authServiceMock },
        { provide: Router, useValue: routerMock },
        { provide: ToastrService, useValue: toastrMock }
      ]
    }).compileComponents();

    fixture = TestBed.createComponent(LoginComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should have invalid form when empty', () => {
    expect(component.loginForm.valid).toBeFalsy();
  });

  it('should validate required fields', () => {
    const usuarioControl = component.loginForm.get('usuario_asignado');
    const contrasenaControl = component.loginForm.get('contrasena');
    
    usuarioControl?.setValue('');
    contrasenaControl?.setValue('');
    
    expect(usuarioControl?.hasError('required')).toBeTruthy();
    expect(contrasenaControl?.hasError('required')).toBeTruthy();
  });
});