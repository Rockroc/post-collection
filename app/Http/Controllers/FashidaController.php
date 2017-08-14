<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;

class FashidaController extends Controller
{
    public function index(Request $request)
    {
        set_time_limit(0);
        $param = $request->all();


        header("Content-type: text/html; charset=gb2312");
//        $url = 'http://www.fastgz.com/products/list.asp?classid=1';
        $url = 'http://www.fastgz.com/products/list.asp?brandid=14&classid=1';

        $content = file_get_contents($url);

        $productUrl = 'http://www.fastgz.com/products/';

        if (isset($param['page'])) {
            $current_page = $param['page'];
            $total = $param['total'];
        }else{
            //找出总页数
            $preg = '/<\/span>\/(\d*)/';
            preg_match($preg, $content, $pageArr);
            $current_page = 1;
            $total = $pageArr[1];
            $total = 2;
        }
        $lists = $this->getListUrls($url,$current_page);

        foreach ($lists as $key => $value) {
            $uri = $productUrl . $value;
            $this->collection($uri,$current_page,$total);
        }

        if($current_page<$total){
            $current_page++;
            header('Location:fashida?page='.$current_page.'&total='.$total);

        }else{
            echo $total;
            echo 'ok';
        }

        //获取当前页所有产品


    }


    public function getListUrls($url,$page)
    {
        //url拼上页数
        $preg = '/<a\sclass=img\shref=\"(.*)\"/';
        $uri = $url . '&page=' . $page;
        $list = file_get_contents($uri);
        preg_match_all($preg, $list, $listArr);

        return $listArr[1];


    }


    //获取详情并采集
    public function collection($uri)
    {



        header("Content-type: text/html; charset=gb2312");

        //获取详情页信息
        $html = file_get_contents($uri);

        $preg = '/<h2>(.*)<\/h2>/';
        preg_match($preg, $html, $titleArr);
        $titleN = $titleArr[1];
        $title = preg_replace("/(&#\d*;)/", '', $titleN);

//        $preg = '/<p>\p{Han}{2}：(.*)/';
        $preg = '/&gt;\s(\d*)<\/div>/';
        preg_match($preg, $html, $modelArr);
        $model = $modelArr[1];


        $dir1 = 'products/dianjita/Fender' . '/' . $title;
        $path = 'products/dianjita/Fender' . '/' . $title . '/' . $model;

        //生成目录
        if (!is_dir($dir1)) {
            File::makeDirectory($dir1, $mode = 0777, $recursive = false);
        }
        if (!is_dir($path)) {
            File::makeDirectory($path, $mode = 0777, $recursive = false);
        }

        //采集图片
        $preg = '/<img src=\"\/files\/products\/small\/(.*?((\.gif|\.jpg|\.png|\.jpeg)))/';
        preg_match($preg, $html, $imgArr);
        $imgUrl = 'http://www.fastgz.com//files/products/small/' . $imgArr[1];
        $this->download_image($imgUrl, $model, $path);

        //采集简介
        $html = preg_replace("/[\t\n\r]+/", "", $html);
        $preg = '/<div class=\"productinfo\"(.*)(?=<div class=\"hide brandinfo\" )/';
        preg_match($preg, $html, $contentArr);

//        echo $contentArr[0];die();
//        $content = @iconv('gb2312', 'UTF-8', $contentArr[0]);
//
//        echo $content;

        file_put_contents($path . '/content.html', $contentArr[0]);
    }


    private function download_image($url, $fileName = '', $dirName, $fileType = array('jpg', 'gif', 'png', 'jpeg'), $type = 1)
    {
        if ($url == '') {
            return false;
        }

        $url = str_replace(' ', '%20', $url);

        // 获取文件原文件名
        $defaultFileName = basename($url);

        // 获取文件类型
        $suffix = substr(strrchr($url, '.'), 1);
        if (!in_array($suffix, $fileType)) {
            return false;
        }

        // 设置保存后的文件名
        $fileName = $fileName == '' ? time() . rand(0, 9) . '.' . $suffix : $fileName . '.' . $suffix;

        // 获取远程文件资源
        if ($type) {
            $ch = curl_init();
            $timeout = 30;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $file = curl_exec($ch);
            curl_close($ch);
        } else {
            ob_start();
            readfile($url);
            $file = ob_get_contents();
            ob_end_clean();
        }

        // 设置文件保存路径
        //$dirName = $dirName . '/' . date('Y', time()) . '/' . date('m', time()) . '/' . date('d', time());
        // $dirName = $dirName . '/' . date('Ym', time());
        if (!file_exists($dirName)) {
            mkdir($dirName, 0777, true);
        }

        // 保存文件
        $res = fopen($dirName . '/' . $fileName, 'a');
        fwrite($res, $file);
        fclose($res);


        $img = \Intervention\Image\Facades\Image::make($dirName . '/' . $fileName);

        $img->rotate(90);

        $img->save($dirName . '/' . $fileName);


        return array(
            'fileName' => $fileName,
            'saveDir'  => $dirName
        );
    }
}
