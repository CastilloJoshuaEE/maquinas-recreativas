import { ComponentFixture, TestBed } from '@angular/core/testing';
import { LevantarInformeComponent } from './levantar-informe';

describe('LevantarInformeComponent', () => {
  let component: LevantarInformeComponent;
  let fixture: ComponentFixture<LevantarInformeComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [LevantarInformeComponent]
    }).compileComponents();

    fixture = TestBed.createComponent(LevantarInformeComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});