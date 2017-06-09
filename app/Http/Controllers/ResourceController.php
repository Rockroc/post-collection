<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $this->http = $http;
    }

    public function index(Request$request)
    {
        set_time_limit(0);
        $type = $request->type;
        $id = $request->id;
        $res = $this->$type($id);

        //category
        $response = $this->http->post(env('ECSHOP_API_URL').'ecapi.category.list', [
            'form_params' => [
                'page' => 1,
                'per_page' => 30
            ],
        ]);
        $categories = Arr::get(json_decode((string)$response->getBody(),true),'categories');

        //brands
        $response = $this->http->post(env('ECSHOP_API_URL').'ecapi.brand.list', [
            'form_params' => [
                'page' => 1,
                'per_page' => 30
            ],
        ]);
        $brands = Arr::get(json_decode((string)$response->getBody(),true),'brands');

        $images = $res['images'];
        $title = $res['title'];
        $content = $res['content'];

        $imagesJson = \GuzzleHttp\json_encode($images);

        return view('detail',compact('images','title','content','categories','brands','imagesJson'));
    }

    public function import(Request $request)
    {
        $images = \Qiniu\json_decode($request->images);

        $ecshopImg = $this->getImages($images);

        $response = $this->http->post(env('ECSHOP_API_URL').'ecapi.product.store', [
            'form_params' => [
                'cat_id'=>$request->cat_id,
                'goods_name'=>$request->goods_name,
                'brand_id'=>$request->brand_id,
                'market_price'=>$request->market_price,
                'goods_desc'=>$request->goods_desc,
                'galleries'=>$ecshopImg
            ],
        ]);
        if($response){
            echo '导入成功';
        }
    }

    public function vox($id)
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

    private function upload($image)
    {
        $upManager = new UploadManager();
        $auth = new Auth($this->accessKey, $this->secretKey);
        $bucket = 'ecshop';
        $token = $auth->uploadToken($bucket);

        $filesize = abs(filesize($image));
        if($filesize<15360){
            return false;
        }

        $key = date('YmdHis').rand(100,999).'.'.pathinfo($image,PATHINFO_EXTENSION);
        list($ret, $error) = $upManager->putFile($token, $key, public_path($image));
        if ($error !== null) {
            return false;
        } else {
            return $ret;
        }
    }

    private function download_image($url, $fileName = '', $dirName, $fileType = array('jpg', 'gif', 'png','jpeg'), $type = 1)
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

        return array(
            'fileName' => $fileName,
            'saveDir' => $dirName
        );
    }
}
