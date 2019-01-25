import { CUSTOM_ELEMENTS_SCHEMA } from '@angular/core';
import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { MyqrPage } from './myqr.page';

describe('MyqrPage', () => {
  let component: MyqrPage;
  let fixture: ComponentFixture<MyqrPage>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ MyqrPage ],
      schemas: [CUSTOM_ELEMENTS_SCHEMA],
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(MyqrPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
