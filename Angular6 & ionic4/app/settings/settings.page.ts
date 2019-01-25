import { Component, OnInit } from '@angular/core';
import { Storage } from "@ionic/storage";
import { AjaxService } from "../services/ajax.service";
import { ToastController } from "@ionic/angular";
import { LoadingController } from "@ionic/angular";

import {TranslateService} from '@ngx-translate/core';
import { GLOBALS } from '../app-globals';

@Component({
  selector: 'app-settings',
  templateUrl: './settings.page.html',
  styleUrls: ['./settings.page.scss'],
})
export class SettingsPage implements OnInit {

  DATA: object;
  USER;
  CURRENT_LANG;
  

  constructor(
    private storage: Storage,
    private ajax: AjaxService,
    private toastController: ToastController,
    public loadingController: LoadingController,
    private translateService: TranslateService
  ) { }

  ngOnInit() {

    var vars = [
      this.storage.get('logged_user'),
      this.storage.get('lang')
    ];

    Promise.all(vars).then(value => {
      this.USER = value[0];
      this.CURRENT_LANG = value[1];

      let fullname = '';
      if (this.USER.userType == 'customer') {
        fullname = this.USER[this.CURRENT_LANG + "Name"] + " " + this.USER[this.CURRENT_LANG + "Surname"];
      } else {
        fullname = this.USER[this.CURRENT_LANG + "Name"];
      }




      switch (this.USER.userType) {
        case 'customer':
          this.DATA = {
            lang: this.CURRENT_LANG,
            fullname: fullname,
            phone: this.USER.phone,
            birthday: this.USER.birthday,
            email: this.USER.email,
          };
          break;

        case 'partner':
          this.DATA = {
            lang: this.CURRENT_LANG,
            fullname: fullname,
            phone: this.USER.phone,
            contractNumber: this.USER.contractNumber,
            email: this.USER.email,
          };
          break;

        case 'agent':
          this.DATA = {
            lang: this.CURRENT_LANG,
            fullname: fullname,
            phone: this.USER.phone,
            birthday: this.USER.birthday,
            email: this.USER.email,
            contractNumber: this.USER.contractNumber,
          };
          break;
      }

    })


  }
  set_lang(event){
    this.storage.set('lang', this.DATA['lang']);
    this.translateService.use(this.DATA['lang']);
    

    this.ajax.post("V_Users/update_lang", {id: this.USER.id, lang: this.DATA['lang']}).subscribe(r => {

    })
  }

  async save(event: any) {

    this.storage.set('lang', this.DATA['lang']);
    this.translateService.use(this.DATA['lang']);
    
    if(this.DATA['lang'] == 'arm'){
      var please_wait = 'Խնդրում ենք սպասել';
    }else if (this.DATA['lang'] == 'rus'){
      var please_wait = 'Пожалуйста подождите';
    }else{
      var please_wait = 'Please Wait';

    }
    const loading = await this.loadingController.create({
      
      
      message: please_wait,
      translucent: true
    });
    // await this.loadingController.componentOnReady();
    await loading.present();

    this.DATA['user_id'] = this.USER.id;

    this.ajax.post("V_Users/update_settings", this.DATA).subscribe(async r => {

      if (r.status == 'ok') {
        this.storage.set('logged_user', r.user);
      }

      const toast = await this.toastController.create({
        message: r.message,
        duration: 4000
      });

      toast.present();
      loading.dismiss();
    })

  }

}
