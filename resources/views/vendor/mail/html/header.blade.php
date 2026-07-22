@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            @if (trim($slot) === 'Laravel')
                <img src="https://new.sobhesahel.com/asset/img/logo.png" class="logo" alt="Sobhe Sahel Logo">
            @else
                <img src="https://new.sobhesahel.com/asset/img/logo.png" class="logo" style="width:auto; max-width: unset" alt="Sobhe Sahel Logo">
            @endif
        </a>
    </td>
</tr>
