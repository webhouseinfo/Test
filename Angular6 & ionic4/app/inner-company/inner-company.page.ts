import { Component, OnInit, ViewChild } from '@angular/core';
import { PagesService } from "../services/pages.service";
import { Storage } from "@ionic/storage";
import { AjaxService } from "../services/ajax.service";
import { ToastController, InfiniteScroll } from "@ionic/angular";
import { Router, ActivatedRoute } from '@angular/router';
import { LoadingController } from "@ionic/angular";
import { UsersService } from "../services/UsersService";
import { GLOBALS } from "../app-globals";
declare var $:any;

@Component({
  selector: 'app-inner-company',
  templateUrl: './inner-company.page.html',
  styleUrls: ['./inner-company.page.scss'],
  providers: [UsersService]

})
export class InnerCompanyPage implements OnInit {
 

  constructor(
    private _pagesService: PagesService,
    private storage: Storage,
    private ajax: AjaxService,
    private toastController: ToastController,
    private router: Router,
    public loadingController: LoadingController,
    //private geolocation: Geolocation,
    private route: ActivatedRoute,
    private _usersService: UsersService
  ) { 
    GLOBALS.activate_gps();
    
  }

  current_lang: string;
  USER;
  COMPANY;
  partner_comments_page1: number = 0;
  customer_comment = [];
  ID;
  HOST = GLOBALS['HOST'];
  COMMENT_BOX_VISIBLE = false;
  RESENT_COMMENTS = [];
  ALL_COMMENTS_COUNT = 0;
  my_pos = { lat: null, lng: null };

  slideOpts = {
    effect: 'flip',
    autoplay: {
      delay: 4000
    },
    loop: true
  };

  COMMENT = {
    'stars' : 0,
    'text' : '',
  };  

  ngOnInit() {


   

    var self = this;
    this.route.params.subscribe(params => {
      this.ID = params['id'];
      

    });

    this.storage.get('logged_user').then(value => {
      this.USER = value;
    });

    this.storage.get("lang").then(val => {
      this.current_lang = val;
    });
    
   
    this.get_page_content();
  }
  
  async barev() {
    var url = $(".home").val();
      if(url.indexOf("home:home") >= 0){
        this.router.navigateByUrl(`/profile/pages/(home:home)`);
      }else{
        if(url.indexOf("other:category") >= 0){
          var arr = url.split('/');
          var f = arr[arr.length -1].slice(arr[arr.length -1], -1)
         this.router.navigateByUrl(`/profile/pages/(other:category/${f})`);
        }
      }

}
    


  disable_comment_form(){
    this.COMMENT.stars = 0;
    this.COMMENT.text = '';
    this.COMMENT_BOX_VISIBLE = false;

  }
  
     
  async submit_comment(){
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

    this._usersService.send_comment({ comment: this.COMMENT, user: this.USER, lang: this.current_lang, partner_id: this.ID }).subscribe(async r => {
      if(r.status == 'ok'){
        this.COMPANY.comments_count++;
        this.rebuild_comments();
        this.update_partner_comments();
        this.disable_comment_form();
      }

      const toast = await this.toastController.create({
        message: r.message,
        duration: 4000
      });

      toast.present();
      loading.dismiss();

    })

  }
  rebuild_comments(): any {
    this._pagesService.get_page_content({ page: 'inner_company', id: this.ID })
      .subscribe(r => {
        if(r.comments){
          this.RESENT_COMMENTS = r.comments;
        }
    });
    this.partner_comments_page1 = 1;
  }



   @ViewChild(InfiniteScroll) infiniteScroll: InfiniteScroll;


  update_partner_comments() {
    let params = {
      id: this.ID,
      page: this.partner_comments_page1
    };
    this.ajax.post("V_Pages/get_comments1", params).subscribe(r => {
      this.RESENT_COMMENTS = this.RESENT_COMMENTS.concat(r.customer_comment);
      this.infiniteScroll.complete();
      
    });
    

  }

  doInfinite(infiniteScroll) {
    this.partner_comments_page1++;
    this.update_partner_comments();

  }

  
  async redirect_to_map(id: number, address_id: number) {
    
    if(this.current_lang == 'arm'){
      var please_wait = 'Խնդրում ենք սպասել';
    }else if (this.current_lang == 'rus'){
      var please_wait = 'Пожалуйста подождите';
    }else{
      var please_wait = 'Please Wait';

    }
    GLOBALS.loading = await this.loadingController.create({
      message: please_wait,
      translucent: true
    });

    await GLOBALS.loading.present();

    this.router.navigateByUrl(`/profile/pages/(other:company/${id}/${address_id})`);
    
  }
  


  get_page_content(): any {
    this._pagesService.get_page_content({ page: 'inner_company', id: this.ID , lang : this.current_lang })
      .subscribe(r => {
        

       

        if(r.comments){
          this.RESENT_COMMENTS = r.comments;
        }

        if(r.comments_count){
          this.ALL_COMMENTS_COUNT = r.comments_count;
        }

        if (r.company) {
         
          this.COMPANY = r.company;

         setTimeout(() => {
            $(".description")[0].innerHTML = r.company[this.current_lang + 'Description'];
           }, 400);

          try {
            if (this.COMPANY.images && this.COMPANY.images.length > 0) {
              this.COMPANY.images = JSON.parse(this.COMPANY.images.replace(/\'/gi, "\""));
            } else {
              this.COMPANY.images = [];
            }
            //a = r.company['Description']
   


          } catch (e) {
            this.COMPANY.images = [];
          }

        }
        
        

      })
  }
  
  
 
}
