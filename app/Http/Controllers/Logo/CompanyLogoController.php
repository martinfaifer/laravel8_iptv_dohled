<?php

namespace App\Http\Controllers\Logo;

use App\Http\Controllers\Controller;
use App\Models\CompanyLogo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\NotificationTrait;

class CompanyLogoController extends Controller
{
    use NotificationTrait;

    public function index()
    {
        return CompanyLogo::all();
    }

    public function store(Request $request): array
    {

        if (CompanyLogo::first()) {
            return $this->frontend_notification("error", "Nejdříve odeberte původní logo!");
        }

        $validation = Validator::make($request->all(), [
            'photo' => ['logo']
        ]);

        if ($validation->fails()) {
            return $this->frontend_notification("error", "error", "Neplatný formát!!");
        }

        $file = $request->file('logo');
        $name = '/logos/' . $file->extension();
        $file->storePubliclyAs('public', $name);

        CompanyLogo::create([
            'logo_path' => '/storage/' . $name,
        ]);

        return $this->frontend_notification("success", "success", "Logo uloženo!");
    }

    public function delete(): array
    {
        try {
            $logo = CompanyLogo::first();
            unlink(str_replace("/storage/", "storage/", $logo->logo_path));
            $logo->delete();

            return $this->frontend_notification("success", "Odebráno!");
        } catch (\Throwable $th) {
            return $this->frontend_notification("error", "Nepodařilo se odebrat!");
        }
    }
}
