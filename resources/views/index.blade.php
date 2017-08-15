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
    <div id="search-container">
        <form action="{{url('search')}}" method="get" target="_blank">
            <div id="search-box">
                <select name="type" class="form-control">
                    <option value="vox">vox</option>
                    <option value="great">长城乐器</option>
                    <option value="hongli">弘力乐器</option>
                    <option value="mooer">mooer</option>
                 </select>

                <div class="search-input">
                    <input type="text" name="keyword" class="form-control" placeholder="输入乐器名称">
                </div>
            </div>
            <div id="search-button">
                <button type="submit" class="btn btn-default">搜索</button>
            </div>
        </form>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>