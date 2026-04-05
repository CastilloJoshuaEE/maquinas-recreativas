import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ReactiveFormsModule } from '@angular/forms';
import { NoopAnimationsModule } from '@angular/platform-browser/animations';
import { ComercioFormComponent } from './comercio-form';

describe('ComercioFormComponent', () => {
  let component: ComercioFormComponent;
  let fixture: ComponentFixture<ComercioFormComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ComercioFormComponent, ReactiveFormsModule, NoopAnimationsModule]
    }).compileComponents();

    fixture = TestBed.createComponent(ComercioFormComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});