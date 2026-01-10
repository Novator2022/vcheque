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
        @include('_inline_styles')
    </head>
    <body>
        <div class="item" style="padding: .78571429em .92857143em;">
            <h2>Ваш баланс: {{ Auth::user()->balance ?? 0 }} руб.</h2>

            <div class="payment_item">
                <img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjwhRE9DVFlQRSBzdmcgIFBVQkxJQyAnLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4nICAnaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkJz48c3ZnIGVuYWJsZS1iYWNrZ3JvdW5kPSJuZXcgMCAwIDUxMiA1MTIiIGhlaWdodD0iNTEycHgiIGlkPSJMYXllcl8xIiB2ZXJzaW9uPSIxLjEiIHZpZXdCb3g9IjAgMCA1MTIgNTEyIiB3aWR0aD0iNTEycHgiIHhtbDpzcGFjZT0icHJlc2VydmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPjxnPjxnPjxwYXRoIGQ9Ik00ODEuODc0LDEwMi42OThjMTMuODU0LDAsMjUuMTI2LDExLjI3MSwyNS4xMjYsMjUuMTI2djI1Ny44OTljMCwxMy44NTQtMTEuMjcxLDI1LjEyNi0yNS4xMjYsMjUuMTI2ICAgIEgzMC4xNDNjLTEzLjg1NCwwLTI1LjEyNi0xMS4yNzEtMjUuMTI2LTI1LjEyNlYxMjcuODI0YzAtMTMuODU0LDExLjI3MS0yNS4xMjYsMjUuMTI2LTI1LjEyNkg0ODEuODc0IE00ODEuODc0LDk3LjY5OEgzMC4xNDMgICAgYy0xNi42MzgsMC0zMC4xMjYsMTMuNDg4LTMwLjEyNiwzMC4xMjZ2MjU3Ljg5OWMwLDE2LjY0LDEzLjQ4OCwzMC4xMjYsMzAuMTI2LDMwLjEyNmg0NTEuNzMxICAgIGMxNi42NCwwLDMwLjEyNi0xMy40ODYsMzAuMTI2LTMwLjEyNlYxMjcuODI0QzUxMiwxMTEuMTg2LDQ5OC41MTMsOTcuNjk4LDQ4MS44NzQsOTcuNjk4TDQ4MS44NzQsOTcuNjk4eiIgZmlsbD0iI0VDNzAwNCIvPjwvZz48Zz48cG9seWdvbiBmaWxsPSIjMUQxRDFCIiBwb2ludHM9IjM5NC45NCwyNzAuNzgzIDM2MC4zOTIsMjcwLjc4MyAzNjAuMzkyLDIzNy44MTIgMzU1LjEzNSwyMzcuODEyIDM1NS4xMzUsMjcwLjc4MyAgICAgMzU0Ljk4NSwyNzAuNzgzIDM1NC45ODUsMjc1Ljg0NCAzNTUuMTM1LDI3NS44NDQgMzU1LjEzNSwzNTkuOTc0IDM2MC4zOTIsMzU5Ljk3NCAzNjAuMzkyLDI3NS44NDQgMzk0Ljk0LDI3NS44NDQgICAiLz48cGF0aCBkPSJNNDY4LjYwNCwyNDcuNjQ1aC0zLjMyNXY0OS4yODJoLTY1LjYwNGMtMS4yMjQsMC0yLjIxMy0wLjk4OS0yLjIxMy0yLjIwNCAgICBjMC0xLjIxMywwLjk4OS0yLjIxMiwyLjIxMy0yLjIxMmg2MS4wNDh2LTUwLjY4M2MwLTAuNzEtMC41Ny0xLjI3OS0xLjI3OS0xLjI3OWMwLDAtMTYuOTQ3LDEuMDA4LTMzLjkzMywyLjc2NCAgICBjLTE5LjE1MSwxLjk3OS0yNi43NywzLjIzMS0yOC43Myw0LjEzNmMtMi4xNDgsMC45OS0zLjYxNCwzLjI5Ni0zLjYxNCw2LjQzNHY0My42MTVjMCwyLjExOSwxLjcxOSwzLjgzOCwzLjgzOCwzLjgzOGg3MS42ICAgIGMwLjcwOSwwLDEuMjc5LTAuNTY5LDEuMjc5LTEuMjc5di01MS4xMzJDNDY5Ljg4MywyNDguMjE0LDQ2OS4zMTIsMjQ3LjY0NSw0NjguNjA0LDI0Ny42NDV6IiBmaWxsPSIjRUM3MDA0Ii8+PHBhdGggZD0iTTQzOS40MjQsMjc3Ljk5MWMwLDAsMTIuMjk3LDAsMTYuNTA5LDBjNS43NzEsMCwxMy45NSwxLjU0LDEzLjk1LDEwLjMzNmMwLDAsMi4wMTctMy42MjIsMi4wMTctNS4yMSAgICBjMC0wLjk4LDAtMTMuNTU4LDAtMTQuNjU5YzAtMi4zMDgtMS4zNDUtOC41NDQtMTMuMDYzLTguNTQ0Yy03Ljg3MSwwLTE5LjQxMiwwLTE5LjQxMiwwbC0zLjQwOCw2LjM5NiAgICBjMCwwLTAuMzY0LDEuOTYxLDAuNTQxLDQuMjg1QzQzNy40NTMsMjcyLjkxMSw0MzkuNDI0LDI3Ny45OTEsNDM5LjQyNCwyNzcuOTkxeiIgZmlsbD0iI0ZGRkZGRiIvPjxwYXRoIGQ9Ik00MzkuNDI0LDI3NS4xMDVoMTkuMTY5YzAsMCwxMS4yOS0wLjM2NCwxMS4yOSw3LjA3OGMwLDAsMi4wMTctMC4zNjQsMi4wMTctMS45NTIgICAgYzAtMC45NzksMC0xMy41NTcsMC0xNC42NDljMC0yLjMxNS0xLjM0NS04LjU1NC0xMy4wNjMtOC41NTRjLTcuODcxLDAtMTkuNDEyLDAtMTkuNDEyLDBsLTMuNDA4LDkuMjgyTDQzOS40MjQsMjc1LjEwNXoiIGZpbGw9IiNFQzcwMDQiLz48cG9seWdvbiBmaWxsPSIjRUM3MDA0IiBwb2ludHM9IjM2OS41ODksMjY5LjMyNiAzNjIuMTEsMjY5LjMyNiAzNjIuMTEsMjY0LjM2OCAzNTMuNDE3LDI2NC4zNjggMzUzLjQxNywyODIuMjY4IDM2Mi4xMSwyODIuMjY4ICAgICAzNjIuMTEsMjc3LjMxIDM2OS41ODksMjc3LjMxICAgIi8+PHBhdGggZD0iTTUxLjY4NiwzMTAuODMxaC04LjEwNXYtMjAuMjE1aDUuODY0YzAuMzg4LTAuNDQ5LDAuODEyLTEuMDM3LDEuMjctMS43NTcgICAgYzAuNDUzLTAuNzA5LDAuOTEtMS41OTYsMS4zNjMtMi42MzNjMS4wNDYtMi4wODIsMS45Ny00Ljk5NiwyLjc4My04Ljc0YzAuODE3LTMuNzQzLDEuMjI0LTguNjQ2LDEuMjI0LTE0LjY5NnYtMTAuODMyaDMwLjI4MSAgICB2MzguNjU4aDYuNjc2djIwLjIxNWgtOC4xMXYtMTMuMjc4SDUxLjY4NlYzMTAuODMxeiBNNzguMjYsMjU4LjkyNUg2NC4xOThjMC4wNjEsOC4yODItMC4yOTksMTQuNzI1LTEuMDc0LDE5LjMxOCAgICBjLTAuNzg0LDQuNTk0LTIuMTg1LDguNzMxLTQuMjAyLDEyLjM3M0g3OC4yNlYyNTguOTI1eiIgZmlsbD0iIzFEMUQxQiIvPjxwYXRoIGQ9Ik0xNDAuMTU3LDI4OS41NzljLTAuOTgsMS4xNjctMS45ODksMi4yMTMtMy4wMzQsMy4xMjhjLTEuMDM3LDAuOTA2LTIuMDgzLDEuNjktMy4xMjgsMi4zNDQgICAgYy0yLjczNiwxLjYyNC01LjMyMiwyLjYxNS03Ljc2OSwyLjk2OWMtMi40NDYsMC4zNjQtNC4xMTgsMC41NDMtNS4wMzMsMC41NDNjLTcuMTAxLDAtMTIuNjcxLTIuMzM2LTE2LjcxNC02Ljk5NCAgICBjLTQuMDQzLTQuNjYtNi4wNi0xMC4xNS02LjA2LTE2LjQ3MmMwLTYuNzc5LDEuOTctMTIuNTIxLDUuOTE2LTE3LjIxOWMzLjk0LTQuNjg3LDkuMTA0LTcuMDQsMTUuNDk1LTcuMDQgICAgYzYuNjQ0LDAsMTEuNTQ2LDIuMjAzLDE0LjcwNiw2LjU5M2MzLjE1Niw0LjM5Nyw0Ljk2Nyw5LjIyNSw1LjQyNSwxNC40OTJjMC4wNjYsMC41MzEsMC4xMTMsMS4wODIsMC4xNSwxLjY2ICAgIGMwLjAyOCwwLjU5LDAuMDQ2LDEuMTQxLDAuMDQ2LDEuNjYzdjAuNDg1djAuNTg4aC0zMi40NTd2MC4xOTd2MC4xOTV2MC4xOTZ2MC4xOTVjMCwzLjYwNSwxLjA3NCw2LjkxOSwzLjIyMiw5Ljk2NCAgICBjMi4xNTIsMy4wNDQsNS41NzQsNC41NTYsMTAuMjY3LDQuNTU2YzEuNjI5LDAsMy4wOTUtMC4xNjcsNC40MDItMC41MzJjMS4yOTgtMC4zNTQsMi41NzgtMC44OTYsMy44MS0xLjYxNCAgICBjMC45MTUtMC41NzksMS44MjEtMS4yNzEsMi43MzYtMi4wNDZjMC45MTUtMC43ODQsMS44OTYtMS42ODksMi45MzEtMi43MzVMMTQwLjE1NywyODkuNTc5eiBNMTMwLjk3NCwyNjkuMjg4ICAgIGMtMC4wNjUtMC42NTMtMC4xNzctMS4zNzItMC4zNC0yLjE4NWMtMC4xNjQtMC44MTItMC40MDctMS42NzItMC43MzMtMi41NzdjLTAuNzE0LTEuNjI0LTEuODcyLTMuMTI4LTMuNDY5LTQuNTE5ICAgIGMtMS41OTctMS4zOTItMy44ODQtMi4wOTItNi44ODItMi4wOTJjLTMuMzI0LDAtNS43OTgsMC44MTItNy40MjMsMi40MjhjLTEuNjI5LDEuNjI1LTIuNzM1LDMuMzcxLTMuMzI0LDUuMjU2ICAgIGMtMC4xOTEsMC42NDUtMC4zNCwxLjI4LTAuNDM4LDEuODg3Yy0wLjA5NCwwLjYyNi0wLjE3NywxLjIxNC0wLjI0MywxLjgwMkgxMzAuOTc0eiIgZmlsbD0iIzFEMUQxQiIvPjxwb2x5Z29uIGZpbGw9IiMxRDFEMUIiIHBvaW50cz0iMTU4LjIyNiwyOTcuNTUzIDE1MC4xMjEsMjk3LjU1MyAxNTAuMTIxLDI1MS45NTggMTU4LjIyNiwyNTEuOTU4IDE1OC4yMjYsMjY5LjM5MSAgICAgMTc5LjQ0LDI2OS4zOTEgMTc5LjQ0LDI1MS45NTggMTg3LjU0NywyNTEuOTU4IDE4Ny41NDcsMjk3LjU1MyAxNzkuNDQsMjk3LjU1MyAxNzkuNDQsMjc2LjQyMyAxNTguMjI2LDI3Ni40MjMgICAiLz48cGF0aCBkPSJNMjA5LjExNywyNTEuOTU4djEzLjc5MWg5LjU1MmM3LjMzOSwwLDEyLjIyMywxLjQ3NywxNC42Niw0LjQzNmMyLjQzOCwyLjk2LDMuNjUsNi41NTYsMy42NSwxMC43ODUgICAgYzAsNC43NDMtMS4yMDUsOC43MDMtMy42MDQsMTEuODVjLTIuNDA5LDMuMTU1LTcuNzMxLDQuNzMzLTE1Ljk4NSw0LjczM2gtMTYuMzc4di00NS41OTVIMjA5LjExN3ogTTIxOC4wNzIsMjkwLjUyMSAgICBjMy44MjgsMCw2LjM1OS0wLjgxMiw3LjU5MS0yLjQzOGMxLjIzMi0xLjYyNCwxLjkxNC0zLjQwNywyLjA0NS01LjM1OXYtMC40Mzh2LTAuNTQxYzAtMi40NjUtMC42MDctNC41NzUtMS44MDMtNi4zMzEgICAgYy0xLjIwNS0xLjc1NS0zLjgxLTIuNjMzLTcuODMzLTIuNjMzaC04Ljk1NXYxNy43NEgyMTguMDcyeiIgZmlsbD0iIzFEMUQxQiIvPjxwb2x5Z29uIGZpbGw9IiMxRDFEMUIiIHBvaW50cz0iMjU0LjczOSwyOTcuNTUzIDI0Ni42MzQsMjk3LjU1MyAyNDYuNjM0LDI1MS45NTggMjc1Ljg0MiwyNTEuOTU4IDI3NS44NDIsMjU4LjkyNSAgICAgMjU0LjczOSwyNTguOTI1ICAgIi8+PHBvbHlnb24gZmlsbD0iIzFEMUQxQiIgcG9pbnRzPSIzMTMuNDcyLDI1OS4xNzcgMzEzLjQ3MiwyNTEuOTU4IDMyMS41NzcsMjUxLjk1OCAzMjEuNTc3LDI5Ny41NTMgMzEzLjQ3MiwyOTcuNTUzICAgICAzMTMuNDcyLDI2OS4zMjYgMjkyLjI1NywyOTAuODEyIDI5Mi4yNTcsMjk3LjU1MyAyODQuMTUyLDI5Ny41NTMgMjg0LjE1MiwyNTEuOTU4IDI5Mi4yNTcsMjUxLjk1OCAyOTIuMjU3LDI4MC42NjIgICAiLz48cmVjdCBmaWxsPSIjRUM3MDA0IiBoZWlnaHQ9IjM0LjQ3NCIgd2lkdGg9IjQuNjA0IiB4PSI0NjUuMjc4IiB5PSIyNTYuMDIxIi8+PHBhdGggZD0iTTQ0NC4zMjYsMjY1LjYyOGMwLDIuMzYzLDEuOTA0LDQuMjU4LDQuMjU3LDQuMjU4YzIuMzU0LDAsNC4yNTktMS44OTUsNC4yNTktNC4yNTggICAgYzAtMi4zNTItMS45MDUtNC4yNTctNC4yNTktNC4yNTdDNDQ2LjIzLDI2MS4zNzEsNDQ0LjMyNiwyNjMuMjc2LDQ0NC4zMjYsMjY1LjYyOHoiIGZpbGw9IiNGRkZGRkYiLz48Zz48Zz48Zz48cGF0aCBkPSJNMTg1LjI2OSwyMjguNzc4Yy0xLjU2OCwwLTIuMTUyLTEuNTA4LTIuMTUyLTEuNTA4bC03LjYxNy0xNy4xOTRsNi40NTUtMTIuNjIgICAgICAgYzAsMCwwLjU4MS0xLjQ4OSwyLjE0Ny0xLjQ4OWMwLjg1LDAsMS4xMjEsMCwxLjIxOSwwdi0yLjk4M2gtMTAuNDA3djIuOTgzYzAsMCwwLjMwNCwwLDEuNDc0LDAgICAgICAgYzEuNTgsMCwxLjAxMywxLjQ4OSwxLjAxMywxLjQ4OWwtMy45NzMsNy45NmwtMy41MzEtNy45NmMwLDAtMC41NTktMS40ODksMS4wMDgtMS40ODljMS4xNTgsMCwxLjMyNiwwLDEuMzI2LDB2LTIuOTgzSDE2MC4yNCAgICAgICB2Mi45ODNjMC4wODMsMCwwLjM3MSwwLDEuMjAyLDBjMS41OTIsMCwyLjE2NiwxLjQ4OSwyLjE2NiwxLjQ4OWw2LjQ5MiwxNC42ODhsLTcuNTA1LDE1LjEyNmMwLDAtMC41NjcsMS41MDgtMi4xNTQsMS41MDggICAgICAgYy0xLjE0NiwwLTAuOTg4LDAtMC45ODgsMHYyLjk3NGg5LjkzOHYtMi45NzRjMCwwLTAuMTE1LDAtMS42OTUsMGMtMS41NzQsMC0wLjk5Ny0xLjUwOC0wLjk5Ny0xLjUwOGw1LjQyLTEwLjU3OWw0LjY4NSwxMC41NzkgICAgICAgYzAsMCwwLjU5NiwxLjUwOC0wLjk5MiwxLjUwOGMtMS41NzQsMC0xLjU0MywwLTEuNTQzLDB2Mi45NzRoMTEuOTk0di0yLjk3NEMxODYuMjYzLDIyOC43NzgsMTg2LjQxNywyMjguNzc4LDE4NS4yNjksMjI4Ljc3OCIgZmlsbD0iIzFEMUQxQiIvPjxwYXRoIGQ9Ik03OC44MTUsMTc1LjA4M2MxLjU4LDAsMi41MzYsMCwyLjUzNiwwdi0yLjk4OGMwLDAtMTAuNDYzLDAtMTIuNDYzLDAgICAgICAgYy0xLjk5NCwwLTE2Ljg3Ny0wLjQwMS0xNi44NzcsMTQuMzIzYzAsMS40OTQsMC0wLjQ0OCwwLDEuMDU2YzAsNy42NjEsNS4zOTUsMTIuOTMyLDkuMTA4LDEzLjg5NCAgICAgICBjLTIuMDU0LDAuNTIzLTMuNTQ4LDEuODc2LTQuMzM1LDMuMWMtMS43MzksMi43MzUtMi43ODUsNi4wODMtMy40NTUsMTEuMzczYy0wLjcxMiw1LjU0Ni0wLjY4MSwxMC44MzItMi43MTcsMTIuNTU5ICAgICAgIGMtMC44MzEsMC43LTIuMDI0LDAuOTYxLTMuMjY4LDAuODMxdjIuNTQ5YzEuMjM3LDAuNzMzLDIuOTY3LDEuMDkyLDQuNzgzLDAuNzQ3YzUuMzkzLTEuMDM3LDcuMzQ5LTUuNzgsNy45OTMtOS43MjkgICAgICAgYzEuMTY1LTcuMjQ2LDAuODI5LTE5LjEwNCw3LjYzMy0yMS43NTFsMi4zOTctMC4wMDV2MjYuMjI4YzAsMCwwLDEuNTA4LTEuNTczLDEuNTA4Yy0xLjU4MywwLTIuNTI2LDAtMi41MjYsMHYyLjk3NGgxNS4zICAgICAgIHYtMi45NzRjMCwwLTAuOTU2LDAtMi41MzYsMGMtMS41NzMsMC0xLjU3My0xLjUwOC0xLjU3My0xLjUwOHYtNTAuNzAyQzc3LjI0MiwxNzYuNTY4LDc3LjI0MiwxNzUuMDgzLDc4LjgxNSwxNzUuMDgzICAgICAgIEw3OC44MTUsMTc1LjA4M3ogTTcwLjE1LDE5OC4wNTRoLTEuNTczYy0yLjQzLDAtOS43NjksMC05Ljc2OS0xMC43MjlsMC4xNDYtNC4zMzNjMC03LjkwOSw3LjA4My03LjkwOSw5Ljg5Ni03LjkwOWgxLjMgICAgICAgVjE5OC4wNTQiIGZpbGw9IiNFNTI3MTMiLz48cGF0aCBkPSJNMTU4LjY1MiwyMTQuMzA2aDIuMDI2YzAuMDMzLTAuNzA5LDAuMDQtMS40MDYsMC4wNC0yLjEzNGMwLTExLjMxNy0zLjM3MS0yMC4wMzQtMTEuNDY2LTIwLjAzNCAgICAgICBjLTguMTA3LDAtMTEuNDc4LDguNzE3LTExLjQ3OCwyMC4wMzRjMCwxMS4zMTIsMy4zNzEsMjAuMTc4LDExLjQ3OCwyMC4xNzhjNi4xODQsMCw5LjQtNS4wNDcsMTAuODEzLTEyLjU5NmgtNS45NTggICAgICAgYy0wLjQ3Niw1LjcyOS0xLjUwMyw5LjMwOS00Ljg1NSw5LjMwOWMtNC4zMjEsMC00Ljk4My02LjE1My01LjEwMy0xNC43NTdoMTAuMjEzSDE1OC42NTJMMTU4LjY1MiwyMTQuMzA2eiBNMTQ5LjI1MiwxOTUuNDMgICAgICAgYzQuMzIxLDAsNC45NzksNS45OTUsNS4xMSwxNC42MDRoLTEwLjIxM0MxNDQuMjY5LDIwMS40MjQsMTQ0LjkzMSwxOTUuNDMsMTQ5LjI1MiwxOTUuNDMiIGZpbGw9IiMxRDFEMUIiLz48cGF0aCBkPSJNMTM1LjY0NiwyMjguNzc4Yy0xLjU3NiwwLTEuNTc2LTEuNTA4LTEuNTc2LTEuNTA4di01NS4xNzRoLTkuMjkxdjIuOTg4aDMuMTQ1djE5LjA0OCAgICAgICBjLTEuMjM3LTEuMTY3LTMuMTQ1LTEuODQ5LTUuMTU0LTEuODQ5Yy04LjExLDAtMTEuOTUyLDguNzIyLTExLjk1MiwyMC4wMzhjMCwxMS4zMDgsMy4wNiwyMC4wMjksMTEuMTU2LDIwLjAyOSAgICAgICBjMi4zMjcsMCw0LjQtMS4wMTgsNS45NS0zLjU5NWwxLjY5NywyLjk5OGg0LjQ0OWgzLjE1OXYtMi45NzRDMTM3LjIyOCwyMjguNzc4LDEzNy4yMjgsMjI4Ljc3OCwxMzUuNjQ2LDIyOC43NzggICAgICAgTDEzNS42NDYsMjI4Ljc3OHogTTEyMi4yOTcsMjI5LjA2M2MtNC42NzgsMC00LjgyLTcuMDg3LTQuODItMTYuNzQyYzAtOS42NjQsMC4xNDItMTYuNzQ3LDQuODItMTYuNzQ3ICAgICAgIGM2LjM2NiwwLDUuMjksNy4wODMsNS4yOSwxNi43NDdDMTI3LjU4NywyMjEuMTgzLDEyOC42NjMsMjI5LjA2MywxMjIuMjk3LDIyOS4wNjMiIGZpbGw9IiMxRDFEMUIiLz48cGF0aCBkPSJNMTA4LjUyNywyMjguNzc4Yy0xLjU2OCwwLTEuNTY4LTEuNTA4LTEuNTY4LTEuNTA4czAtMjUuMjc2LDAtMjcuMTI1YzAtMi42OC0xLjEyNS04LjA1OS04LjIwMy04LjA1OSAgICAgICBjLTQuMTgsMC02LjAwMSwyLjkwNC02LjYyOSwzLjY3NGwtMS44MjgtMy4yMzFIODIuODR2Mi45ODRoMy4xNHYzMS43NTZjMCwwLDAsMS41MDgtMS41NzksMS41MDhjLTEuNTYyLDAtMS41NjIsMC0xLjU2MiwwICAgICAgIHYyLjk3NGgxMi40NDl2LTIuOTc0YzAsMCwwLDAtMS41ODgsMGMtMS41NzUsMC0xLjU3NS0xLjUwOC0xLjU3NS0xLjUwOHYtMjMuMjAzYzAtMi43NSwwLjI5Ny00LjA2MiwwLjQ0Ni00LjU3ICAgICAgIGMwLjU0OS0xLjU1LDEuNzc3LTMuNTMsNC40NjEtMy41M2MzLjE0OSwwLDMuNzU2LDIuODgxLDMuNzU2LDQuMzE4YzAsMC44NSwwLDI2Ljk4NSwwLDI2Ljk4NXMwLDEuNTA4LTEuNTYyLDEuNTA4ICAgICAgIGMtMS41NzMsMC0xLjU3MywwLTEuNTczLDB2Mi45NzRoMTIuNDV2LTIuOTc0QzExMC4xMDMsMjI4Ljc3OCwxMTAuMTAzLDIyOC43NzgsMTA4LjUyNywyMjguNzc4IiBmaWxsPSIjMUQxRDFCIi8+PC9nPjwvZz48L2c+PC9nPjwvZz48L3N2Zz4="/>
                <p>Яндекс.Деньги</p>

                <form class="ui form" method="post" action="/home/balance/yandexmoney">
                    @csrf

                    <div class="field">
                        <label>Сумма пополнения, руб.:</label>
                        <div class="ui action input">
                            <input type="text" type="number" name="sum" value="2">
                            <button class="ui button">Пополнить</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="item" style="padding: .78571429em .92857143em; max-height: 500px; overflow: hidden auto;">
            <?php $transactions = App\Transactions::where('user_id', Auth::user()->id)->orderBy('created_at', 'desc')->orderBy('id', 'desc')->get(); ?>
            <table class="ui celled table">
                <thead>
                    <tr>
                        <th class="center aligned" style="width:0">Дата / время</th>
                        <th class="center aligned">Сумма</th>
                        <th>Примечание</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction) { ?>
                    <tr>
                        <td style="white-space:nowrap"><?php print date('d.m.Y H:i:s', strtotime($transaction->created_at)); ?></td>
                        <td class="center aligned <?php print $transaction->amount > 0 ? 'positive' : 'negative'; ?>"><?php print ($transaction->amount > 0 ? '+' : '') . $transaction->amount; ?> руб.</td>
                        <td><?php print $transaction->description; ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

        </div>

        <style type="text/css">
            .payment_item {
                border: 1px solid #ccc;
                -webkit-border-radius: 4px;
                border-radius: 4px;
                padding: 16px 20px;
                display: block;
                text-decoration: none !important;
                text-align: center;
                font-size: 0;
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                -ms-flex-wrap: wrap;
                flex-wrap: wrap;
                width: 320px;
            }

            .payment_item label {
                font-size: 14px !important;
                line-height: 24px !important;
            }

            .payment_item img {
                width: 100px;
                display: inline-block;
                margin: 0px 10px;
                min-height: 100px;
            }

            .payment_item p {
                display: block;
                font-size: 14px;
                text-align: center;
                width: 100%;
            }
        </style>
    </body>
</html>
