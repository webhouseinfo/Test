/// <reference types="@types/googlemaps" />
import { Component, OnInit, ViewChild, HostListener, ElementRef, NgZone } from "@angular/core";
import { MapsAPILoader } from '@agm/core';
import {
  FormBuilder,
  FormGroup,
  Validators,
  FormControl,
  FormArray
} from "@angular/forms";
import { UsersService } from "../services/UsersService";
import { ToastController, AlertController, LoadingController } from "@ionic/angular";
import { Storage } from "@ionic/storage";
import { PageService } from "../services/PageService";
import { TranslateService } from '@ngx-translate/core';
import { MAT_STEPPER_GLOBAL_OPTIONS } from "@angular/cdk/stepper";
import { Router } from "@angular/router";
import {GLOBALS} from '../app-globals'

@Component({
  selector: "app-signup",
  templateUrl: "./signup.page.html",
  styleUrls: ["./signup.page.scss"],
  providers: [
    UsersService, PageService,
    {
      provide: MAT_STEPPER_GLOBAL_OPTIONS, useValue: { displayDefaultIndicatorType: false }
    }]
})



export class SignupPage implements OnInit {

  constructor(
    private _formBuilder: FormBuilder,
    private _usersService: UsersService,
    private toastController: ToastController,
    public loadingController: LoadingController,
    private storage: Storage,
    private mapsAPILoader: MapsAPILoader,
    private ngZone: NgZone,
    public alertController: AlertController,
    private pageService: PageService,
    private translate: TranslateService,
    private router: Router,
  ) { }



  @ViewChild("search")
  public searchElementRef: ElementRef;

  // @HostListener('window:keyup', ['$event'])
  // keyEvent(e: KeyboardEvent) {
  //   if (e.keyCode == 13){}
  //     this.save_customer();
  // }
  maxDate = new Date((new Date()).getFullYear() - 16, 0, 1);

  TERMS: string;
  searchControl: FormControl;

  customer_form: FormGroup;
  partner_form: FormGroup;

  customer_has_card: boolean;
  customer_is_invited: boolean;

  step_1: FormGroup;
  step_2: FormGroup;
  step_3: FormGroup;

  places: any = [];

  partner_files = [];
  partner_logo = null;

  loading: boolean = false;
  current_lang: string = "arm";

  COUNTRIES = {
    arm: [],
    rus: [],
    eng: [],
    ge: []
  };

  AREAS = [];
  USERDATA = {
    'name': "",
    'surname': "",
    'address': "",
    'gender': "",
    'email': '',
    'birthday': ''
  };

  step_2_addresses(){
    return (<any>this.step_2.get('addresses')).controls
  }

  customer_validation_messages = {
    name: [
      { type: "required", message: GLOBALS.dict['name_is_required']},
    ],
    surname: [
      { type: "required", message: GLOBALS.dict['surname_is_required'] },
      {
        type: "maxlength",
        message: GLOBALS.dict['surname_max_symb']
      }
    ],
     date: [{ type: "required", message: GLOBALS.dict['birthday_required'] }],
    land: [{ type: "required", message: GLOBALS.dict['country_required'] }],
     address: [{ type: "required", message: GLOBALS.dict['address_required']}],
    phone_code: [
      { type: "required", message: GLOBALS.dict['code_required'] },
      { type: "pattern", message: GLOBALS.dict['enter_only_numbers'] }
    ],
    phone_number: [
       { type: "required", message: GLOBALS.dict['phone_is_required'] },
      { type: "pattern", message: GLOBALS.dict['enter_only_numbers'] }
    ],
    email: [
      { type: "required", message: GLOBALS.dict['emaiL_required'] },
      { type: "pattern", message: GLOBALS.dict['wrong_format'] }
    ],
    gender: [{ type: "required", message: GLOBALS.dict['gender_required'] }],
    terms: [{ type: "required", message: GLOBALS.dict['confirm_terms'] }]
  };

  step_1_validation_messages = {
    name: [
      { type: "required", message: GLOBALS.dict['companyNameReq'] },
    ],
    hvhh: [
      { type: "required", message: GLOBALS.dict['hvhh_required']},
      { type: "pattern", message: GLOBALS.dict['enter_only_numbers'] }
    ],
    type: [
      { type: "required", message: GLOBALS.dict['company_type_required'] },
    ],
    law_address: [
      { type: "required", message: GLOBALS.dict['legal_address_required'] },
    ]
  };

  step_2_validation_messages = {
    email: [
      { type: "required", message: GLOBALS.dict['email_required']},
      { type: "pattern", message: GLOBALS.dict['wrong_format'] }
    ],
    phone_code: [
       { type: "required", message: GLOBALS.dict['code_required'] },
      { type: "pattern", message: GLOBALS.dict['enter_only_numbers'] }
    ],
    phone_number: [
       { type: "required", message: GLOBALS.dict['phone_is_required'] },
      { type: "pattern", message: GLOBALS.dict['enter_only_numbers'] }
    ]
    // ,
    // address: [
    //   { type: "required", message: GLOBALS.dict['address_required'] },
    // ]
  };

  step_3_validation_messages = {
    
  }

  async show_terms() {

    this.translate.get('close').subscribe(async text => {

      const alert = await this.alertController.create({
        message: this.TERMS[this.current_lang + "Text"],
        buttons: [text]
      });

      await alert.present();
    })

  }

  redirect_to_login(){
    this.router.navigateByUrl('/login')
  }

  init_map() {
    var self = this;

    this.searchControl = new FormControl();

    this.mapsAPILoader.load().then(() => {
      const autocomplete = new google.maps.places.Autocomplete(this.searchElementRef.nativeElement, {
        types: ["address"]
      });

      autocomplete.addListener("place_changed", () => {
        this.ngZone.run(() => {

          const place: google.maps.places.PlaceResult = autocomplete.getPlace();

          // place was not found
          if (place.geometry === undefined || place.geometry === null) {
            return;
          }

          self.add_address_field(place);
          self.searchControl.setValue("");
        });
      });
    });
  }

  add_address_field(place: google.maps.places.PlaceResult) {
    this.places.push(place);
    var ctrl = <FormArray>this.step_2.controls['addresses'];

    ctrl.push(this._formBuilder.group({
      address: [place.formatted_address, Validators.required]
    }));

  }

  ngOnInit() {
    if(GLOBALS.loading)
      GLOBALS['loading'].dismiss();

    this.init_map();

    var self = this;

    this.storage.get("lang").then(val => {
      this.current_lang = val;
    });




    this.pageService.get_page_content({ page: "signup" }).subscribe(r => {
      if (r.arm_countries) {
        this.COUNTRIES['arm'] = r.arm_countries
      }
      if (r.eng_countries) {
        this.COUNTRIES['eng'] = r.eng_countries
      }
      if (r.rus_countries) {
        this.COUNTRIES['rus'] = r.rus_countries
      }

      if (r.areas) {
        this.AREAS = r.areas;
      }

      if (r.terms) {
        this.TERMS = r.terms;
      }
    });

    this.storage.get('social_logged_user').then(val => {
      if(val)
      {
        this.USERDATA = val;
        this.customer_form.get('name').setValue(this.USERDATA.name);
        this.customer_form.get('surname').setValue(this.USERDATA.surname);
        this.customer_form.get('email').setValue(this.USERDATA.email);
      }
    })

      this.customer_form = this._formBuilder.group({
        name: [
          "",
          [Validators.required]
        ],
        surname: [
          "",
          [Validators.required]
        ],

        date: [""],
        land: ["", []],
        address: ["", []],
        phone_code: ["", [Validators.pattern(/^[.\d]+$/)]],
        phone_number: ["", [Validators.pattern(/^[.\d]+$/)]],
        email: ["", [Validators.required, Validators.pattern(GLOBALS.EMAIL_VALIDATION_PATTERN)]],
        invite_code: [""],
        card_number: [""],
        has_card: [""],
        is_invited: [""],
        gender: ['male', [Validators.required]],
        terms: ["", [Validators.required]]
      });




    this.step_1 = this._formBuilder.group({
      name: ["", Validators.required],
      hvhh: ["", [Validators.pattern(/^[.\d]+$/)]],
      type: ["", Validators.required],
       law_address: ["", []],
    });

    this.step_2 = this._formBuilder.group({
      email: ["", [Validators.required, Validators.required, Validators.pattern(GLOBALS.EMAIL_VALIDATION_PATTERN)]],
      phone_code: ["", [Validators.required, Validators.pattern(/^[.\d]+$/)]],
      phone_number: ["", [Validators.required, Validators.pattern(/^[.\d]+$/)]],
      addresses: this._formBuilder.array([])
    });

    this.step_3 = this._formBuilder.group({
      description: ["", Validators.required],
      terms: ["", Validators.required],
      image_1: [''],
      image_2: [''],
      image_3: [''],
      image_4: [''],
      image_5: [''],
      image_6: [''],
      logo: ['']
    });

  }

  has_card_toggle(e) {
    const input = <HTMLInputElement>event.target;
    this.customer_form.get('has_card').setValue(input.checked)
    if (this.customer_form.get("has_card").value)
      this.customer_form.controls["card_number"].setValidators(
        Validators.required
      );
    else this.customer_form.controls["card_number"].clearValidators();

    this.customer_form.controls["card_number"].markAsUntouched();
    this.customer_form.controls["card_number"].updateValueAndValidity();
  }


  remove_address_field(i: number) {
    var ctrl = <FormArray>this.step_2.controls['addresses'];
    ctrl.removeAt(i);
    this.places.splice(i, 1);

  }

  fileChange(elm) {
    var input = <HTMLInputElement>event.target;

    if (input.files.length > 0) {
      let file = input.files[0];
      let name = input.getAttribute('name');
      this.step_3.get(name).setValue(file);

      var reader = new FileReader();

      reader.onload = function (e: any) {
        elm.target.previousSibling.src = e.target.result
      }
      reader.readAsDataURL(input.files[0]);

    }

  }


  async save_partner() {
    var form = new FormData();

    Object.keys(this.step_1.controls).forEach(key => {
      form.append(key, this.step_1.get(key).value);
    });

    Object.keys(this.step_2.controls).forEach(key => {
      form.append(key, this.step_2.get(key).value);
    });

    Object.keys(this.step_3.controls).forEach(key => {
      form.append(key, this.step_3.get(key).value);
    });
    var places = [];

    this.places.forEach(function (item) {
      places.push({ name: item.formatted_address, lat: item.geometry.location.lat(), lng: item.geometry.location.lng() });
    })

    if(places.length < 1){
      const toast = await this.toastController.create({
        message: GLOBALS.dict['branch_address_required'],
        duration: 4000
      });
      return toast.present();
    }

    if(! (this.step_3.get('image_1').value || 
    this.step_3.get('image_2').value ||
    this.step_3.get('image_3').value ||
    this.step_3.get('image_4').value ||
    this.step_3.get('image_5').value ||
    this.step_3.get('image_6').value)
     ){
      const toast = await this.toastController.create({
        message: GLOBALS.dict['partner_image_required'],
        duration: 4000
      });
      return toast.present();
    }

    if(! this.step_3.get('logo').value){
      const toast = await this.toastController.create({
        message: GLOBALS.dict['logo_required'],
        duration: 4000
      });
      return toast.present();
    }

    if(! this.step_3.get('terms').value){
      const toast = await this.toastController.create({
        message: GLOBALS.dict['confirm_terms'],
        duration: 4000
      });
      return toast.present();
    }

    form.append('addresses', JSON.stringify(places));
    form.append('lang', this.current_lang);
    form.append('law_address', this.step_1.get('law_address').value);


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

    this._usersService.insert_partner(form).subscribe(
      async r => {
        loading.dismiss();

        if (r.status == "ok") {
          const toast = await this.toastController.create({
            message: r.message,
            duration: 4000
          });
          toast.present();
          this.router.navigateByUrl('/login');
        }
      },
      err => {
        loading.dismiss();
        console.log(err);
      }
    );

  }

  async save_customer() {
    var data = {
      name: this.customer_form.get("name").value,
      surname: this.customer_form.get("surname").value,
      date: this.customer_form.get("date").value,
      land: this.customer_form.get("land").value,
      address: this.customer_form.get("address").value,
      phone:
        this.customer_form.get("phone_code").value +
        this.customer_form.get("phone_number").value,
      email: this.customer_form.get("email").value,
      has_card: this.customer_form.get("has_card").value,
      card_number: this.customer_form.get("card_number").value,
      is_invited: this.customer_form.get("is_invited").value,
      invite_code: this.customer_form.get("invite_code").value,
      gender: this.customer_form.get("gender").value,
      lang: this.current_lang
    };

    
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

    this._usersService.insert_customer(data).subscribe(
      async r => {
        loading.dismiss();

        if (r.status == "ok") {
          if (r.status == "ok") {

            const toast = await this.toastController.create({
              message: r.message,
              duration: 4000
            });
            toast.present();

            this.router.navigateByUrl('/login');
          }
        }
      },
      err => {
        loading.dismiss();
        console.log(err);
      }
    );
  }
}
