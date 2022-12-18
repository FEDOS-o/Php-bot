<?php

namespace App\Services;

use App\Credentials\Film;
use PHPHtmlParser\Dom;

class HtmlParserService
{
    private $kinopoisk = "https://www.kinopoisk.ru";

    public function parse($str) {
        $dom = new Dom;
        $dom->loadStr($str);
        return new Film($this->getName($dom),
                        $this->getFilmLink($dom),
                        $this->getPosterLink($dom),
                        $this->getInfo($dom),
                        $this->getGenres($dom),
                        $this->getActors($dom),
                        $this->getRating($dom),
                        $this->getDescription($dom));
    }

    function getName($dom) {
        return  preg_replace("/&[a-z]+;/", ' ', $dom->getElementsByClass('filmName')->find('a')[0]->text);
    }

    function getFilmLink($dom) {
        return $this->kinopoisk . $dom->getElementsByClass('filmName')->find('a')[0]->href;
    }

    function getPosterLink($dom) {
        return $this->kinopoisk . $dom->getElementsByClass('poster')->find('a')[0]->find('img')[0]->src;
    }

    function getInfo($dom) {
        return '';
    }

    function getGenres($dom) {
        return '';
    }

    function getActors($dom) {
        $result = $dom->getElementsByClass('info')->find('a')[2]->text .
            ', ' .
            $dom->getElementsByClass('info')->find('a')[3]->text .
            ', ' .
            $dom->getElementsByClass('info')->find('a')[4]->text;
        $result = substr($result, 0, strlen($result) - 6);
        $result .= "...";
        return $result;
    }

    function getRating($dom) {
        return $dom->getElementsByClass('imdb')->text;
    }

    function getDescription($dom) {
        $result = preg_replace("/&[a-z]+;/", ' ', $dom->getElementsByClass('syn')->text);
//        $result = substr($result, 0, strlen($result) - 6);
//        $result .= ".";
        return $result;
    }
}