import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ReactiveFormsModule } from '@angular/forms';
import { NoopAnimationsModule } from '@angular/platform-browser/animations';
import { UserFormComponent } from './user-form';

describe('UserFormComponent', () => {
  let component: UserFormComponent;
  let fixture: ComponentFixture<UserFormComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [UserFormComponent, ReactiveFormsModule, NoopAnimationsModule]
    }).compileComponents();

    fixture = TestBed.createComponent(UserFormComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should have invalid form when empty in crear mode', () => {
    component.modo = 'crear';
    component.ngOnInit();
    expect(component.userForm.valid).toBeFalsy();
  });

  it('should validate CI pattern', () => {
    component.modo = 'crear';
    component.ngOnInit();
    const ciControl = component.userForm.get('ci');
    ciControl?.setValue('123456789');
    expect(ciControl?.hasError('pattern')).toBeTruthy();
    
    ciControl?.setValue('1234567890');
    expect(ciControl?.hasError('pattern')).toBeFalsy();
  });
});