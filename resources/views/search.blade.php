<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <title>post mucis</title>
</head>
<body>
    <div id="list-box">

        <div class="list-group">
            @if(count($data)>0)
            @foreach($data as $item)
                <a href="/detail?type={{$type}}&{{$item['query']}}" target="_blank" class="list-group-item list-group-item-success">
                    {{$item['name']}}
                </a>
            @endforeach
            @else
                <a href="#"  class="list-group-item list-group-item-success">
                    暂无记录
                </a>
            @endif

        </div>

    </div>


</body>
</html>