import { Component, OnInit } from '@angular/core';
import { PagesService } from "../services/pages.service";
import { Storage } from "@ionic/storage";
import { NavigationEnd, Router } from '@angular/router';

@Component({
  selector: 'app-terms',
  templateUrl: './terms.page.html',
  styleUrls: ['./terms.page.scss'],
})
export class TermsPage implements OnInit {
 
  CURRENT_LANG;
  DATA;
  USER: any;

  constructor(
    private _pagesService: PagesService,
    private storage: Storage,
    private router: Router
  ) { }

  ngOnInit() {
    
    this.storage.get('lang').then(value => {
      this.CURRENT_LANG = value;

      this._pagesService.get_page_content({ page: 'terms'}).subscribe(r => {
        this.DATA = {};

        if(r.terms_text){
          this.DATA['terms_text'] = r.terms_text;
        }
      })
    })
    this.subscribe_to_changes();
  }
  update_page_content() {
    this._pagesService.get_page_content({ page: 'terms', user: this.USER })
}
  subscribe_to_changes() {
    this.router.events.subscribe((val) => {
        if (val instanceof NavigationEnd && val.url.includes('other:terms')) {

            var get_lang = this.storage.get("lang");

            Promise.all([get_lang]).then(values => {
                this.CURRENT_LANG = values[0];
                this.update_page_content();
            });
            
        }
    });
    

}
  

}
