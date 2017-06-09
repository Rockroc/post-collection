<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <title>post music</title>
</head>
<body>

<div id="list-box">
    <form action="/import" method="post">
        {!! csrf_field() !!}
        <div class="panel panel-default">
            <div class="panel-heading h2 text-center">{{$title}}</div>
            <input type="hidden" name="goods_name" value="{{$title}}" />
            <input type="hidden" name="images" value="{{$imagesJson}}">
            <input type="hidden" name="goods_desc" value="{{$content}}">
            <div class="panel-body">
                <div class="row">

                    @foreach($images as $row)
                    <div class="col-xs-3 col-md-3">
                        <a href="{{$row}}" target="_blank" class="thumbnail">
                            <img src="{{$row}}" style="height: 180px; width: 100%; display: block;" alt="">
                        </a>
                    </div>
                    @endforeach

                </div>
            </div>
            <ul class="list-group">
                <li class="list-group-item list-item-height">
                    <div class="form-group">
                        <label for="inputEmail3" class="col-xs-2 col-sm-2 control-label">分类</label>
                        <div class="col-sm-10 col-xs-10">
                            <select name="cat_id" class="form-control">
                                @foreach($categories as $item)
                                    <option value="{{$item['id']}}">{{$item['name']}}</option>
                                    @foreach($item['categories'] as $row)
                                        <option value="{{$row['id']}}">---{{$row['name']}}</option>
                                    @endforeach
                                @endforeach
                             </select>
                        </div>
                    </div>
                </li>
                <li class="list-group-item list-item-height">
                    <div class="form-group">
                        <label for="inputEmail3" class="col-xs-2 col-sm-2 control-label">品牌</label>
                        <div class="col-sm-10 col-xs-10">
                            <select name="brand_id" class="form-control">
                                @foreach($brands as $item)
                                <option value="{{$item['id']}}">{{$item['name']}}</option>
                                @endforeach
                             </select>
                        </div>
                    </div>
                </li>
                <li class="list-group-item list-item-height">
                    <div class="form-group">
                        <label for="inputEmail3" class="col-xs-2 col-sm-2 control-label">本店价格</label>
                        <div class="col-sm-10 col-xs-10">
                            <input type="number" class="form-control" id="inputEmail3" name="shop_price" placeholder="价格">
                        </div>
                    </div>
                </li>
                <li class="list-group-item list-item-height">
                    <div class="form-group">
                        <label for="inputEmail3" class="col-xs-2 col-sm-2 control-label">市场价格</label>
                        <div class="col-sm-10 col-xs-10">
                            <input type="number" class="form-control" id="inputEmail3" name="market_price" placeholder="价格">
                        </div>
                    </div>
                </li>
            </ul>
            <button style="width:100%;" class="btn btn-primary" type="submit">导入商品到ECSHOP</button>
        </div>
    </form>
</div>


</body>
</html>