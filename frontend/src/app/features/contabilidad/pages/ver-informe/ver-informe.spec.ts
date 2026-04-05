import { ComponentFixture, TestBed } from '@angular/core/testing';
import { VerInformeComponent } from './ver-informe';

describe('VerInformeComponent', () => {
  let component: VerInformeComponent;
  let fixture: ComponentFixture<VerInformeComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [VerInformeComponent]
    }).compileComponents();

    fixture = TestBed.createComponent(VerInformeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});