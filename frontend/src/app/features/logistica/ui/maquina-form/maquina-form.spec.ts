import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ReactiveFormsModule } from '@angular/forms';
import { NoopAnimationsModule } from '@angular/platform-browser/animations';
import { MaquinaFormComponent } from './maquina-form';

describe('MaquinaFormComponent', () => {
  let component: MaquinaFormComponent;
  let fixture: ComponentFixture<MaquinaFormComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [MaquinaFormComponent, ReactiveFormsModule, NoopAnimationsModule]
    }).compileComponents();

    fixture = TestBed.createComponent(MaquinaFormComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});