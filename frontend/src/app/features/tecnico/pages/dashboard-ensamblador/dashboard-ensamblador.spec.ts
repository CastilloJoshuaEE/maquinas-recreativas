import { ComponentFixture, TestBed } from '@angular/core/testing';
import { DashboardEnsambladorComponent } from './dashboard-ensamblador';

describe('DashboardEnsambladorComponent', () => {
  let component: DashboardEnsambladorComponent;
  let fixture: ComponentFixture<DashboardEnsambladorComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [DashboardEnsambladorComponent]
    }).compileComponents();

    fixture = TestBed.createComponent(DashboardEnsambladorComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});