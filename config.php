<?php

return array(
    /**
     * The amount of time cache should last
     *
     * @see http://www.php.net/manual/en/dateinterval.construct.php
     */
    'cacheDuration' => 'PT10M',
    'cacheDurationSeconds' => 600,

    /**
     * Whether request to APIs should be cached or not
     */
    'enableCache' => true,
);
