import { Component, OnInit,  } from '@angular/core';
import { PagesService } from "../services/pages.service";
import { Storage } from "@ionic/storage";
import { AjaxService } from "../services/ajax.service";
import { Router, NavigationEnd } from '@angular/router';
import {GLOBALS} from "../app-globals";

@Component({
  selector: 'app-notifications',
  templateUrl: './notifications.page.html',
  styleUrls: ['./notifications.page.scss'],
})
export class NotificationsPage implements OnInit {

  USER;
  CURRENT_LANG = 'arm';
  NOTIFICATIONS = null;
  HOST: string = GLOBALS['HOST'];


  constructor(
    private _pagesService: PagesService,
    private storage: Storage,
    private ajax: AjaxService,
    private router: Router
  ) { }

  ngOnInit() {

    this.storage.get('lang').then(val => {
      this.CURRENT_LANG = val;
    });

    this.storage.get('logged_user').then(val => {
      this.USER = val;

      this.update_notifications();
      this.subscribe_to_changes();
    });

  }

  subscribe_to_changes(){

    this.router.events.subscribe((val) => {
        if(val instanceof NavigationEnd && val.url.includes('notifications:')){
          this.update_notifications();
        }
    });
  }
  
  update_notifications(){
    this.NOTIFICATIONS = null;
    this._pagesService.get_page_content({ user: this.USER, page: 'notifications' })
      .subscribe(r => {
        if(r.notifications){
          this.NOTIFICATIONS = r.notifications;
        }
      })
  }

  submit_answer(button:string, notification){
    this.NOTIFICATIONS = this.NOTIFICATIONS.filter(a => a.id != notification.id);
    this.ajax.post("V_Users/submit_notification_answer", {button: button, user: this.USER, id: notification.id}).subscribe(r => {
      console.log(r);
    })
  }

}
