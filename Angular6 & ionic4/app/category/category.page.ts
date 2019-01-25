import { Component, OnInit } from '@angular/core';
import { Router,ActivatedRoute } from '@angular/router';
import { PagesService } from "../services/pages.service";
import { Storage } from "@ionic/storage";

import { Geolocation } from '@ionic-native/geolocation/ngx';
import { GLOBALS } from "../app-globals";
import { ToastController, LoadingController } from '@ionic/angular';
declare var $:any;
@Component({
  selector: 'app-category',
  templateUrl: './category.page.html',
  styleUrls: ['./category.page.scss'],
})
export class CategoryPage implements OnInit {

  ID: number;
  companies = null;
  category;
  CURRENT_LANG:string;
  HOST: string = GLOBALS['HOST'];
  DID_ORDER = false;
  my_pos = {lat : 0, lng: 0};

  constructor(
    private router: Router,
    private route:ActivatedRoute,
    private _pagesService: PagesService,
    private storage: Storage,
    private geolocation: Geolocation,
    private toastController: ToastController,
    private loadingController: LoadingController

  ) {
    GLOBALS.activate_gps();
   }

  ngOnInit() {
   
    const self = this;
    this.route.params.subscribe( params =>{
      this.ID = params['id'];

      this.storage.get("lang").then(val => {
        this.CURRENT_LANG = val;
      });

      let location_watch = setInterval(function(){
        console.log('try get coordinates')

        self.geolocation.getCurrentPosition().then((resp) => {
          self.my_pos = { lat: resp.coords.latitude, lng: resp.coords.longitude };
          clearInterval(location_watch);
        })

      }, 1500);
     
    });

    this.update_page_content();
  }

  redirect_to_user(item: any){
  
      var url = window.location.href;
      $(".home").val(url);
    
    this.router.navigateByUrl('/profile/pages/(other:inner-company/' + item.id + ')');
  }

  async order_companies(){
    if(this.DID_ORDER) return;
    if(this.my_pos.lat <= 0 || this.my_pos.lng <= 0){

      if(this.CURRENT_LANG == 'arm'){
        var wait_for_position = 'Խնդրում ենք սպասել մինչ կհաշվարկվի Ձեր տեղանքը';
      }else if (this.CURRENT_LANG == 'rus'){
        var wait_for_position = 'Пожалуйста, подождите, пока ваше местоположение будет рассчитано';
      }else{
        var wait_for_position = 'Please wait until your location is calculated';
  
      }

      const toast = await this.toastController.create({
        message: wait_for_position,
        duration: 4000
      });
      toast.present();
      return;
    }

    let tmp = await this.prepare_companies();
    this.companies = tmp.sort((a,b) => a.address.distance < b.address.distance ? -1 : 1);
    this.DID_ORDER = true;

  }

  update_page_content(){
    
    this._pagesService.get_page_content({ page: 'category', category_id: this.ID })
      .subscribe(r => {

        if(r.companies){
          this.companies = Object["values"](r.companies).reverse();
          
        } 

        if(r.category) this.category = r.category;
      })
  }

  async prepare_companies(){
    let array = this.companies;
    let my_pos = this.my_pos;

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

    for (let i = 0; i < array.length; i++) {
      let item = array[i];
      var min = { distance: Infinity, addr: array[0].address };

      for (let address of item.address) {
        var current_distance;
        if(my_pos){
          current_distance = this.distance(address.lat, address.lng, my_pos.lat, my_pos.lng);
        }
        else{
          current_distance = -1;
          min.addr = address
          min.distance = current_distance;
          break;
        }
        if (current_distance < min.distance) {
          min.addr = address;
          min.distance = current_distance;
        }
      }
      min.addr.distance = parseFloat(min.distance.toFixed(2));
      array[i].address = min.addr;
    }
    loading.dismiss();
    return(array);
  }

  distance(lat1: number, lon1: number, lat2: number, lon2: number) {
    var p = 0.017453292519943295;    // Math.PI / 180
    var c = Math.cos;
    var a = 0.5 - c((lat2 - lat1) * p) / 2 +
      c(lat1 * p) * c(lat2 * p) *
      (1 - c((lon2 - lon1) * p)) / 2;

    return 12742 * Math.asin(Math.sqrt(a)); // 2 * R; R = 6371 km
  }




}
