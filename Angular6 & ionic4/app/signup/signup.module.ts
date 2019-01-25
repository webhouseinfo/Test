import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';

import { Routes, RouterModule } from '@angular/router';

import { IonicModule } from '@ionic/angular';
import { SignupPage } from './signup.page';

import { loading } from '../components/loading/loading.component';
import { MaterialModule } from '../../app/services/modules/material-module';

import { AgmCoreModule } from '@agm/core';
import { TranslateModule } from '@ngx-translate/core';

const routes: Routes = [
  {
    path: '',
    component: SignupPage
  }
];



@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    AgmCoreModule,
    TranslateModule,
    ReactiveFormsModule,
    IonicModule, MaterialModule,
    RouterModule.forChild(routes)
  ],
  declarations: [SignupPage, loading]
})
export class SignupPageModule { }
