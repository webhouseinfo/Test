import { Component, OnInit } from '@angular/core';
import { Storage } from "@ionic/storage";
import { Router } from '@angular/router';
import { TranslateService } from '@ngx-translate/core';

@Component({
  selector: 'app-profile',
  templateUrl: './profile.page.html',
  styleUrls: ['./profile.page.scss'],
})
export class ProfilePage implements OnInit {
  
  USER;

  constructor(
    private storage: Storage,
    public router: Router,
    public translate: TranslateService
  ) {

   }

   on_outlet_activation(event){
    (<any>event).deactivate();
   }

  ngOnInit() {

    this.storage.get('logged_user').then(value => {
      this.USER = value;
    });

  }

}
