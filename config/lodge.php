<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Lodge Registration Options
    |--------------------------------------------------------------------------
    |
    | Fixed option lists used both to validate the Lodge self-registration
    | form and to feed the equivalent dropdowns in the mobile app, via the
    | GET /api/v1/public/lodge-registration-options endpoint. Single source
    | of truth: update here to change what the form accepts/shows.
    |
    */

    'potencias' => ['GOSP', 'GOB', 'GLESP', 'GOP'],

    'ritos' => ['REAA', 'Rito de York', 'Rito Adonhiramita', 'Rito Brasileiro'],

    'tipos' => ['Loja Simbólica'],

    'graus' => ['Aprendiz', 'Companheiro', 'Mestre', 'Venerável'],

];
