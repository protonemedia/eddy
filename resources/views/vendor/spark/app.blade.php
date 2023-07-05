<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link href="https://fonts.bunny.net/css?family=Nunito:400,600,700" rel="stylesheet" />

        <!-- Scripts -->
        <script src="https://cdn.paddle.com/paddle/paddle.js"></script>
        <script
            src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.27.0/moment.min.js"
            integrity="sha512-rmZcZsyhe0/MAjquhTgiUcb4d9knaFc7b5xAfju483gbEXTkeJRUMIPk6s3ySZMYUHEcjKbjLjyddGWMrNEvZg=="
            crossorigin="anonymous"
        ></script>
        <script
            src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.20/lodash.min.js"
            integrity="sha512-90vH1Z83AJY9DmlWa8WkjkV79yfS2n2Oxhsi2dZbIv0nC4E6m5AbH8Nh156kkM7JePmqD6tcZsfad1ueoaovww=="
            crossorigin="anonymous"
        ></script>

        <!-- Styles -->
        <style>
            {!! file_get_contents($cssPath) !!}
        </style>

        @if (strpos((string) config('spark.brand.color'), '#') === 0)
            <style>
                .bg-custom-hex {
                    background-color: {!! config('spark.brand.color') !!};
                }
            </style>
        @endif

        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia

        <!-- Scripts -->
        <script>
            window.translations = <?php echo $translations; ?>;

            {!! file_get_contents($jsPath) !!};
        </script>
    </body>
</html>
