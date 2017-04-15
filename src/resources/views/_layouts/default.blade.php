<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Admin Panel" />
        <meta name="author" content="" />

        <title>Admin</title>

        @include('hideyo_backend::_partials.assets')
    </head>
    <body>
        @include('hideyo_backend::_partials.header')
        <div class="container-fluid">
      
            @yield('main')
        </div>

        @include('hideyo_backend::_partials.footer')
    </body>
</html>