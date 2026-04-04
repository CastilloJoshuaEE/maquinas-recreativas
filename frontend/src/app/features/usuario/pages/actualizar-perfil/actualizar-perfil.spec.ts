import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ReactiveFormsModule } from '@angular/forms';
import { provideHttpClient } from '@angular/common/http';
import { provideHttpClientTesting } from '@angular/common/http/testing';
import { NoopAnimationsModule } from '@angular/platform-browser/animations';
import { Router } from '@angular/router';
import { ActualizarPerfilComponent } from './actualizar-perfil.component';
import { AuthService } from '@core/services/auth.service';
import { UserService } from '@core/services/user.service';

describe('ActualizarPerfilComponent', () => {
  let component: ActualizarPerfilComponent;
  let fixture: ComponentFixture<ActualizarPerfilComponent>;
  let authServiceMock: any;
  let userServiceMock: any;
  let routerMock: any;

  beforeEach(async () => {
    authServiceMock = {
      getCurrentUser: jest.fn().mockReturnValue({ ID_Usuario: 'test-id' })
    };
    
    userServiceMock = {
      getProfile: jest.fn().mockReturnValue({ subscribe: jest.fn() }),
      updateProfile: jest.fn().mockReturnValue({ subscribe: jest.fn() }),
      registrarActividad: jest.fn().mockReturnValue({ subscribe: jest.fn() })
    };
    
    routerMock = {
      navigate: jest.fn()
    };
    
    await TestBed.configureTestingModule({
      imports: [ActualizarPerfilComponent, ReactiveFormsModule, NoopAnimationsModule],
      providers: [
        provideHttpClient(),
        provideHttpClientTesting(),
        { provide: AuthService, useValue: authServiceMock },
        { provide: UserService, useValue: userServiceMock },
        { provide: Router, useValue: routerMock }
      ]
    }).compileComponents();

    fixture = TestBed.createComponent(ActualizarPerfilComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});