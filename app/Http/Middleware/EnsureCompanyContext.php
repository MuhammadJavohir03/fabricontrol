<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user) {
            return $next($request);
        }

        // super_admin — companiya tanlamagan bo'lsa, companiyalar ro'yxatiga yuboriladi
        if ($user->role === 'super_admin') {
            $companyId = session('active_company_id');

            if (! $companyId || ! Company::find($companyId)) {
                return redirect()->route('admin.companies.index');
            }

            return $next($request);
        }

        // Oddiy foydalanuvchi (admin/chevar/client) — o'z companiyasiga bog'liq
        if (! $user->company_id) {
            abort(403, "Sizga hech qanday companiya biriktirilmagan.");
        }

        $company = Company::find($user->company_id);

        if (! $company || ! $company->faolmi()) {
            return redirect()->route('admin.companies.blocked');
        }

        return $next($request);
    }
}