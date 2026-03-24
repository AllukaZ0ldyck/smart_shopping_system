<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HitPayRedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $query = array_filter(
            array_merge(['gateway' => 'hitpay'], $request->query()),
            fn ($value) => $value !== null && $value !== ''
        );

        $posUrl = rtrim($request->getSchemeAndHttpHost(), '/') . '/#/app/pos';

        if (! empty($query)) {
            $posUrl .= '?' . http_build_query($query);
        }

        return redirect()->away($posUrl);
    }
}
