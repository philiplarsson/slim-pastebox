<?php

use App\Controllers\PasteController;

$app->post('/paste', PasteController::class . ':create');
