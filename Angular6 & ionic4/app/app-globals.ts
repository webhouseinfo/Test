import { sanitizeSrcset } from "@angular/core/src/sanitization/url_sanitizer";
import { LocationAccuracy } from '@ionic-native/location-accuracy/ngx';

const locationAccuracy: LocationAccuracy = new LocationAccuracy();

export const GLOBALS = {
    loading: null,
    dict: {},
    routes: {current: '/home', previous: ["/home"]},
    HOST: "https://vilmar.am/vilmar_app",
    EMAIL_VALIDATION_PATTERN: /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
    validate_email: function (email) {
        return this.EMAIL_VALIDATION_PATTERN.test(String(email).toLowerCase());
    },
    activate_gps: function () {
        locationAccuracy.canRequest().then((canRequest: boolean) => {
            if (canRequest) {
                // the accuracy option will be ignored by iOS
                locationAccuracy.request(locationAccuracy.REQUEST_PRIORITY_HIGH_ACCURACY).then(
                    () => console.log('Request successful'),
                    error => console.log('Error requesting location permissions', error)
                );
            }
        });
    }
};