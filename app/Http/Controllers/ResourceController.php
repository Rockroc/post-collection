<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Intervention\Image\Image;
use Qiniu\Storage\UploadManager;
use Qiniu\Auth;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;


class ResourceController extends Controller
{
    private $accessKey = 'pEbcCp-BcrFbScELnXc_4CPAf52k7LhYKspQIYkP';
    private $secretKey = 'BPNMGlRUcwGJSUn3EGwwAqY3ncm1b7tw2v4R---h';

    private $http;

    public function __construct(Client $http)
    {
        $client = new Client([

              'timeout' => 0,

        ]);
        $this->http = $client;
    }

    public function index(Request$request)
    {
        set_time_limit(0);
        $type = $request->type;

        $res = $this->$type();

        if($type=='hongli'){
            return \Illuminate\Support\Facades\Response::download($res);
        }

        //category
        $response = $this->http->post(env('ECSHOP_API_URL').'ecapi.category.list', [
            'form_params' => [
                'page' => 1,
                'per_page' => 50
            ],
        ]);
        $categories = Arr::get(json_decode((string)$response->getBody(),true),'categories');

        //brands
        $response = $this->http->post(env('ECSHOP_API_URL').'ecapi.brand.list', [

            'form_params' => [
                'page' => 1,
                'per_page' => 100
            ],
        ]);
        $brands = Arr::get(json_decode((string)$response->getBody(),true),'brands');

        $images = $res['images'];
        $title = $res['title'];
        $content = base64_encode(($res['content']));
//        echo $content;
        $imagesJson = \GuzzleHttp\json_encode($images);

        return view('detail',compact('type','images','title','content','categories','brands','imagesJson'));
    }

    public function import(Request $request)
    {
        set_time_limit(0);

        $images = \Qiniu\json_decode($request->images);

        $ecshopImg = $this->getImages($images);

        $goods_name = $request->goods_name;
        $content = base64_decode($request->goods_desc);

        if($request->type=='great'){
            $goods_name = @iconv('UTF-8','gb2312',$goods_name);
            //匹配出详情里的图片上传到七牛并替换url
            $prel = '/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/';
            preg_match_all($prel, $content, $arr);
            $image_base_url = 'http://www.musicgw.com/';
            foreach($arr[1] as $value){
                preg_match("/Uploadfile(.*)/i",$value,$item);
                if(!$item){
                    preg_match("/upfile(.*)/i",$value,$item);
                }
                $img = $image_base_url.$item[0];
                $test[] = $img;
                //下载并保存图片
                $imgData = $this->download_image($img,'','uploads/',array('jpg', 'gif', 'png','jpeg'),1,false);
                //上传图片
                $res = $this->upload($imgData['saveDir'].$imgData['fileName']);
                if($res){
                    unlink($imgData['saveDir'].$imgData['fileName']);
                    $replaceUrl = 'http://or6dx15ll.bkt.clouddn.com/'.$res['key'];
                    $content = str_replace($value,$replaceUrl,$content);
                }
            }
        }



        $response = $this->http->post(env('ECSHOP_API_URL').'ecapi.product.store', [

            'form_params' => [
                'cat_id'=>$request->cat_id,
                'goods_name'=>$request->goods_name,
                'brand_id'=>$request->brand_id,
                'shop_price'=>$request->shop_price,
                'market_price'=>$request->market_price,
                'min_price'=>$request->min_price,
                'goods_desc'=>base64_encode($content),
                'galleries'=>$ecshopImg
            ],
        ]);
        if($response){
            echo '导入成功';
        }
    }

    public function hongli()
    {
        $url = "http://www.hlmusic.com.cn/productshow.aspx?pid={$_GET['pid']}";
        $response = $this->http->get($url, []);
        $output = $response->getBody();

        $prel = '/<h1>\s*(.*)<\/h1>/';
        preg_match($prel, $output, $arr);
        $title = $arr[1];

        mkdir('uploads/'.$title, 0777, true);
        file_put_contents('uploads/'.$title.'/url.txt',$url);
//        $html = preg_replace("/[\t\n\r]+/","",$output);
//        $prel = '/<div class=\"PInfoTDT\">(.*)<\/div>(.*)/';
//
//        preg_match($prel, $html, $arr);
//
//        print_r($arr);die();
//        $content = $arr[0];

        //图片匹配
        $prel = "/\'pic\':\'(.*)\'\,(.*)/";
        preg_match($prel, $output, $table);
        $prel = "/\/upload\/(.*?((\.gif|\.jpg|\.png|\.jpeg)))/";
        preg_match_all($prel, $table[0], $arr);

        $base_url = 'http://www.hlmusic.com.cn';
        $images = array();
        foreach($arr[0] as $key=>$value){
            $imgUrl = $base_url.$value;
            $images[] = $imgUrl;
            $res = $this->download_image($imgUrl,$fileName = '', 'uploads/'.$title, $fileType = array('jpg', 'gif', 'png','jpeg'), $type = 1,$saveState=false);
        }

        if(file_exists('uploads/'.$title.'.zip')){
            unlink('uploads/'.$title.'.zip');
        }
        $zip = \Comodojo\Zip\Zip::create('uploads/'.$title.'.zip');
        $zip->add('uploads/'.$title);
        $zip->close();
        File::deleteDirectory('uploads/'.$title, $preserve = false);

        return 'uploads/'.$title.'.zip';

//        $manager = new \Comodojo\Zip\ZipManager();
//        $manager->addZip( \Comodojo\Zip\Zip::create($title.'.zip') );
    }

    public function great()
    {
        header("Content-type: text/html; charset=gb2312");

        $url = "http://www.musicgw.com/product/view.asp?id={$_GET['id']}";
        $response = $this->http->get($url, []);
        $output = $response->getBody();

        $prel = "/<p class=p-sku>(.*)<span>(.*)(?=<\/span>)/";
        preg_match($prel, $output, $arr);
        $title = $arr[2];

        $html = preg_replace("/[\t\n\r]+/","",$output);
        $html = preg_replace("/(&#\d*;)/"," ",$html);
        $prel = "/<div class=\"hide productinfo\">(.*)(?=<div class=\"hide brandinfo\">)/";

        preg_match($prel, $html, $arr);
        $content = $arr[0];


//        echo $html;
//        $prel = "/<A style=\"DISPLAY: none\" id=show_big_img\s*href=\"(.*)\" target=_blank>/";
        $prel = "/change_img\(\'(.*)\'(?=,'\.)/i";
        preg_match_all($prel, $output, $arr);
        $base_url = "http://www.musicgw.com/";

        $images = array();
        foreach($arr[1] as $value){
            $images[] = $base_url.$value;
        }

        return [
            'title'=>@iconv('gb2312','UTF-8',$title),
            'content'=>@iconv('gb2312','UTF-8',$content),
            'images'=>$images
        ];

    }

    public function vox()
    {


        $url = "http://www.tomleemusic.com.hk/acton/vox/product_details.php?id={$_GET['id']}&categoryId={$_GET['categoryId']}";

        $response = $this->http->get($url, []);
        $output = $response->getBody();

//        echo $output;die();

        $prel = "/<div class=\"name\">(.*)<\/div>/";
        preg_match($prel, $output, $arr);
        $title = $arr[1];

        $prel = '/<div\s*class=\"Scroller-Container\">(.*)(?=<div id="Scrollbar)/isu';
//        $prel = '/<div class=\"Scroller-Container\">.*)<\/div>/isu';
        preg_match($prel, $output, $arr);
//        print_r($arr);
        $content = $arr[1];

        //图片
        $prel = '/<[img|IMG].*?src=[\'|\"]upload\/(.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/';
        preg_match_all($prel, $output, $arr);
        $imgArr = $arr[1];
        $base_url = "http://www.tomleemusic.com.hk/acton/vox/upload/";

        $images = array();
        foreach($imgArr as $key=>$value){
            $iarr = explode('"',$value);

            $imgUrl = str_replace ( ' ', '%20', $base_url.$iarr[0]);

            $img = get_headers($imgUrl);

            if(isset($img[9])){
                $imgSize = explode(':',$img[9])[1];
            }else{
                $imgSize = explode(':',$img[5])[1];
            }

            if($imgSize>15360){
                $images[] = $base_url.$iarr[0];
            }

        }

        //获取图片
//        $ecshopImg = $this->getImages($images);
        return [
            'title'=>$title,
            'content'=>$content,
            'images'=>$images
        ];

    }

    private function getImages($images)
    {
        $ecshopImg = array();
        foreach ($images as $pic_item) { //循环取出每幅图的地址
            //下载并保存图片
            $imgData = $this->download_image($pic_item,'','uploads/');
            //上传图片
            $res = $this->upload($imgData['saveDir'].$imgData['fileName']);
            if($res){
                $ecshopImg[] = 'http://or6dx15ll.bkt.clouddn.com/'.$res['key'];
            }else{
                var_dump($res);
            }
            //删除图片
            unlink($imgData['saveDir'].$imgData['fileName']);
        }

        return $ecshopImg;
    }

    private function upload($image,$checkSize=true)
    {
        $upManager = new UploadManager();
        $auth = new Auth($this->accessKey, $this->secretKey);
        $bucket = 'ecshop';
        $token = $auth->uploadToken($bucket);

        if($checkSize){
            $filesize = abs(filesize($image));
            if($filesize<15360){
                return false;
            }
        }


        $key = date('YmdHis').rand(100,999).'.'.pathinfo($image,PATHINFO_EXTENSION);
        list($ret, $error) = $upManager->putFile($token, $key, public_path($image));
        if ($error !== null) {
            return false;
        } else {
            return $ret;
        }
    }

    private function download_image($url, $fileName = '', $dirName, $fileType = array('jpg', 'gif', 'png','jpeg'), $type = 1,$saveState=true)
    {
        if ($url == '')
        {
            return false;
        }

        $url = str_replace ( ' ', '%20', $url);

        // 获取文件原文件名
        $defaultFileName = basename($url);

        // 获取文件类型
        $suffix = substr(strrchr($url, '.'), 1);
        if (!in_array($suffix, $fileType))
        {
            return false;
        }

        // 设置保存后的文件名
        $fileName = $fileName == '' ? time() . rand(0, 9) . '.' . $suffix : $defaultFileName;

        // 获取远程文件资源
        if ($type)
        {
            $ch = curl_init();
            $timeout = 30;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $file = curl_exec($ch);
            curl_close($ch);
        }
        else
        {
            ob_start();
            readfile($url);
            $file = ob_get_contents();
            ob_end_clean();
        }

        // 设置文件保存路径
        //$dirName = $dirName . '/' . date('Y', time()) . '/' . date('m', time()) . '/' . date('d', time());
        // $dirName = $dirName . '/' . date('Ym', time());
        if (!file_exists($dirName))
        {
            mkdir($dirName, 0777, true);
        }

        // 保存文件
        $res = fopen($dirName . '/' . $fileName, 'a');
        fwrite($res, $file);
        fclose($res);


        if($saveState){
            $img = \Intervention\Image\Facades\Image::make($dirName.$fileName);
            $width = $img->width();
            $height = $img->height();

            $canvas = \Intervention\Image\Facades\Image::canvas(850, 850, '#ffffff');


            if($width>$height){
                $img->resize(850, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }else{
                $img->resize(null, 850, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            $canvas->insert($img, 'center');

            $canvas->save($dirName.$fileName);
        }


        return array(
            'fileName' => $fileName,
            'saveDir' => $dirName
        );
    }
}
