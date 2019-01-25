import { Component, OnInit } from '@angular/core';
import { PagesService } from "../services/pages.service";
import { Storage } from "@ionic/storage";
import { Router, NavigationEnd } from '@angular/router';

@Component({
  selector: 'app-about',
  templateUrl: 'about.page.html',
  styleUrls: ['about.page.scss']
})
export class AboutPage  implements OnInit {

  DATA: object;
  CURRENT_LANG;
  USER: any;


  constructor(    
    private _pagesService: PagesService,
    private storage: Storage,
    private router: Router
  ) { }

  ngAfterViewChecked(){
    // this.storage.get('lang').then(value => {
    //   this.CURRENT_LANG = value;
    // });
    //alert("askdkajsdj")
  }

  ngOnInit() {

    this.storage.get('lang').then(value => {
      this.CURRENT_LANG = value;

      this._pagesService.get_page_content({ page: 'about',  }).subscribe(r => {
        this.DATA = {};

        if(r.about_text){
          this.DATA['about_text'] = r.about_text;
        }
      })
    })
    this.subscribe_to_changes();
  }
  update_page_content() {
    this._pagesService.get_page_content({ page: 'about', user: this.USER })
}
  subscribe_to_changes() {
    this.router.events.subscribe((val) => {
        if (val instanceof NavigationEnd && val.url.includes('other:about')) {

            var get_lang = this.storage.get("lang");

            Promise.all([get_lang]).then(values => {
                this.CURRENT_LANG = values[0];
                this.update_page_content();
            });
            
        }
    });
} 
}
