<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class WelcomeText extends Widget
{
    protected static string $view = 'filament.widgets.welcome-text';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static bool $isLazy = false;
    
    public function getColumnSpan(): int | string | array
    {
        return 'full';
    }
}