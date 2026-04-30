import { ComponentFixture, TestBed } from '@angular/core/testing';
import { MaquinaListComponent } from './maquina-list';

describe('MaquinaListComponent', () => {
  let component: MaquinaListComponent;
  let fixture: ComponentFixture<MaquinaListComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [MaquinaListComponent]
    }).compileComponents();

    fixture = TestBed.createComponent(MaquinaListComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});