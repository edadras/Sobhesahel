<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class CmsChangesWidget extends Widget
{
    protected static string $view = 'filament.widgets.cms-changes-widget';

    protected static ?int $sort = 3;

    protected static ?string $heading = 'Blog Posts';

    protected static bool $isLazy = false;
}

