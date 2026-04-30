import { ComponentFixture, TestBed } from '@angular/core/testing';
import { NoopAnimationsModule } from '@angular/platform-browser/animations';
import { UserTableComponent } from './user-table';

describe('UserTableComponent', () => {
  let component: UserTableComponent;
  let fixture: ComponentFixture<UserTableComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [UserTableComponent, NoopAnimationsModule]
    }).compileComponents();

    fixture = TestBed.createComponent(UserTableComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should emit onEdit when editUser is called', () => {
    const user = { id: '123', nombre: 'Test', apellido: 'User' } as any;
    jest.spyOn(component.onEdit, 'emit');
    component.editUser(user);
    expect(component.onEdit.emit).toHaveBeenCalledWith(user);
  });

  it('should emit onDelete when deleteUser is called', () => {
    const user = { id: '123', nombre: 'Test', apellido: 'User' } as any;
    jest.spyOn(component.onDelete, 'emit');
    component.deleteUser(user);
    expect(component.onDelete.emit).toHaveBeenCalledWith(user);
  });
});