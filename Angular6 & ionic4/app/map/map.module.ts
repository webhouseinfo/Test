import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Routes, RouterModule } from '@angular/router';

import { IonicModule } from '@ionic/angular';

import { MapPage } from './map.page';
import { AgmCoreModule } from '@agm/core';
import { TranslateModule } from '@ngx-translate/core';


const routes: Routes = [
  {
    path: '',
    component: MapPage
  }
];

@NgModule({
  imports: [
    CommonModule,
    FormsModule,
    IonicModule,
    TranslateModule,
    AgmCoreModule.forRoot({
      apiKey: "AIzaSyBGprELwsSK0vZGjps_YIkizCOfavQU5ZM",
      libraries: ["places"]
    }),
    RouterModule.forChild(routes)
  ],
  declarations: []
})
export class MapPageModule {}
