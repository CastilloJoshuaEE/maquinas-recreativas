import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ReactiveFormsModule } from '@angular/forms';
import { NoopAnimationsModule } from '@angular/platform-browser/animations';
import { Router } from '@angular/router';
import { ToastrService } from 'ngx-toastr';
import { RegistroComponent } from './registro.component';
import { AuthService } from '@core/services/auth.service';

describe('RegistroComponent', () => {
  let component: RegistroComponent;
  let fixture: ComponentFixture<RegistroComponent>;
  let authServiceMock: any;
  let routerMock: any;
  let toastrMock: any;

  beforeEach(async () => {
    authServiceMock = {
      register: jest.fn().mockReturnValue({ subscribe: jest.fn() })
    };
    
    routerMock = {
      navigate: jest.fn()
    };
    
    toastrMock = {
      success: jest.fn(),
      error: jest.fn()
    };
    
    await TestBed.configureTestingModule({
      imports: [RegistroComponent, ReactiveFormsModule, NoopAnimationsModule],
      providers: [
        { provide: AuthService, useValue: authServiceMock },
        { provide: Router, useValue: routerMock },
        { provide: ToastrService, useValue: toastrMock }
      ]
    }).compileComponents();

    fixture = TestBed.createComponent(RegistroComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should have invalid form when empty', () => {
    expect(component.registroForm.valid).toBeFalsy();
  });

  it('should validate CI pattern', () => {
    const ciControl = component.registroForm.get('ci');
    ciControl?.setValue('123456789');
    expect(ciControl?.hasError('pattern')).toBeTruthy();
    
    ciControl?.setValue('1234567890');
    expect(ciControl?.hasError('pattern')).toBeFalsy();
  });
});