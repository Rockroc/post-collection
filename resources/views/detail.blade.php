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
            <div class="panel-heading h2 text-center"><input type="text" name="goods_name" value="{{$title}}" /></div>
            <input type="hidden" name="images" value="{{$imagesJson}}">
            <input type="hidden" name="goods_desc" value="{{$content}}">
            <input type="hidden" name="type" value="{{$type}}">
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
                            <select id="category" name="cat_id" class="form-control">
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
                            <select id="brand" name="brand_id" class="form-control">
                                @foreach($brands as $item)
                                <option value="{{$item['id']}}">{{$item['name']}}</option>
                                @endforeach
                             </select>
                        </div>
                    </div>
                </li>
                <li class="list-group-item list-item-height">
                    <div class="form-group">
                        <label for="inputEmail3" class="col-xs-2 col-sm-2 control-label">批发价</label>
                        <div class="col-sm-10 col-xs-10">
                            <input type="number" class="form-control" id="inputEmail3" name="shop_price" placeholder="价格">
                        </div>
                    </div>
                </li>
                <li class="list-group-item list-item-height">
                    <div class="form-group">
                        <label for="inputEmail3" class="col-xs-2 col-sm-2 control-label">建议零售价</label>
                        <div class="col-sm-10 col-xs-10">
                            <input type="number" class="form-control" id="inputEmail3" name="market_price" placeholder="价格">
                        </div>
                    </div>
                </li>
                <li class="list-group-item list-item-height">
                    <div class="form-group">
                        <label for="inputEmail3" class="col-xs-2 col-sm-2 control-label">最低零售价</label>
                        <div class="col-sm-10 col-xs-10">
                            <input type="number" class="form-control" id="inputEmail3" name="min_price" placeholder="价格">
                        </div>
                    </div>
                </li>
                <li class="list-group-item list-item-height">
                    <div class="form-group">
                        <label for="inputEmail3" class="col-xs-2 col-sm-2 control-label">琴行价</label>
                        <div class="col-sm-10 col-xs-10">
                            <input type="number" class="form-control" id="inputEmail3" name="piano_price" placeholder="价格">
                        </div>
                    </div>
                </li>
                <li class="list-group-item list-item-height">
                    <div class="form-group">
                        <label for="inputEmail3" class="col-xs-2 col-sm-2 control-label">琴行机构价</label>
                        <div class="col-sm-10 col-xs-10">
                            <input type="number" class="form-control" id="inputEmail3" name="mechanism_price" placeholder="价格">
                        </div>
                    </div>
                </li>
            </ul>
            <button style="width:100%;" class="btn btn-primary" type="submit">导入商品到ECSHOP</button>
        </div>
    </form>
</div>

<script>
    function set_select_checked(selectId, checkValue){
        var select = document.getElementById(selectId);

        for (var i = 0; i < select.options.length; i++){
            if (select.options[i].value == checkValue){
                select.options[i].selected = true;
                break;
            }
        }
    }

    @if($categoryId != 0)
        set_select_checked('category',{{$categoryId}});
    @endif

    @if($brandId != 0)
        set_select_checked('brand',{{$brandId}});
    @endif



</script>

</body>
</html>