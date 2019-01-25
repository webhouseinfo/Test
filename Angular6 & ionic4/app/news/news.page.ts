import { Component, OnInit } from '@angular/core';
import { Storage } from "@ionic/storage";
import { PagesService } from "../services/pages.service";
import {GLOBALS} from '../app-globals'
import { NavigationEnd, Router } from '@angular/router';
@Component({
  selector: 'app-news',
  templateUrl: './news.page.html',
  styleUrls: ['./news.page.scss'],
})
export class NewsPage implements OnInit {

  NEWS;

  CURRENT_LANG;
  HOST: string = GLOBALS['HOST'];
  USER: any;

  constructor(
    private _pagesService: PagesService,
    private storage: Storage,
    private router: Router,
  ) { }

  ngOnInit() {

    this.storage.get('lang').then(value => {
      this.CURRENT_LANG = value;

      this._pagesService.get_page_content({ page: 'news',  }).subscribe(r => {
        this.NEWS = {};

        if(r.news){
          this.NEWS['list'] = r.news;
        }
      })
    })
    this.subscribe_to_changes();
  }
  update_page_content() {
    this._pagesService.get_page_content({ page: 'news', user: this.USER })
}
  subscribe_to_changes() {
    this.router.events.subscribe((val) => {
        if (val instanceof NavigationEnd && val.url.includes('other:news')) {

            var get_lang = this.storage.get("lang");

            Promise.all([get_lang]).then(values => {
                this.CURRENT_LANG = values[0];
                this.update_page_content();
            });
            
        }
    });
} 

}
