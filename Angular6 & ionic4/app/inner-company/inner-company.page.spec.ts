import { CUSTOM_ELEMENTS_SCHEMA } from '@angular/core';
import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { InnerCompanyPage } from './inner-company.page';

describe('InnerCompanyPage', () => {
  let component: InnerCompanyPage;
  let fixture: ComponentFixture<InnerCompanyPage>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ InnerCompanyPage ],
      schemas: [CUSTOM_ELEMENTS_SCHEMA],
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(InnerCompanyPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
