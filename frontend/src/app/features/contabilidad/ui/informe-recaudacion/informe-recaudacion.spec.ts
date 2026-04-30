import { ComponentFixture, TestBed } from '@angular/core/testing';
import { InformeRecaudacionComponent } from './informe-recaudacion';

describe('InformeRecaudacionComponent', () => {
  let component: InformeRecaudacionComponent;
  let fixture: ComponentFixture<InformeRecaudacionComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [InformeRecaudacionComponent]
    }).compileComponents();

    fixture = TestBed.createComponent(InformeRecaudacionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});