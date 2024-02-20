<tr>
    <td class="header">
        <table class="inner-body" style="background-color: #e5b84b;" align="center" width="570" cellpading="0" cellspacing="0" role="presentation">
            <tbody>
                <tr style="max-width: 100vw;">
                    <td style="padding: 20px;">
                        <img src="{{url('images/brand.svg')}}" height="32px"/>
                    </td>
                    <td style="text-align:right; box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; padding: 20px;">
                        <a href="{{ $url }}" style="display: inline-block;">
                        @if (trim($slot) === 'Laravel')
                        <img src="https://laravel.com/img/notification-logo.png" class="logo" alt="Laravel Logo">
                        @else
                        {{ $slot }}
                        @endif
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
    </td>
</tr>
