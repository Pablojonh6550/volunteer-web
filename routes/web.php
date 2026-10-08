<?php

use Illuminate\Support\Facades\Route;

/*
| O frontend é um SPA Vue 3 (resources/js). Toda rota que não pertence à API
| devolve o mesmo shell HTML e o Vue Router assume a navegação no navegador.
*/
Route::view('/{any?}', 'app')
    ->where('any', '^(?!api|sanctum|storage|up).*$')
    ->name('spa');
