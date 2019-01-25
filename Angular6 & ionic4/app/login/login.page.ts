import { Component, OnInit, ViewChild, ElementRef } from '@angular/core';
import { Router } from '@angular/router';
import {
  FormBuilder,
  FormGroup,
  Validators,
  FormControl
} from "@angular/forms";

import { UsersService } from "../services/UsersService";

import { ToastController, Platform } from "@ionic/angular";
import { LoadingController } from "@ionic/angular";
import { Storage } from "@ionic/storage";
import { AjaxService } from "../services/ajax.service";

import { Facebook, FacebookLoginResponse } from '@ionic-native/facebook/ngx';
import { OneSignal } from '@ionic-native/onesignal/ngx';
import {TranslateService} from '@ngx-translate/core';
import {GLOBALS} from '../app-globals'
import { WINDOW_CALLBACK_NAME } from 'google-maps';

@Component({
  selector: 'app-login',
  templateUrl: './login.page.html',
  styleUrls: ['./login.page.scss'],
  providers: [UsersService, OneSignal]


})
export class LoginPage implements OnInit {

  login_form: FormGroup;
  current_lang = 'arm';
  ACTIVE_FORM = 'LOGIN';
  FORGOTTEN_BRANCHES = [];
  PLAYER_ID = null;

  login_validation_messages = {
    login: [{ type: "required", message: GLOBALS.dict['enter_login'] }],
    password: [{ type: "required", message: GLOBALS.dict['enter_password'] }],
  }

  constructor(
    private platform: Platform,
    private _formBuilder: FormBuilder,
    private _usersService: UsersService,
    private toastController: ToastController,
    public loadingController: LoadingController,
    private storage: Storage,
    private router: Router,
    private ajax: AjaxService,
 
    private fb: Facebook,
    private oneSignal: OneSignal,
    private translate: TranslateService
  ) {
   
   }

  initOneSignal() {

    var iosSettings: any = {};
    iosSettings["kOSSettingsKeyAutoPrompt"] = true;
    iosSettings["kOSSettingsKeyInAppLaunchURL"] = false;

  
      this.oneSignal.startInit('a4a28f40-78e6-4c02-8747-27c54e461d0e', 'vilmar-efa88');
      this.oneSignal.inFocusDisplaying(this.oneSignal.OSInFocusDisplayOption.InAppAlert);
      this.oneSignal.iOSSettings(iosSettings)

      this.oneSignal.getIds().then(r => {
        this.PLAYER_ID = r.userId;
        this.storage.set('player-id', this.PLAYER_ID);
      })
     
      this.oneSignal.handleNotificationOpened().subscribe(data => {
      
        const answer = data.action.actionID;
        const notification = data.notification.payload.additionalData.notId;

        this.ajax.post('V_Notifications/answer', { answer: answer, notification: notification }).subscribe(r => {

        });
      })
      this.oneSignal.endInit();
    
  }

  ngOnInit() {

    if(GLOBALS.loading)
      GLOBALS['loading'].dismiss();
    
    var self = this;
    this.initOneSignal();
    this.login_form = this._formBuilder.group({
      login: ["", [Validators.required]],
      password: ["", [Validators.required]]
    });

    this.storage.get("lang").then(val => {
      self.current_lang = val;
    });

  }


 
 

  fb_login() {
    let permissions = [
      'public_profile',
      'email'
    ];

    this.fb.login(permissions)
      .then((res: FacebookLoginResponse) => {

        this.fb.api("me?fields=id,name,email,first_name,address,birthday,gender,last_name", []).then(profile => {
          let user = {
            'name': profile['first_name'],
            'surname': profile['last_name'],
            'email': profile['email'],
            'address': profile['address'],
            'gender': profile['gender'],
            'birthday': profile['birthday'],
          };
          // alert(user);

          this.ajax.post("V_Users/check_social_account", { lang: this.current_lang , email: profile['email'], player_id : this.PLAYER_ID }).subscribe(r => {
            if (r.status == 'account_found') {
              this.storage.set('logged_user', r.user);

              if (this.platform.is('android'))
                window.location.href = '/profile/pages/(home:home)'
              else
                this.router.navigateByUrl('/profile/pages/(home:home)');

              return;
            } else {
              this.storage.set('social_logged_user', user);
              this.router.navigateByUrl('/signup');
            }
          })


        });


      })
     

    this.fb.logEvent(this.fb.EVENTS.EVENT_NAME_ADDED_TO_CART);
  }

  closeFooter() {
    document.getElementById("hideFooter").style.visibility = 'hidden';
  }
  openFooter() {
    document.getElementById("hideFooter").style.visibility = 'visible';
  }

  async check_forgotten_email(email: string) {
    if(this.current_lang == 'arm'){
      var please_wait = 'Խնդրում ենք սպասել';
    }else if (this.current_lang == 'rus'){
      var please_wait = 'Пожалуйста подождите';
    }else{
      var please_wait = 'Please Wait';

    }
    const loading = await this.loadingController.create({
      message: please_wait,
      translucent: true
    });

    await loading.present();

    this.ajax.post("V_Users/check_forgottend_email", { lang: this.current_lang, email: email }).subscribe(async r => {
      loading.dismiss();

      if (r.status == 'ok') {
        this.ACTIVE_FORM = 'LOGIN';
      }

      if (r.branches) {
        this.FORGOTTEN_BRANCHES = r.branches;
      }

      const toast = await this.toastController.create({
        message: r.message,
        duration: 4000
      });
      toast.present();
    });

  }

  async send_email_to_branches(selected_branches) {
    if(this.current_lang == 'arm'){
      var please_wait = 'Խնդրում ենք սպասել';
    }else if (this.current_lang == 'rus'){
      var please_wait = 'Пожалуйста подождите';
    }else{
      var please_wait = 'Please Wait';

    }
    const loading = await this.loadingController.create({
      message: please_wait,
      translucent: true
    });

    await loading.present();

    this.ajax.post("V_Users/update_selected_branches_passwords", { lang: this.current_lang, branches: selected_branches }).subscribe(async r => {

      if (r.status == 'ok') {
        this.FORGOTTEN_BRANCHES = [];
        this.ACTIVE_FORM = 'LOGIN';
      }

      const toast = await this.toastController.create({
        message: r.message,
        duration: 4000
      });

      toast.present();
      loading.dismiss();
    })

  }

  async to_signup_page() {
    if(this.current_lang == 'arm'){
      var please_wait = 'Խնդրում ենք սպասել';
    }else if (this.current_lang == 'rus'){
      var please_wait = 'Пожалуйста подождите';
    }else{
      var please_wait = 'Please Wait';

    }
    GLOBALS['loading'] = await this.loadingController.create({
      message: please_wait,
      translucent: true
    });

    await GLOBALS['loading'].present();

    this.router.navigateByUrl('/signup');
  }
  
 
  
  async login_submit() {

    var data = { lang: this.current_lang, player_id: this.PLAYER_ID };

    Object.keys(this.login_form.controls).forEach(key => {
      data[key] = this.login_form.get(key).value;
    });

    if(this.current_lang == 'arm'){
      var please_wait = 'Խնդրում ենք սպասել';
    }else if (this.current_lang == 'rus'){
      var please_wait = 'Пожалуйста подождите';
    }else{
      var please_wait = 'Please Wait';

    }

    GLOBALS.loading = await this.loadingController.create({
      message: please_wait,
      translucent: true
    });

    await GLOBALS.loading.present();

    this._usersService.login_attempt(data)
      .subscribe(
        async r => {
          

          if (r.status == "ok") {
            this.storage.set('logged_user', r.user);

            if(this.platform.is('android'))
              window.location.href = '/profile/pages/(home:home)'
            else
              this.router.navigateByUrl('/profile/pages/(home:home)');
            return;
          }

          GLOBALS.loading.dismiss();
          const toast = await this.toastController.create({
            message: r.message,
            duration: 4000
          });
          toast.present();
        },
        err => {
          GLOBALS.loading.dismiss();
          console.log(err);
        }
      );
  }



}

