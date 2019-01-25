import { Component, OnInit, ViewChild, HostListener } from '@angular/core';
import { PagesService } from "../services/pages.service";
import { Router, ActivatedRoute } from '@angular/router';
import { Storage } from "@ionic/storage";
import { Geolocation } from '@ionic-native/geolocation/ngx';
import { GLOBALS } from '../app-globals';
import { Platform, ToastController, LoadingController } from '@ionic/angular';
import { LaunchNavigator } from '@ionic-native/launch-navigator/ngx';
import { getAllRouteGuards } from '@angular/router/src/utils/preactivation';
declare var $:any;
@Component({
  selector: 'app-map',
  templateUrl: './map.page.html',
  styleUrls: ['./map.page.scss'],
})
export class MapPage implements OnInit {
  STOP_MARKER: any;
  START_MARKER: any;

  constructor(
    private platform: Platform,
    private _pageService: PagesService,
    private launchNavigator: LaunchNavigator,
   // private launchNavigatorOptions : LaunchNavigatorOptions,
    private router: Router,
    private route: ActivatedRoute,
    private storage: Storage,
    private geolocation: Geolocation,
    public toastController: ToastController,
    public loadingController: LoadingController
  ) { }

  @ViewChild("map") mapElement;
  map: any;
  
  ID: number;
  ACTIVE_ADDRESS_ID: number;
  COMPANY;
  CURRENT_LANG: string;
  YEREVAN = { lat: 40.1785714, lng: 44.4989469 };
  MARKERS = [];
  directionsDisplay;
  MY_POSITION;
  

  async ngOnInit() {

    GLOBALS.activate_gps();
    this.get_my_position();

    this.route.params.subscribe(params => {
      this.ID = params['id'];
      this.ACTIVE_ADDRESS_ID = params['address_id'];
      this.storage.get("lang").then(val => {
        this.CURRENT_LANG = val;
      });

      this.initMap();

    });
  }

  

  redirect_back() {
    this.router.navigateByUrl(`/profile/pages/(other:inner-company/${this.ID})`);
  }

  async get_my_position(callback = null) {

    if(this.MY_POSITION){
      
      if(callback) return callback();
      return;
    }

    this.geolocation.getCurrentPosition().then((resp) => {
      this.MY_POSITION = { lat: resp.coords.latitude, lng: resp.coords.longitude };

      if (callback) callback();
     

    }, async err => {

      if(this.CURRENT_LANG == 'arm'){
        var geolocation_not_found = 'Կորդինատները բացակայում են';
      }else if (this.CURRENT_LANG == 'rus'){
        var geolocation_not_found = 'Координаты отсутствуют';
      }else{
        var geolocation_not_found = 'The Coordinates are missing';
  
      }
      const toast = await this.toastController.create({
        


        message: geolocation_not_found,
        duration: 4000,
        position: "top"
      });

      if (callback) callback();

      return toast.present();
    });
  }

  async get_distance(p1, p2, additional_data) {
    var self = this;

    var origin1 = new google.maps.LatLng(p1.lat, p1.lng);
    var destinationB = new google.maps.LatLng(p2.lat, p2.lng);
    var service = new google.maps.DistanceMatrixService();

    if (this.directionsDisplay != null) {
      this.directionsDisplay.setMap(null);
      this.directionsDisplay = null;
    }
    var directionsService = new google.maps.DirectionsService;
    this.directionsDisplay = new google.maps.DirectionsRenderer({
      map: this.map
    });


    this.directionsDisplay.setMap(this.map);
    this.directionsDisplay.setOptions({ suppressMarkers: true });

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

    directionsService.route(<any>{
      origin: origin1,
      destination: destinationB,
      travelMode: 'DRIVING'
    }, function (response, status) {

      service.getDistanceMatrix(<any>{
        origins: [origin1],
        destinations: [destinationB],
        travelMode: 'DRIVING',
        avoidHighways: false,
        avoidTolls: false,
      }, r => {
        loading.dismiss();
        if (<any>status == 'OK') {
          self.directionsDisplay.setDirections(response);
          self.START_MARKER = new google.maps.Marker({
           
            position: origin1,
            map: self.map,
          });
          


          self.STOP_MARKER = new google.maps.Marker({
            icon: {
              url: 'https://vilmar.am/vilmar_app/images/map-icon.svg',
           },
            position: {
              lat: p2.lat,
              lng: p2.lng,
            },
            map: self.map,
          });
          

          if(this.current_lang == 'arm'){
            var distance1 = 'Հեռ. ';
          }else if (this.current_lang == 'rus'){
            var distance1 = 'Расстояние ';
          }else{
            var distance1 = 'Distance ';
      
          }

          additional_data.text += distance1  +  r['rows'][0]['elements'][0]['distance']['text']
          additional_data.window.setContent(additional_data.text);
          additional_data.window.open(self.map, self.STOP_MARKER);

        } else {
          window.alert('Directions request failed due to ' + status);
        }
        
      });
     

    });
  }

  initMap() {
    this._pageService.get_page_content({ page: 'map', id: this.ID })
      .subscribe(r => {

        if (r.company) {
          this.COMPANY = r.company;

          let mapOptions: google.maps.MapOptions = {
            zoom: 16,
            mapTypeId: google.maps.MapTypeId.ROADMAP
          }

          this.map = new google.maps.Map(this.mapElement.nativeElement, mapOptions)

          var active_index = this.update_markers();
          if (this.COMPANY.length)
            this.map.setCenter(new google.maps.LatLng(this.COMPANY[active_index].lat, this.COMPANY[active_index].lng));
          else
            this.map.setCenter(new google.maps.LatLng(this.YEREVAN.lat, this.YEREVAN.lng));
        }
        // this.navigation();
        

      })
      

    if (GLOBALS.loading)
      GLOBALS.loading.dismiss();
  }

  

  @HostListener('window:keyup', ['$event'])
  keyEvent(e: KeyboardEvent) {
    if (e.keyCode == 13) {
      this.clear_markers();
    }

  }

  clear_markers() {
    if (this.directionsDisplay != null) {
      this.directionsDisplay.setMap(null);
      this.directionsDisplay = null;
    }
    if (this.START_MARKER)
      this.START_MARKER.setMap(null);

    if (this.STOP_MARKER)
      this.STOP_MARKER.setMap(null);

    for (var i = 0; i < this.MARKERS.length; i++) {
      this.MARKERS[i].setMap(null);
    }
    this.MARKERS.length = 0;
  }

  update_markers(): number {
    
    this.clear_markers();

    var infowindow = new google.maps.InfoWindow();
    var self = this;
    var active_index = 0;
    this.COMPANY.forEach((item, i) => {

      let marker: google.maps.Marker = new google.maps.Marker({
        
        map: this.map,
        icon: {
          url: 'https://vilmar.am/vilmar_app/images/map-icon.svg',
       },
        // animation: google.maps.Animation.DROP,
        position: new google.maps.LatLng(item.lat, item.lng),
        zIndex: 2
        
      });

      

      

      if(item.id == this.ACTIVE_ADDRESS_ID) {
        active_index = i;
        //marker.setAnimation(google.maps.Animation.BOUNCE);
      }

      this.MARKERS.push(marker);
      
     

      google.maps.event.addListener(marker, 'click', function () {
        self.on_marker_click(marker, infowindow, item, self)
        $(".whmap").attr("disabled", false);

        $(".whmap").data("lat",item.lat);
        $(".whmap").data("lng",item.lng);

        


      });

    })
    
   
    return active_index;
    
  }
  


  async on_marker_click(marker, infowindow, item, self) {



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

    self.update_markers();

    if (marker.getAnimation() !== null) {
      marker.setAnimation(null);
    }

    var style = 'color: #1a1a1a;font-size: 18px;background-color:transparent; text-align:center';
    var text = `<h1 style='${style}'>` + item.address + "</h1>";



    self.get_my_position(function () {
     
      loading.dismiss();

      

      if (infowindow) {
        infowindow.close();
      }


      if (self.MY_POSITION) {
        self.get_distance({
          lat: self.MY_POSITION.lat,
          lng: self.MY_POSITION.lng
        },
          {
            lat: marker.getPosition().lat(),
            lng: marker.getPosition().lng()
          },
          {
            window: infowindow,
            text: text,
            marker: marker
          });

          


      } else {
        infowindow.setContent(text);
        infowindow.open(self.map, marker);
      }
          
    })

  }
navigation(){
  if (this.platform.is('android')){
        this.launchNavigator.navigate([$(".whmap").data("lat"),$(".whmap").data("lng")], {
          start: ""+this.MY_POSITION.lat+","+this.MY_POSITION.lng+""
         });
    }else {
        var app;
        app = this.launchNavigator.APP.USER_SELECT;
        this.launchNavigator.navigate([$(".whmap").data("lat"),$(".whmap").data("lng")], {
          app : app,
          start: ""+this.MY_POSITION.lat+","+this.MY_POSITION.lng+""
         });
      }
  }
   
  
}

