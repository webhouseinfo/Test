import { Component, OnInit, ViewChild } from '@angular/core';
import { PagesService } from "../services/pages.service";
import { Storage } from "@ionic/storage";
import { AjaxService } from "../services/ajax.service";
import { ToastController } from "@ionic/angular";
import { Router, NavigationEnd } from '@angular/router';
import { LoadingController } from "@ionic/angular";
import { UsersService } from '../services/UsersService';
import { InfiniteScroll } from '@ionic/angular';

import { FileTransfer, FileUploadOptions, FileTransferObject } from '@ionic-native/file-transfer/ngx';
import { GLOBALS } from '../app-globals';


export interface PeriodicElement {
  date: number;
  card_number: string;
  price: number;
  paid_money: string;
  paid_points: string;
  type: string;
  paid_partner: string;
  paid_admin: string;
}

const ELEMENT_DATA: PeriodicElement[] = [];

@Component({
  selector: 'app-account',
  templateUrl: './account.page.html',
  styleUrls: ['./account.page.scss'],
  providers: [UsersService]
})
export class AccountPage implements OnInit {

  USER;
  OUTS = null;
  INS = null;
  FULL_TRANSACTIONS = [];
  CURRENT_LANG;
  SELECTED_IN_TYPE = "company";
  INACTIVE_MONEY = [];
  access_gained;
  COMMENT_FORM_VISIBLE = false;
  partner_comments = [];
  ALL_COMMENTS_COUNT = 0;
  CARD;
  transactions_page: number = 0;
  partner_comments_page: number = 0;

  COMMENT = {
    'stars': 0,
    'text': '',
    'partner_id': 0,
    'transaction_id': 0
  };

  displayedColumns =
    ['date', 'card_number', 'price', 'sale', 'paid_money', 'paid_points','all_points'];
  dataSource = [];
  displayedSource = [];

  constructor(
    private _pagesService: PagesService,
    private storage: Storage,
    private ajax: AjaxService,
    private toastController: ToastController,
    private router: Router,
    public loadingController: LoadingController,
    private _usersService: UsersService,
    private transfer: FileTransfer
  ) { }

  ngOnInit() {

    this.storage.get("lang").then(val => {
      this.CURRENT_LANG = val;
    });

    this.storage.get("logged_user").then(val => {
      this.USER = val;

      this.update_transactions_data();
      this.subscribe_to_changes();
      this.update_customer_transactions();
      this.update_partner_comments();

    });
  }

  show_comment_form(star_numer, transaction) {
    event.stopPropagation();
    event.stopImmediatePropagation();

    this.COMMENT_FORM_VISIBLE = true;
    this.COMMENT.stars = star_numer;
    this.COMMENT.transaction_id = transaction.id;
    this.COMMENT.partner_id = transaction.partner_id;
    return false;
  }

  hide_comment_form() {
    // this.COMMENT.text = '';
    // this.COMMENT.stars = 0;
    this.COMMENT_FORM_VISIBLE = false;
  }

  bind_comment_to_transaction() {
    var found_transaction = null;
    var found_index = null;

    for (let i = 0; i < this.INS.length; i++) {
      if (this.INS[i].id == this.COMMENT.transaction_id) {
        found_index = i;
        found_transaction = this.INS[i];
        found_transaction.comment = Object.assign({}, this.COMMENT);
        this.INS[i] = found_transaction;
      }
    }

    if (!found_transaction) {
      for (let i = 0; i < this.OUTS.length; i++) {
        if (this.OUTS[i].id == this.COMMENT.transaction_id) {
          found_index = i;
          found_transaction = this.OUTS[i];
          found_transaction.comment = Object.assign({}, this.COMMENT);
          this.OUTS[i] = found_transaction;
        }
      }
    }

  }
  async submit_comment() {

    this.bind_comment_to_transaction();
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

    this._usersService.send_comment({
      comment: this.COMMENT,
      user: this.USER,
      lang: this.CURRENT_LANG,
      partner_id: this.COMMENT.partner_id
    }).subscribe(async r => {

      if (r.status == 'ok') {
        this.hide_comment_form();
      }

      const toast = await this.toastController.create({
        message: r.message,
        duration: 4000
      });

      toast.present();
      loading.dismiss();

    })

  }

  async open_transaction_attempt(password: string) {
    if (password == this.USER.trans_password) {
      this.access_gained = true;
    } else {
      this.access_gained = false;
      if(this.CURRENT_LANG == 'arm'){
        var wrong_password = 'Գաղտնաբառը սխալ է';
      }else if (this.CURRENT_LANG == 'rus'){
        var wrong_password = 'Неверный пароль';
      }else{
        var wrong_password = 'Wrong password';
  
      }
      const toast = await this.toastController.create({

        message: wrong_password,
        duration: 4000
      });
      toast.present();
    }
  }

  filter_by_date(start: any, end: any) {

    if (!start.trim().length || !end.trim().length) return;

    start = (new Date(start)).getTime();
    end = (new Date(end)).getTime() + 86400000 // ONE DAY;

    this.displayedSource = [];

    for (let i = 0; i < this.dataSource.length; i++) {
      var current_time = (new Date(this.dataSource[i].date)).getTime();

      if (current_time >= start && current_time <= end) {
        this.displayedSource.push(this.dataSource[i]);
      }
    }

  }

  async download_excel() {
    var self = this;

    let params = {
      dataid: this.USER.id,
      datainp: 'transaction',
      datatransaction: 'partner',
      lang: this.CURRENT_LANG
    };

    this.bind_comment_to_transaction();
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

    this.ajax.post("V_Pages/export", params).subscribe(async r => {
      loading.dismiss();
      
      if (r.message) {
        const toast = await this.toastController.create({
          message: r.message,
          duration: 4000
        });
        toast.present();
        
      }

    })
  }

  @ViewChild(InfiniteScroll) infiniteScroll: InfiniteScroll;


  update_partner_comments() {
    let params = {
      user: this.USER,
      page: this.partner_comments_page
    };

    this.ajax.post("V_Pages/get_comments", params).subscribe(r => {
      this.partner_comments = this.partner_comments.concat(r.partner_comments);
      this.infiniteScroll.complete();
    });

  }

  load_partner_comments() {
    this.partner_comments_page++;
    this.update_partner_comments();
    
  }


  // load_partner_comments() {
  //   this.partner_comments_page++;
  //   this.update_partner_comments();
    

  // }
  // update_partner_comments() {
  //   let params = {
  //     user: this.USER,
  //     page: this.partner_comments_page
  //   };
   
  //   this.ajax.post("V_Pages/get_comments", params).subscribe(r => {
  //     this.partner_comments = this.partner_comments.concat(r.partner_comments);
     
  //   });

  // }

  

  update_customer_transactions() {
    let type = " 'in' , 'out' ";
    if (this.CARD && this.CARD.type == 'gift') type = " 'out' ";

    let params = {
      user: this.USER,
      page: this.transactions_page,
      type: type
    };

    this.ajax.post("V_Pages/get_transactions", params).subscribe(r => {
      this.FULL_TRANSACTIONS = this.FULL_TRANSACTIONS.concat(r.transactions)
      this.split_transactions(this.FULL_TRANSACTIONS);
      this.infiniteScroll.complete();
    })

  }

  load_customer_transactions(event) {
    this.transactions_page++;
    this.update_customer_transactions();

  }

  update_partner_transactions() {

    let params = {
      user: this.USER,
      page: this.transactions_page,
    };

    this.ajax.post("V_Pages/get_transactions", params).subscribe(r => {
      this.FULL_TRANSACTIONS = this.FULL_TRANSACTIONS.concat(r.transactions)

      this.dataSource = this.FULL_TRANSACTIONS.map(item => {

        var sale, paid_money, paid_points, all_points;

        if (item.type == 'out') {

          sale = '---';
          paid_money = '---';
          paid_points = item.money;

          all_points = 0;

        } else {

          // sale = 100 * (item.client_points + item.admin_points + item.agent_points + item.invite_points) / item.money;
          paid_money = item.money - this.USER.discount * item.money / 100;
          sale = item.money - paid_money
          paid_points = '---';

          all_points = +item.client_points + +item.admin_points + +item.agent_points + +item.invite_points;
        }

        return {
          date: item.date,
          card_number: item.card_number.replace(/_/g, '-'),
          price: item.money,
          sale: sale,
          paid_money: paid_money,
          paid_points: paid_points,
          type: item.type,
          paid_partner: item.paid_partner,
          paid_admin: item.paid_admin,
          all_points: all_points
        }
      });

      this.displayedSource = this.dataSource;
      //this.infiniteScroll.complete();

    })
  }

  load_partner_transactions(event) {
    this.transactions_page++;
    this.update_partner_transactions();
  }

  update_transactions_data() {
    this.FULL_TRANSACTIONS = [];
    this.transactions_page = 0;
    
    this.OUTS = null;
    this.INS = null;

    this._pagesService.get_page_content({ page: 'my_account', user: this.USER })
      .subscribe(r => {
        if (r.card) {
          r.card.cardNumber = r.card.cardNumber.replace(/_/g, ' ');
          r.card.unit = ! r.card.unit ? 0 : parseFloat(r.card.unit).toFixed(1);
          this.CARD = r.card;
        }

        

        if (r.user) {
          this.USER.rating = r.user.rating;
        }

        if (r.all_comments_count) {
          this.ALL_COMMENTS_COUNT = this.USER.comments_count;
        }

        if (r.inactive_money) {
          this.INACTIVE_MONEY = r.inactive_money;
        }

      })


  }

  split_transactions(arr) {

    let OUTS = [];
    let INS = [];

    for (let key in arr) {
      let item = arr[key];
      if (item.type == 'out') {
        OUTS.push(item);
      } else if (item.type == 'in') {
        INS.push(item);
      }
    }

    this.INS = INS;
    this.OUTS = OUTS;

  }
  doRefresh(event) {
    setTimeout(() => {
      this.update_transactions_data();
      this.load_partner_comments();
      event.target.complete();
    }, 1000);
  }
  

  subscribe_to_changes() {
    this.router.events.subscribe((val) => {
      if (val instanceof NavigationEnd && val.url.includes('account:')) {
        this.update_transactions_data();
        this.access_gained = false;
      }
    });
  }

}
