import { Component, OnInit } from '@angular/core';
import { Storage } from "@ionic/storage";
import { AjaxService } from '../services/ajax.service';

import { ToastController } from "@ionic/angular";
import { LoadingController } from "@ionic/angular";
import { GLOBALS } from '../app-globals';

@Component({
  selector: 'app-contact',
  templateUrl: 'contact.page.html',
  styleUrls: ['contact.page.scss']
})
export class ContactPage implements OnInit {
  CURRENT_LANG;
  USER;
  
  constructor(
    private storage: Storage,
    private ajax: AjaxService,
    private toastController: ToastController,  
    public loadingController: LoadingController,
  ){}

  ngOnInit(){

    this.storage.get('lang').then(value => {
      this.CURRENT_LANG = value;
    });

    this.storage.get('logged_user').then(value => {
      this.USER = value;
    });
  }

  async send(text: string){
    if(this.CURRENT_LANG == 'arm'){
      var please_wait = 'Խնդրում ենք սպասել';
    }else if (this.CURRENT_LANG == 'rus'){
      var please_wait = 'Пожалуйста подождите';
    }else{
      var please_wait = 'Please Wait';

    }

    const loading = await this.loadingController.create({
      message: please_wait,
      translucent: true
    });

    await loading.present();
    
    this.ajax.post('V_Users/support_message', {user: this.USER, text: text, lang: this.CURRENT_LANG}).subscribe(async r => {
      console.log(r);

      const toast = await this.toastController.create({
        message: r.message,
        duration: 4000
      });

      toast.present();
      loading.dismiss();

    });

  }
}
