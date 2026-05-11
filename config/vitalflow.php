<?php

return [

    'api_throttle_per_minute' => (int) env('API_THROTTLE_PER_MINUTE', 120),

    'auth_throttle_per_minute' => (int) env('AUTH_THROTTLE_PER_MINUTE', 10),

];
