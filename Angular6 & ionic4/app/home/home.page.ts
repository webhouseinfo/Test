import { Component, OnInit, ViewChild } from '@angular/core';
import { PagesService } from "../services/pages.service";
import { Storage } from "@ionic/storage";
import { AjaxService } from "../services/ajax.service";
import { ToastController, Item } from "@ionic/angular";
import { Router, NavigationEnd } from '@angular/router';
import { LoadingController } from "@ionic/angular";


import { BarcodeScanner } from '@ionic-native/barcode-scanner/ngx';
//import { Geolocation } from '@ionic-native/geolocation/ngx';

import { GLOBALS } from "../app-globals";
declare var $:any;

export interface INCOME {
    name: string;
    money: number;
 
}

@Component({
    selector: 'app-home',
    templateUrl: './home.page.html',
    
    styleUrls: ['./home.page.scss'],
    providers: [PagesService]
    
})



export class HomePage implements OnInit {

    
        
  

    USER;
    current_lang = 'arm';
    TRANSACTION_CARD = null;
    SLIDER;

    HOST: string = GLOBALS['HOST'];

    AGENT_TOTAL_INCOME = 0;
    AGENT_INCOME_COLUMNS: string[] = ['name', 'money'];
    AGENT_INCOME = [];

    

    // slideOpts = {
    //     effect: 'flip',
    //     autoplay: {
    //         delay: 6000

    //     },
    //     loop: true
    // };

    catslideOpts = {
        slidesPerView: 5,
        slidesPerGroup: 1,
        slidesPerColumn: 1,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false
        },
        loop: true

    }

    announce_companies = [];
    categories = [];
    my_pos: { lat: number; lng: number; };
  

    constructor(
        private _pagesService: PagesService,
        private storage: Storage,
        private ajax: AjaxService,
        private toastController: ToastController,
        private router: Router,
        public loadingController: LoadingController,
        private barcodeScanner: BarcodeScanner,
        //private geolocation: Geolocation
    ) {
        GLOBALS.activate_gps();

    }
    ngAfterContentInit() {
        if (GLOBALS.loading)
            GLOBALS.loading.dismiss();


    }

   

    subscribe_to_changes() {
        this.router.events.subscribe((val) => {
            if (val instanceof NavigationEnd && val.url.includes('home:home')) {

                var get_lang = this.storage.get("lang");

                Promise.all([get_lang]).then(values => {
                    this.current_lang = values[0];
                    this.update_page_content();
                });
                
            }
        });

    }
    
    
    ngOnInit() {
        var get_user = this.storage.get('logged_user');
        var get_lang = this.storage.get("lang");

        Promise.all([get_user, get_lang]).then(values => {
            this.USER = values[0];
            this.current_lang = values[1];
            this.update_page_content();
            
            
        })
        $(document).ready(function(){
           
            var b = setInterval(function(){
                if($(".owl-carousel").length ){
                  
                    $(".owl-carousel").owlCarousel({
                      items:1,
                      loop:true,
                      autoplay:true,
                      autoplayTimeout:3000,
                      dots:true,
                      dotsEach:true
                    });
                   
                    
                    clearInterval(b);
                }
               
              }, 100);
            
        })
        

        
            
          
       
        
     this.subscribe_to_changes();

    }
    
    

    calcHeight() {
        setTimeout(() => {
            let slider1 = document.getElementById("ion_slides_top").clientHeight;
            let slider2 = document.getElementById("ion_slides_bottom").clientHeight;
            let ion_content1 = document.getElementById("ion_content").clientHeight;
            let fullHeight = ion_content1 - slider1 - slider2 - 56;
            (document.getElementsByClassName("mat-tab-body-wrapper")[0] as any).style.height = fullHeight + 'px';
        }, 600)

    }

    update_page_content() {

        const self = this;
        this._pagesService.get_page_content({ page: 'home', user: this.USER })
            .subscribe(r => {
                
                if (r.categories) {
                    this.categories = r.categories;
                }

                if (r.companies) {
                    this.announce_companies = Object["values"](r.companies).reverse();
                    // Start Calculate Tabs Height
                    setTimeout(function(){
                        self.calcHeight();
                    }, 600)
                    

                }
                // alert(this.USER.cashBack);
                // if(this.USER['cashBack'] == ''){

                // };
                
               

                if (r.agent_income) {
                    let arr = [];
                    this.AGENT_TOTAL_INCOME = 0;
                    for (let item in r.agent_income) {
                        let tmp = r.agent_income[item];
                        this.AGENT_TOTAL_INCOME += parseFloat(tmp.agent_points);
                        arr.push({
                            name: tmp.armName || tmp.rusName || tmp.engName,
                            money: tmp.agent_points
                        })
                    }
                    this.AGENT_INCOME = arr;
                }

                if (r.slider) {
                    this.SLIDER = r.slider;
                }

               
            })


    }
    
    


    // CUSTOMER 
    redirect_to_categorie(id: number) {
        this.router.navigateByUrl(`/profile/pages/(other:category/${id})`);
    }

    redirect_to_company(id: number) {
        var url = window.location.href;
        $(".home").val(url);
        this.router.navigateByUrl(`/profile/pages/(other:inner-company/${id})`);
    }

    // construct_nearest(array) {
    // const self = this;

    // return new Promise((callback) => {
    // this.geolocation.getCurrentPosition().then((resp) => {
    // self.my_pos = { lat: resp.coords.latitude, lng: resp.coords.longitude };
    // callback(this.prepare_companies(array))

    // }).catch((error) => {
    // callback(this.prepare_companies(array))
    // });
    // });

    //}

    // prepare_companies(array) {
    // for (let i = 0; i < array.length; i++) {
    // let item = array[i];
    // var min = { distance: Infinity, addr: array[0].address };

    // for (let address of item.address) {
    // var current_distance;
    // if (this.my_pos) {
    // current_distance = this.distance(address.lat, address.lng, this.my_pos.lat, this.my_pos.lng);
    // }
    // else {
    // current_distance = -1;
    // min.addr = address
    // min.distance = current_distance;
    // break;
    // }
    // if (current_distance < min.distance) {
    // min.addr = address;
    // min.distance = current_distance;
    // }
    // }
    // min.addr.distance = parseFloat(min.distance.toFixed(2));
    // array[i].address = min.addr;
    // }
    // return (array);
    // }

    // distance(lat1: number, lon1: number, lat2: number, lon2: number) {
    // var p = 0.017453292519943295; // Math.PI / 180
    // var c = Math.cos;
    // var a = 0.5 - c((lat2 - lat1) * p) / 2 +
    // c(lat1 * p) * c(lat2 * p) *
    // (1 - c((lon2 - lon1) * p)) / 2;

    // return 12742 * Math.asin(Math.sqrt(a)); // 2 * R; R = 6371 km
    // }

    // companies_by_distance() {
    // return this.announce_companies.sort((a, b) => a.address.distance > b.address.distance ? 1 : -1);
    // }
     
    companies_by_sale() {
        return this.announce_companies.sort((a, b) => +a.discount > +b.discount ? -1 : 1);
        
    }
    companies_by_sale_first(x) {
        if(x == 5){
           
           return this.announce_companies.sort((a, b) => +a.id > +b.id ? -1 : 1);
        }
   
       
   }

   

    // -----------------------------------

    // PARTNER PART
    // scann() {

    //     this.barcodeScanner.scan().then(barcodeData => {
    //         if(this.current_lang == 'arm'){
    //             var wrong_qr = 'Սխալ QR !!!';
    //           }else if (this.current_lang == 'rus'){
    //             var wrong_qr = 'Неверный QR !!!';
    //           }else{
    //             var wrong_qr = 'Invalid QR !!!';
          
    //           }
    //         if (!barcodeData.text) return alert(wrong_qr);
    //         this.validate_scanning(barcodeData.text);
    //     }).catch(err => {
    //         alert(JSON.stringify(err));
    //     });
    // }
    scann() {
        let card_number = prompt("Enter Card number");
    
        if (card_number.trim().length) {
    
          this.ajax.post("V_Transactions/check_card", {current_lang: this.current_lang, card_number: card_number}).subscribe(async r => {
    
            if(r.status == 'ok'){
    
              this.TRANSACTION_CARD = r.card;
    
            }else{
    
              const toast = await this.toastController.create({
                message: r.message,
                duration: 4000
              });
              toast.present();
    
            }
    
          })
        }else{
          alert("Invalid card Number");
        }
      }

    validate_scanning(card_number) {
       
        
        if (card_number.trim().length) {

            this.ajax.post("V_Transactions/check_card", { current_lang: this.current_lang, card_number: card_number }).subscribe(async r => {

                if (r.status == 'ok') {

                    this.TRANSACTION_CARD = r.card;

                } else {

                    const toast = await this.toastController.create({
                        message: r.message,
                        duration: 4000
                    });
                    toast.present();

                }

            })
        } else {
            if(this.current_lang == 'arm'){
                var invalid_card = 'Սխալ քարտի համար';
              }else if (this.current_lang == 'rus'){
                var invalid_card = 'Неверная карта';
              }else{
                var invalid_card = 'Invalid card';
          
              }
            alert(invalid_card);
        }
    }
    
       
    

    async transaction_IN_commit(money: number) {
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

        this.ajax.post("V_Transactions/cashin_submit", { current_lang: this.current_lang, user: this.USER, card: this.TRANSACTION_CARD, money: money }).subscribe(async r => {
            console.log(r);

            const toast = await this.toastController.create({
                message: r.message,
                duration: 4000
            });

            toast.present();
            loading.dismiss();
            this.cancel_transaction();

        })
    }
    

    async transaction_OUT_commit(money: string) {

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

        

        this.ajax.post("V_Transactions/cashout_submit", { current_lang: this.current_lang, user: this.USER, card: this.TRANSACTION_CARD, money: money }).subscribe(async r => {

            const toast = await this.toastController.create({
                message: r.message,
                duration: 4000
            });
            toast.present();
            loading.dismiss();
            this.cancel_transaction();
        })
    }

    cancel_transaction() {
        this.TRANSACTION_CARD = null;
    }
    // -----------------------------------


    // GLOBAL FUNCTIONS
    isNumber(n: any) {
        return !isNaN(parseFloat(n)) && isFinite(n);
    }
}