import { Component, OnInit } from '@angular/core';

import { Platform, MenuController, ToastController, LoadingController } from '@ionic/angular';
import { SplashScreen } from '@ionic-native/splash-screen/ngx';
import { StatusBar } from '@ionic-native/status-bar/ngx';
import { Router, ActivatedRoute, NavigationEnd } from '@angular/router';
import { Storage } from "@ionic/storage";
import { OneSignal } from '@ionic-native/onesignal/ngx';

import { TranslateService } from '@ngx-translate/core';
import { GLOBALS } from './app-globals'
import { Network } from '@ionic-native/network/ngx';
import { AjaxService } from './services/ajax.service';


@Component({

  selector: 'app-root',
  templateUrl: 'app.component.html',
  styleUrls: ['app.component.scss'],
  providers: [TranslateService, OneSignal, Network]
})
export class AppComponent implements OnInit {

  USER;
  APP_FIRST_ENTER: boolean = true;
  CURRENT_LANG = 'arm';


  constructor(
    private platform: Platform,
    private splashScreen: SplashScreen,
    private statusBar: StatusBar,
    private router: Router,
    private storage: Storage,
    private menu: MenuController,
    private oneSignal: OneSignal,
    private route: ActivatedRoute,
    private translate: TranslateService,
    private toastController: ToastController,
    public loadingController: LoadingController,
    private network: Network,
    private ajax: AjaxService
  ) {
    this.initializeApp();
  }

  network_guide() {

    this.network.onDisconnect().subscribe(r => {
      document.body.setAttribute('no-internet', 'true');
    })

    this.network.onConnect().subscribe(r => {
      if (this.platform.is('android'))
        window.location.href = "/profile/pages/(home:home)"
      else
        this.router.navigateByUrl('/profile/pages/(home:home)');

      document.body.removeAttribute('no-internet');
    })

  }

  subscribe_to_changes() {
 
    this.router.events.subscribe((e) => {
      
      if(e instanceof NavigationEnd)
      {
        if(! window.location.href.includes('redirect')){
          GLOBALS.routes.previous.push( GLOBALS.routes.current );
          GLOBALS.routes.current = window.location.pathname;
        }
      } 
      
      this.storage.get("logged_user").then(val => {
        this.USER = val;
      })
    });
  }



  ngOnInit() {
    
    if (GLOBALS.loading)
      GLOBALS['loading'].dismiss();

    this.subscribe_to_changes();

    
  }

  initOneSignal() {

    var iosSettings: any = {};
    iosSettings["kOSSettingsKeyAutoPrompt"] = true;
    iosSettings["kOSSettingsKeyInAppLaunchURL"] = false;


    this.oneSignal.startInit('', '');
    this.oneSignal.inFocusDisplaying(this.oneSignal.OSInFocusDisplayOption.InAppAlert);
    this.oneSignal.iOSSettings(iosSettings)
    this.oneSignal.handleNotificationOpened().subscribe(data => {
      const answer = data.action.actionID;
      const notification = data.notification.payload.additionalData.notId;

      this.ajax.post('V_Notifications/answer', { answer: answer, notification: notification }).subscribe(r => {
      });
    })
    this.oneSignal.endInit();

  }

  initializeApp() {

    this.network_guide();

    var storage_values = [
      this.storage.get("lang"),
      this.storage.get("app_first_enter"),
      this.storage.get("logged_user"),
      this.storage.get('forced-logout')
    ];
    
    Promise.all(storage_values).then(values => {

      this.CURRENT_LANG = values[0];
      this.APP_FIRST_ENTER = values[1];
      this.USER = values[2];
      let forced_logout = values[3];

      if (!values[0]) {
        this.storage.set('lang', 'arm');
      }
      this.translate.setDefaultLang(this.CURRENT_LANG);

      this.translate.getTranslation(this.CURRENT_LANG).subscribe(val => {
        GLOBALS.dict = val;
      })
      if (forced_logout) {
        this.storage.remove('forced-logout').then(r => {
          this.router.navigateByUrl('/login');
        })
      } else {
        this.auth();
      }

    });

    this.platform.ready().then(() => {

      if (this.platform.is('ios'))
        this.initOneSignal();

      this.statusBar.styleDefault();
      this.splashScreen.hide();
    });
  }

  
  close_menu() {
    this.menu.close();
  }

  async logout() {
    const self = this;
    event.preventDefault();
    let a = this.storage.remove('logged_user');
    let b = this.storage.remove('player-id');
    let c = this.storage.set('forced-logout', true);


    
    if(this.CURRENT_LANG == 'arm'){
      var please_wait = 'Խնդրում ենք սպասել';
    }else if (this.CURRENT_LANG == 'rus'){
      var please_wait = 'Пожалуйста подождите';
    }else{
      var please_wait = 'Please Wait';

    }
    GLOBALS.loading = await this.loadingController.create({
      message: please_wait,
      translucent: true
    });

    self.close_menu();
    await GLOBALS.loading.present();

    Promise.all([a, b, c]).then(r => {

      if (this.platform.is('android'))
        window.location.href = '/login'
      else {

        
        this.router.navigateByUrl('/login');

       }

      self.close_menu();
    }).catch(err => {

      if (this.platform.is('android'))
        window.location.href = '/login'
      else {
        
        window.location.reload();
      }

    })
  }

  auth() {
    if (!this.APP_FIRST_ENTER) {
      this.router.navigateByUrl('/intro');
      this.storage.set('app_first_enter', Date());
    } else {

      if (!this.USER) {
        this.router.navigateByUrl('/login');
      } else {
        this.router.navigateByUrl('/profile/pages/(home:home)');
      }
    }
  }
}
