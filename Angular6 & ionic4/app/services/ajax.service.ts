import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Service } from '../services/Service';

@Injectable({
  providedIn: 'root'
})
export class AjaxService extends Service {

  constructor(
    private http: HttpClient
  ) {
    super();
  }

  post(url: string, data: object): any {

    var url = this.host + url;

    return this.http.post(url, data);
  }

}
