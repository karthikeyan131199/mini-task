<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Low-Stock Threshold
    |--------------------------------------------------------------------------
    |
    | Products with stock_on_hand less than or equal to this threshold
    | will be reported as low stock.
    |
    */
    'low_stock_threshold' => env('LOW_STOCK_THRESHOLD', 10),
];
