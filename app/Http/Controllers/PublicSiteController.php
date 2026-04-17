<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class PublicSiteController extends Controller
{
    public function home(): View
    {
        return view('public.home');
    }

    public function enotourism(): View
    {
        return view('public.enotourism');
    }

    public function features(): View
    {
        return view('public.features');
    }

    public function widgets(): View
    {
        return view('public.widgets');
    }

    public function integrations(): View
    {
        return view('public.integrations');
    }

    public function services(): View
    {
        return view('public.services');
    }
}
