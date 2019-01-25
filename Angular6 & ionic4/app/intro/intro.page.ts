import { Component, OnInit, ViewChild } from '@angular/core';
import { ModalController, LoadingController, Platform } from '@ionic/angular';

import { Slides } from '@ionic/angular';
import { TranslateService } from '@ngx-translate/core';
import { Storage } from "@ionic/storage";
import { Router } from '@angular/router';
import {GLOBALS} from '../app-globals'

@Component({
  selector: 'app-intro',
  templateUrl: './intro.page.html',
  styleUrls: ['./intro.page.scss'],
})
export class IntroPage implements OnInit {
  @ViewChild(Slides) slides: Slides;

  selected_lang: string = null;
  constructor(
    private platform: Platform,
    private translate: TranslateService,
    private storage: Storage,
    private router: Router,
    private loadingController: LoadingController,
    private modalController: ModalController) { }

  ngOnInit() {

  }

  

  to_login_page() {
    if(this.platform.is('android'))
      window.location.href = '/login'
    else  
      this.router.navigateByUrl('/login');
  }

  empty_lang(): void {
    this.selected_lang = null;
  }

  next_slide() {
    this.slides.slideNext();
  }

  prev_slide() {
    this.slides.slidePrev();
  }

  select_lang(lang): void {
    this.selected_lang = lang;

    this.storage.set('lang', lang);
    this.translate.setDefaultLang(this.selected_lang);
  }

}
