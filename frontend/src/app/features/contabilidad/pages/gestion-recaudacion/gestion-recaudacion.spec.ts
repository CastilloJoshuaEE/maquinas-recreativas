import { ComponentFixture, TestBed } from '@angular/core/testing';
import { GestionRecaudacionComponent } from './gestion-recaudacion';

describe('GestionRecaudacionComponent', () => {
  let component: GestionRecaudacionComponent;
  let fixture: ComponentFixture<GestionRecaudacionComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [GestionRecaudacionComponent]
    }).compileComponents();

    fixture = TestBed.createComponent(GestionRecaudacionComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});