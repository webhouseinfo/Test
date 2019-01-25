import { NgModule } from '@angular/core';

import { BrowserModule } from '@angular/platform-browser';
import { RouterModule, RouteReuseStrategy } from '@angular/router';
import { IonicModule, IonicRouteStrategy } from '@ionic/angular';
import { SplashScreen } from '@ionic-native/splash-screen/ngx';
import { StatusBar } from '@ionic-native/status-bar/ngx';
import { AppRoutingModule } from './app-routing.module';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { MaterialModule } from '../app/services/modules/material-module';

import { AppComponent } from './app.component';
import { HttpClientModule, HttpClient } from '@angular/common/http';
import { LaunchNavigator } from '@ionic-native/launch-navigator/ngx';

import { IonicStorageModule } from '@ionic/storage';
import { AgmCoreModule } from '@agm/core';

import { BarcodeScanner } from '@ionic-native/barcode-scanner/ngx';
import { Geolocation } from '@ionic-native/geolocation/ngx';

import { FileTransfer } from '@ionic-native/file-transfer/ngx';

import { TranslateModule, TranslateLoader } from '@ngx-translate/core';
import { TranslateHttpLoader } from '@ngx-translate/http-loader';
import { ProfilePageModule } from './profile/profile.module';
import { Network } from '@ionic-native/network/ngx';


import { Facebook } from '@ionic-native/facebook/ngx';
import { MAT_STEPPER_GLOBAL_OPTIONS } from '@angular/cdk/stepper';

import { LocationAccuracy } from '@ionic-native/location-accuracy/ngx';


export function HttpLoaderFactory(http: HttpClient) {
  return new TranslateHttpLoader(http, './assets/i18n/', '.json');
}


@NgModule({
  declarations: [
    AppComponent,
  ],
  entryComponents: [],
  imports: [
    AgmCoreModule.forRoot({
      apiKey: "AIzaSyBGprELwsSK0vZGjps_YIkizCOfavQU5ZM",
      libraries: ["places"]
    }),
    HttpClientModule,
    BrowserModule,
    IonicModule.forRoot(),
    TranslateModule.forRoot({
      loader: {
        provide: TranslateLoader,
        useFactory: HttpLoaderFactory,
        deps: [HttpClient]
      }
    }),
    IonicStorageModule.forRoot(),
    AppRoutingModule,
    BrowserAnimationsModule,
    MaterialModule],
  providers: [
    Facebook,
    StatusBar,
    Geolocation,
    LaunchNavigator,
    
    FileTransfer,
    BarcodeScanner,
    Network,
    LocationAccuracy,
    SplashScreen,
    {
      provide: RouteReuseStrategy,
      useClass: IonicRouteStrategy
    },
    {
      provide: MAT_STEPPER_GLOBAL_OPTIONS,
      useValue: { 
        displayDefaultIndicatorType: false,
        showError: true
      }
    }
  ],
  bootstrap: [AppComponent]
})
export class AppModule { }
