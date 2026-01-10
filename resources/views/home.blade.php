<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <title>{{ config('app.name', 'VCheck') }}</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link rel="icon" type="image/png" sizes="16x16" href="/img/logo.png">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link href="{{ mix('react/app.css') }}" rel="stylesheet" type="text/css">
        <style>
            .like_disabled {
                cursor: default;
                opacity: .45 !important;
            }
        </style>
        <script>
            window.auth = {!! json_encode(Auth::user()) !!};
            window.auth.can = {
                user: (['user','manager','admin','superadmin'].indexOf(window.auth.role)>-1),
                manager: (['manager','admin','superadmin'].indexOf(window.auth.role)>-1),
                admin: (['admin','superadmin'].indexOf(window.auth.role)>-1),
                superadmin: (['superadmin'].indexOf(window.auth.role)>-1)
            };
            window.csfrToken = '{{ csrf_token() }}';
            window._trans={ messages: {!! json_encode(trans('messages')) !!} }
            window.calculateNds = (amount, nds) => {
                nds = parseInt(nds)/100;
                amount = parseFloat(amount)

                return (Math.round(100*nds*(amount/(1+nds)))/100);
            }
            window.cheques = {
                statuses: {!! json_encode(App\Cheque::getStatuses()) !!},
                types: {!! json_encode(App\Cheque::getTypes()) !!},
                periodicOptions: [
                    {
                        key: 0,
                        value: 'daily',
                        text: 'Ежедневно',
                    },
                    {
                        key: 1,
                        value: 'weekly',
                        text: 'Еженедельно',
                    },
                    {
                        key: 2,
                        value: 'monthly',
                        text: 'Ежемесячно',
                    },
                    {
                        key: 3,
                        value: 'once',
                        text: 'Однократно',
                    }
                ],
                findByValue: (collection, value) => {
                    for( let i in collection) {
                        const item = collection[i]
                        if (item && item.value && item.value == value) return item;
                    }
                    return {
                        key: null,
                        value: value,
                        text: value
                    }
                }
            };
            window.escapeRegExp = function (str) {
                return str.replace(/[-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
            }
            window.procent = <?php
                $user = Auth::user();
                if ($user->procent) {
                    $procent = $user->procent;
                } else {
                    $procent = \App\Param::where('name', 'default_procent')->first()->value;
                }

                print $procent / 100;
            ?>;

            window.message = "{{ session('message') }}";
            window.selected_cheques = [];
        </script>
        <script src="//code-eu1.jivosite.com/widget/VY9ExZv5TP" async></script> 
    </head>
    <body>
        <div id="app"></div>

        <script src="{{ mix('react/app.js') }}" ></script>
    </body>
</html>
