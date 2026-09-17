<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans">
    <div class="flex min-h-screen items-center justify-center bg-gray-700">
        <div
            class="min-w-[540px] max-w-[720px] w-[75vw] min-h-[50vh] bg-slate-900 flex-col flex items-center rounded-3xl justify-center">
            <img class='w-[50%]' src='UCONN_SG_on_blue.svg'>
            <h1 class='text-white font-large font-bold text-4xl'>UCSD RANKINGS</h1>

            <div class='flex gap-[24px] justify-center items-center mt-10 mb-10'>
                <a href="{{ route('login') }}"
                    class='bg-gray-200 text-black text-lg font-bold rounded-md border-none p-2 pl-4 pr-4'>
                    Login
                </a>
                <a href="{{ route('register') }}"
                    class='bg-gray-500 text-white text-lg font-bold rounded-md border-none p-2 pl-4 pr-4'>
                    Register
                </a>
            </div>
        </div>
    </div>
</body>

</html>
