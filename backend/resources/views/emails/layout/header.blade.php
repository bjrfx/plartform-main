<table style="border: 0; width: 100%;">
@if(isset($merchantLogo) && $merchantLogo)
    <!-- To prevent Outlook from stretching image when forwarding the email -->
    <tr>
        <td style="width: 225px;">
            <div class="logo">
                <img src="{{ $merchantLogo }}" alt="{{ $merchantName }}" width="225" style="display: block; max-width: 225px; width: 100%; height: auto; border: 0;">
            </div>
        </td>
        <td>&nbsp;</td>
    </tr>
@endif
    <tr>
        <td @if(isset($merchantLogo) && $merchantLogo) colspan="2" @endif >
            <div class="header-title">
                <h2>{{ $subject }}</h2>
            </div>
        </td>
    </tr>
</table>
