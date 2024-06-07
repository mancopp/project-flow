<?php

namespace App\Helper;

use Symfony\Component\Security\Core\Security;

define("DEFAULT_SIDEBAR_STYLES", [
  'selected' => 'bg-cyan-700 hover:bg-cyan-600',
  'default' => 'bg-cyan-900 hover:bg-cyan-700',
  'container' => 'bg-cyan-800'
]);

class SidebarHelper
{
    public function generateSidebar(string $title, string $subtitle, array $nav_buttons = [], array $styles = DEFAULT_SIDEBAR_STYLES): array
    {
        return [
            'styles' => $styles,
            'title' => $title,
            'subtitle' => $subtitle,
            'nav_buttons' => $nav_buttons,
        ];
    }
}
