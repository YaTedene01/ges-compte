<!-- HTML for static distribution bundle build --><!DOCTYPE html>

<!DOCTYPE html><html lang="en">

<html lang="en"><head>

<head>    <meta charset="UTF-8">

    <meta charset="UTF-8">    <title>{{config('l5-swagger.documentations.'.$documentation.'.api.title')}}</title>

    <title>{{config('l5-swagger.documentations.'.$documentation.'.api.title')}}</title>    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/swagger-api/swagger-ui/dist/swagger-ui.css') }}">

    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/5.9.0/swagger-ui.min.css">    <link rel="icon" type="image/png" href="{{ asset('vendor/swagger-api/swagger-ui/dist/favicon-32x32.png') }}" sizes="32x32"/>

    <link rel="icon" type="image/png" href="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/5.9.0/favicon-32x32.png" sizes="32x32"/>    <link rel="icon" type="image/png" href="{{ asset('vendor/swagger-api/swagger-ui/dist/favicon-16x16.png') }}" sizes="16x16"/>

    <link rel="icon" type="image/png" href="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/5.9.0/favicon-16x16.png" sizes="16x16"/>    <style>

    <style>    html

        html {    {

            box-sizing: border-box;        box-sizing: border-box;

            overflow: -moz-scrollbars-vertical;        overflow: -moz-scrollbars-vertical;

            overflow-y: scroll;        overflow-y: scroll;

        }    }

        *,    *,

        *:before,    *:before,

        *:after {    *:after

            box-sizing: inherit;    {

        }        box-sizing: inherit;

        body {    }

            margin:0;

            background: #fafafa;    body {

        }      margin:0;

    </style>      background: #fafafa;

</head>    }

    </style>

<body>    @if(config('l5-swagger.defaults.ui.display.dark_mode'))

<div id="swagger-ui"></div>        <style>

            body#dark-mode,

<script src="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/5.9.0/swagger-ui-bundle.min.js"></script>            #dark-mode .scheme-container {

<script src="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/5.9.0/swagger-ui-standalone-preset.min.js"></script>                background: #1b1b1b;

<script>            }

    window.onload = function() {            #dark-mode .scheme-container,

        // Begin Swagger UI call region            #dark-mode .opblock .opblock-section-header{

        const ui = SwaggerUIBundle({                box-shadow: 0 1px 2px 0 rgba(255, 255, 255, 0.15);

            url: "{{ url(config('l5-swagger.documentations.'.$documentation.'.paths.docs_json', 'swagger.json')) }}",            }

            dom_id: '#swagger-ui',            #dark-mode .operation-filter-input,

            deepLinking: true,            #dark-mode .dialog-ux .modal-ux,

            presets: [            #dark-mode input[type=email],

                SwaggerUIBundle.presets.apis,            #dark-mode input[type=file],

                SwaggerUIStandalonePreset            #dark-mode input[type=password],

            ],            #dark-mode input[type=search],

            plugins: [            #dark-mode input[type=text],

                SwaggerUIBundle.plugins.DownloadUrl            #dark-mode textarea{

            ],                background: #343434;

            layout: "StandaloneLayout",                color: #e7e7e7;

            docExpansion: "none",            }

            filter: true,            #dark-mode .title,

            persistAuthorization: true,            #dark-mode li,

            requestInterceptor: function(request) {            #dark-mode p,

                request.headers['X-CSRF-TOKEN'] = '{{ csrf_token() }}';            #dark-mode table,

                return request;            #dark-mode label,

            }            #dark-mode .opblock-tag,

        });            #dark-mode .opblock .opblock-summary-operation-id,

        window.ui = ui;            #dark-mode .opblock .opblock-summary-path,

    };            #dark-mode .opblock .opblock-summary-path__deprecated,

</script>            #dark-mode h1,

</body>            #dark-mode h2,

</html>            #dark-mode h3,
            #dark-mode h4,
            #dark-mode h5,
            #dark-mode .btn,
            #dark-mode .tab li,
            #dark-mode .parameter__name,
            #dark-mode .parameter__type,
            #dark-mode .prop-format,
            #dark-mode .loading-container .loading:after{
                color: #e7e7e7;
            }
            #dark-mode .opblock-description-wrapper p,
            #dark-mode .opblock-external-docs-wrapper p,
            #dark-mode .opblock-title_normal p,
            #dark-mode .response-col_status,
            #dark-mode table thead tr td,
            #dark-mode table thead tr th,
            #dark-mode .response-col_links,
            #dark-mode .swagger-ui{
                color: wheat;
            }
            #dark-mode .parameter__extension,
            #dark-mode .parameter__in,
            #dark-mode .model-title{
                color: #949494;
            }
            #dark-mode table thead tr td,
            #dark-mode table thead tr th{
                border-color: rgba(120,120,120,.2);
            }
            #dark-mode .opblock .opblock-section-header{
                background: transparent;
            }
            #dark-mode .opblock.opblock-post{
                background: rgba(73,204,144,.25);
            }
            #dark-mode .opblock.opblock-get{
                background: rgba(97,175,254,.25);
            }
            #dark-mode .opblock.opblock-put{
                background: rgba(252,161,48,.25);
            }
            #dark-mode .opblock.opblock-delete{
                background: rgba(249,62,62,.25);
            }
            #dark-mode .loading-container .loading:before{
                border-color: rgba(255,255,255,10%);
                border-top-color: rgba(255,255,255,.6);
            }
            #dark-mode svg:not(:root){
                fill: #e7e7e7;
            }
            #dark-mode .opblock-summary-description {
                color: #fafafa;
            }
        </style>
    @endif
</head>

<body @if(config('l5-swagger.defaults.ui.display.dark_mode')) id="dark-mode" @endif>
<div id="swagger-ui"></div>

<script src="{{ asset('vendor/swagger-api/swagger-ui/dist/swagger-ui-bundle.js') }}"></script>
<script src="{{ asset('vendor/swagger-api/swagger-ui/dist/swagger-ui-standalone-preset.js') }}"></script>
<script>
    window.onload = function() {
        // Build a system
        const ui = SwaggerUIBundle({
            dom_id: '#swagger-ui',
            url: "{!! $urlToDocs !!}",
            operationsSorter: {!! isset($operationsSorter) ? '"' . $operationsSorter . '"' : 'null' !!},
            configUrl: {!! isset($configUrl) ? '"' . $configUrl . '"' : 'null' !!},
            validatorUrl: {!! isset($validatorUrl) ? '"' . $validatorUrl . '"' : 'null' !!},
            oauth2RedirectUrl: "{{ route('l5-swagger.'.$documentation.'.oauth2_callback', [], $useAbsolutePath) }}",

            requestInterceptor: function(request) {
                request.headers['X-CSRF-TOKEN'] = '{{ csrf_token() }}';
                return request;
            },

            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset
            ],

            plugins: [
                SwaggerUIBundle.plugins.DownloadUrl
            ],

            layout: "StandaloneLayout",
            docExpansion : "{!! config('l5-swagger.defaults.ui.display.doc_expansion', 'none') !!}",
            deepLinking: true,
            filter: {!! config('l5-swagger.defaults.ui.display.filter') ? 'true' : 'false' !!},
            persistAuthorization: "{!! config('l5-swagger.defaults.ui.authorization.persist_authorization') ? 'true' : 'false' !!}",

        })

        window.ui = ui

        @if(in_array('oauth2', array_column(config('l5-swagger.defaults.securityDefinitions.securitySchemes'), 'type')))
        ui.initOAuth({
            usePkceWithAuthorizationCodeGrant: "{!! (bool)config('l5-swagger.defaults.ui.authorization.oauth2.use_pkce_with_authorization_code_grant') !!}"
        })
        @endif
    }
</script>
</body>
</html>
