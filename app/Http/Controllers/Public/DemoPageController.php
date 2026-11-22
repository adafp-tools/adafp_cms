<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DemoPageController extends Controller
{
    use ResolvesLocale;

    public function redirect(): RedirectResponse
    {
        // Redirect /demo to /{default_locale}/demo
        return redirect()->route('public.demo', ['locale' => $this->defaultLocale()]);
    }

    public function show(string $locale): View
    {
        // This page only exists in demo mode.
        if (config('cms.instance') !== 'demo') {
            throw new NotFoundHttpException();
        }

        $headerMenu = $this->menuItems('header');
        $footerMenu = $this->menuItems('footer');
        $panelUrl   = route('panel.login');
        $panelPath  = '/' . config('cms.panel_path', 'panel');

        // Credentials are intentionally public on the demo site so visitors
        // can explore the admin panel.
        $demoEmail    = config('cms.demo_admin_email');
        $demoPassword = config('cms.demo_admin_password');

        return view('public.demo', array_merge(
            $this->themeData($locale),
            [
                'pageTitle'   => __('demo.page_title'),
                'headerMenu'  => $headerMenu,
                'footerMenu'  => $footerMenu,
                'panelUrl'    => $panelUrl,
                'panelPath'   => $panelPath,
                'demoEmail'   => $demoEmail,
                'demoPassword' => $demoPassword,
            ]
        ));
    }
}
