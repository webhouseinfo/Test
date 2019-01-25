import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Service } from '../services/Service';

@Injectable({
  providedIn: 'root'
})

export class PagesService extends Service {

  constructor(
    private http: HttpClient
  ) {
    super();
  }

  get_page_content(data): any {

    var url = this.host + "V_Pages/get_page_content";

    return this.http.post(url, data);

  }
}
