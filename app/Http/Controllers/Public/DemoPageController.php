<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DemoPageController extends Controller
{
    use ResolvesLocale;

    public function index(string $locale): View
    {
        $panelPath  = config('cms.panel_path', 'panel');
        $demoEmail  = config('cms.demo_login_email', '');
        $demoPassword = config('cms.demo_login_password', '');

        $headerMenu = $this->menuItems('header');
        $footerMenu = $this->menuItems('footer');

        $theme = $this->currentTheme();

        return view("themes.{$theme}.demo", array_merge(
            $this->themeData($locale),
            compact('panelPath', 'demoEmail', 'demoPassword', 'headerMenu', 'footerMenu'),
        ));
    }
}
