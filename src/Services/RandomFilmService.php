<?php

namespace App\Services;

use App\Credentials\FilmRequest;
use GuzzleHttp\Client;

class RandomFilmService
{

    function __construct() {

    }

    public function get_films(FilmRequest $params) {
        $client = new Client(array( 'curl' => array( CURLOPT_SSL_VERIFYPEER => false, ), ));
        $response = $client->request('GET', 'https://www.kinopoisk.ru/chance/?item=true&not_show_rated=false&count=' . $params->count . '&max_years=' . $params->max_years . '&min_years=' . $params->min_years . '&rnd=' . $this->random01());
        $result = json_decode($response->getBody(), true);
        $parser = new HtmlParserService();
        return array_map(fn($x) => $parser->parse($x), $result);
    }


    function random01(): float
    {
        return (float)rand() / (float)getrandmax();
    }

}