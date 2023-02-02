<?php

namespace App\Console\Commands;

use App\Models\Product;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Console\Command;
use Symfony\Component\DomCrawler\Crawler;

class ParseCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    public function handle()
    {
        $next_page = 2;
        for ($current_page = 1; $current_page < $next_page; $current_page++){
            $url = "https://anaki.uz/odezhda-1/futbolki-i-topy-1?page=$current_page";
            $response = $this->getPage($url);
            if (!empty($response)){
                foreach ($response[0]['products'] as $item){
                    $prod = new Product($item);
                    $response_text = Product::query()->where(['url' => $prod->url])->count() ? ' уже существует' : ' Сохранен';

                    echo '<pre>';
                    var_dump($prod->url . $response_text); // or do something
                    echo '</pre>';
                }

                $next_page = $next_page + 1;
            }
        }

    }

    protected function getPage($url): array
    {
        $jar = new CookieJar();
        $client = new Client();
        $prods = [];
        $res = $client->request('GET', $url, [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8, application/json, text/javascript, */*; q=0.01',
                'Accept-Encoding' => 'gzip, deflate, sdch, br',
                'Accept-Language' => 'ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4',
                'Connection' => 'keep-alive',
                'Upgrade-Insecure-Requests' => '1'
            ]
        ], ['cookies' => $jar]);

        if($res->getStatusCode()=='200'){
            $crawler = new Crawler($res->getBody()->getContents());

            $prods = $crawler->filter('div#product-category>div.row>div#content>div.custom-category>div.row')->each(function (Crawler $node) {
                $prods['products'] = $node->filter('div.product-layout')->each(function (Crawler $node){
                    $prods['url'] = $node->filter('a')->link()->getUri();
                    $prods['country'] = $node->filter('p.manufacture-product')->first()->text();
                    $prods['price'] = $node->filter('p.price')->first()->text();
                    $prods['name'] = $node->filter('p.product-description')->first()->text();
                    $prods['default_image'] = $node->filter('img')->first()->image()->getUri();
                    $prods['rotate_image'] = $node->filter('img')->last()->image()->getUri();

                    return $prods;
                });
                return $prods;
            });
        }else{
            var_dump('error');
        }
        return $prods;
    }

}
