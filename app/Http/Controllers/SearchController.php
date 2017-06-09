<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class SearchController extends Controller
{
    private $http;


    public function __construct(Client $http)
    {
        $this->http = $http;
    }

    public function index(Request $request)
    {
        $type = $request->type;
        $keyword = $request->keyword;
        $data = $this->$type($keyword);

        return view('search',compact('type','data'));
    }


    public function vox($keyword)
    {
        $base_url = 'http://www.tomleemusic.com.hk/';

        //搜索的地址
        $searchUrl = 'http://www.tomleemusic.com.hk/acton/vox/product_search.php';

        //解析搜索结果的正则


        $response = $this->http->post($searchUrl, [
            'form_params' => [
                'key' => $keyword,
                'brandId' => 16
            ],
        ]);

        $output = $response->getBody();
//        echo $output;die();


        $prel = '/\<table\s*([\w\=\s\"\>\<\.\?\&\/\-\p{Han}]+|])(?=table)/u';
        preg_match($prel, $output, $table);

//        $prel = "/<tr><td><a href=\"(.*)\">(.*)<\/a><\/td><\/tr>/";
        //链接
        $prel = '/(?<=)<a\s*href="(\w.|=)+(?=)/';

        $data = array();
        if(isset($table[0])){
            preg_match_all($prel, $table[0], $urls);
            $needUrls = array();

            foreach($urls[0] as $value){
                $childrenUrl = explode('"',$value);
                $query = parse_url($childrenUrl[1]);
                $needUrls[] = $query['query'];
            }

            //名称
            $prel = '/(?<=\>)[\w\/\-\s{0,1}\p{Han}]+(?=\<\/a\>)/u';
            preg_match_all($prel, $table[0], $names);


            foreach($names[0] as $key=>$value){
                $data[$key]['name'] = $value;
                $data[$key]['query'] = $needUrls[$key];
            }
        }



        return $data;


    }

}
