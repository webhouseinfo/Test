import { Component, OnInit } from '@angular/core';
import { PagesService } from "../services/pages.service";
import { Storage } from "@ionic/storage";
import { GLOBALS } from '../app-globals';
import { DomSanitizer, SafeUrl } from '@angular/platform-browser';


@Component({
  selector: 'app-myqr',
  templateUrl: './myqr.page.html',
  styleUrls: ['./myqr.page.scss'],
})
export class MyqrPage implements OnInit {
  USER;
  CARD;
  QR_IMAGE_SRC = null;

  constructor(
    private _pagesService: PagesService,
    private storage: Storage,
    private ds: DomSanitizer
  ) { }

  ngOnInit() {

    let get_user = Promise.resolve(this.storage.get('logged_user'));
    let get_qr_image = Promise.resolve(this.storage.get('qr_image'));

    // get_qr_image.then(source => {
    //   if (source){
    //     let url: SafeUrl = this.ds.bypassSecurityTrustUrl(source);
    //     this.QR_IMAGE_SRC = url;
    //   }
    // })

    get_user.then(value => {
      this.USER = value;

      this._pagesService.get_page_content({ page: 'myqr', user_id: this.USER.id }).subscribe(r => {
        this.CARD = r.card;
        this.QR_IMAGE_SRC = GLOBALS.HOST + '/images/qr/' + this.CARD.qrImage;
      })
    });


  }

}
