import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Service } from '../services/Service';

@Injectable()
export class UsersService extends Service {

    constructor(
        private http: HttpClient
    ) {
        super();
    }

    insert_customer(data): any {
        
        var url = this.host + "V_Users/register_customer";

        return this.http.post(url, data);
    }

    insert_partner(data): any {
        
        var url = this.host + "V_Users/register_partner";

        return this.http.post(url, data);
    }

    login_attempt(data): any {

        var url = this.host + "V_Users/login_attempt";

        return this.http.post(url, data);
    }

    send_comment(data): any {

        var url = this.host + "V_Users/add_comment";

        return this.http.post(url, data);

    }
}