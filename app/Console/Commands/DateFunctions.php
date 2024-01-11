<?php

use Carbon\Carbon;

function getCurrentDate() {
    return Carbon::now()->format('Y-m-d');
    // return '2023-11-22';
}
