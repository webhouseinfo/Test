import { Component, OnInit } from '@angular/core';

import { Storage } from "@ionic/storage";
import { AjaxService } from "../services/ajax.service";
import { ToastController } from "@ionic/angular";
import { LoadingController } from "@ionic/angular";
import { PagesService } from "../services/pages.service";
import { GLOBALS } from '../app-globals';

@Component({
  selector: 'app-invite',
  templateUrl: './invite.page.html',
  styleUrls: ['./invite.page.scss'],
})
export class InvitePage implements OnInit {

  USER;
  current_lang = 'arm';
  GLOBALS = GLOBALS;

  INVIDED_USER = {
    email: null,
    type: 'once'
  };

  PAGE = {
    invite_money: null
  };

  constructor(
    private storage: Storage,
    private ajax: AjaxService,
    private toastController: ToastController,
    public loadingController: LoadingController,
    private _pagesService: PagesService,
  ) { }

  ngOnInit() {

    this._pagesService.get_page_content({ page: 'invite' })
      .subscribe(r => {
        if (r.invite_money) {
          this.PAGE.invite_money = r.invite_money.money;
        }
      })

    this.storage.get('logged_user').then(value => {
      this.USER = value;
    });

    this.storage.get("lang").then(val => {
      this.current_lang = val;
    });
  }

  flush_invitation(){
    this.INVIDED_USER = {
      email: null,
      type: 'once'
    };
  }

  async submit_invitation(){
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

    this.ajax.post('V_Users/submit_invitation', { lang: this.current_lang,user: this.USER, data: this.INVIDED_USER }).subscribe(async r => {
      
      loading.dismiss();

      if (r.message) {
        const toast = await this.toastController.create({
          message: r.message,
          duration: 4000
        });
        toast.present();
      }

      if(r.status == 'ok')
        this.INVIDED_USER.email = null
      
    })
  }

  async submit_email(email) {
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

    this.ajax.post('V_Users/invite_check_email', { lang: this.current_lang, email: email, user: this.USER }).subscribe(async r => {

      loading.dismiss();

      if (r.message) {
        const toast = await this.toastController.create({
          message: r.message,
          duration: 4000
        });
        toast.present();
      }

      if (r.status == 'ok') {
        this.INVIDED_USER = {
          email: email,
          type: 'once',
        }
      }

    })
  }

}
