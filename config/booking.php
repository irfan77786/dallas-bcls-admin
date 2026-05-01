<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Reservation PDF & email branding
    |--------------------------------------------------------------------------
    |
    | Same URL used in booking confirmation emails and the DomPDF reservation
    | PDF. Optionally override with BOOKING_RESERVATION_LOGO_URL in .env.
    | Copy the file under public/img/... for offline/fast PDF renders.
    |
    */

    'reservation_logo_url' => env(
        'BOOKING_RESERVATION_LOGO_URL',
        'https://dallasblackcarslimoservice.com/img/black-car-service-dallas-logo.PNG'
    ),

];
