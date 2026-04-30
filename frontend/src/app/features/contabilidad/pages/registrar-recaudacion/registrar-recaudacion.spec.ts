import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ReactiveFormsModule } from '@angular/forms';
import { NoopAnimationsModule } from '@angular/platform-browser/animations';
import { RegistrarRecaudacionComponent } from './registrar-recaudacion';

describe('RegistrarRecaudacionComponent', () => {
  let component: RegistrarRecaudacionComponent;
  let fixture: ComponentFixture<RegistrarRecaudacionComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [RegistrarRecaudacionComponent, ReactiveFormsModule, NoopAnimationsModule]
    }).compileComponents();

    fixture = TestBed.createComponent(RegistrarRecaudacionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});