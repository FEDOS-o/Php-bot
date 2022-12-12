<?php

namespace App\Credentials;

class FilmRequest
{
    public $count;
    public $min_years;
    public $max_years;

    function __construct($count, $min_years, $max_years) {
        $this->count = $count;
        $this->min_years = $min_years;
        $this->max_years = $max_years;
    }
}