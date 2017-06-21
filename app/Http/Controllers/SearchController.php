<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Response;

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
//        print_r($data);
        return view('search',compact('type','data'));
    }

    public function hongli($keyword)
    {
        $searchUrl = 'http://www.hlmusic.com.cn/productsearch.aspx?kw='.$keyword;
        $response = $this->http->post($searchUrl, [
            'form_params' => [
//                'keyword' => $keyword,
            ],
        ]);
        $output = $response->getBody();
//        echo $output;die();
        $prel = '/<br \/><a href=\"(.*)\" target=\"_blank\">(.*)<\/a><\/li>/';
        preg_match_all($prel, $output, $table);
        $data = array();
        foreach($table[1] as $key=>$value){
            $data[$key]['name'] = $table[2][$key];
            $data[$key]['query'] = parse_url($value)['query'];
        }
        return $data;
    }

    public function great($keyword)
    {
        header("Content-type: text/html; charset=gb2312");
        $searchUrl = 'http://www.musicgw.com/product/list.asp';
        $response = $this->http->post($searchUrl, [
//            'headers' => [
//                'Content-Type' => 'text/html;charset=gb2312'
//            ],
            'form_params' => [
                'keyword' => @iconv('UTF-8','gb2312',$keyword),
//                'button'=>'搜索'
            ],
        ]);
        $output = $response->getBody();

        $prel = '/\<li\>\<a\s*href=\"(.*)\"\s*class=\"img\"\>\s*\<img\s(.*)\s*<\/a\>\<a\shref=\"(.*)\">(.*)\<\/a\>\<\/li\>/';
        preg_match_all($prel, $output, $table);

        $data = array();
        foreach($table[3] as $key=>$value){
            $data[$key]['name'] = @iconv('gb2312','UTF-8',$table[4][$key]);
            $data[$key]['query'] = parse_url($value)['query'];
        }

        return $data;

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
