<?php

namespace App\Credentials;

class Film
{
    public $name;
    public $film_link;
    public $poster_link;
    public $info;
    public $genres;
    public $actors;
    public $rating;
    public $description;

    function __construct($name, $film_link, $poster, $info, $genres, $actors, $rating, $description)
    {
        $this->name = $name;
        $this->film_link = $film_link;
        $this->poster_link = $poster;
        $this->info = $info;
        $this->genres = $genres;
        $this->actors = $actors;
        $this->rating = $rating;
        $this->description = $description;
    }
}