<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SchoolSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SchoolSettingController extends Controller
{
    /**
     * Ensure current user has permission to manage school settings and branding.
     */
    private function authorizeAdmin(): void
    {
        $user = auth()->user();
        if (!$user) {
            abort(401);
        }
        if (!$user->hasRole(['superadmin', 'admin']) && !$user->hasPermission('pengaturan.jam_sekolah')) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah pengaturan sekolah.');
        }
    }

    /**
     * Show the school settings form (identitas sekolah, logo, favicon, jam masuk / jam pulang).
     */
    public function index()
    {
        $this->authorizeAdmin();

        $settings = SchoolSetting::all()->pluck('value', 'key');

        return view('settings.index', [
            'jamMasuk'         => $settings['jam_masuk'] ?? '07:15',
            'jamPulang'        => $settings['jam_pulang'] ?? '14:55',
            'namaSekolah'      => $settings['nama_sekolah'] ?? 'SMK Negri 1 Ciomas',
            'logoUrl'          => SchoolSetting::getLogoUrl(),
            'faviconUrl'       => SchoolSetting::getFaviconUrl(),
            'hasCustomLogo'    => SchoolSetting::hasCustomLogo(),
            'hasCustomFavicon' => SchoolSetting::hasCustomFavicon(),
        ]);
    }

    /**
     * Persist school settings and branding assets.
     */
    public function update(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'jam_masuk'       => 'required|date_format:H:i',
            'jam_pulang'      => 'required|date_format:H:i|after:jam_masuk',
            'nama_sekolah'    => 'nullable|string|max:150',
            'logo'            => 'nullable|file|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'favicon'         => [
                'nullable',
                'file',
                'max:1024',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['ico', 'png', 'jpg', 'jpeg', 'svg', 'webp'])) {
                        $fail('Format favicon harus berupa salah satu dari: ico, png, jpg, jpeg, svg, atau webp.');
                    }
                },
            ],
            'remove_logo'     => 'nullable|boolean',
            'remove_favicon'  => 'nullable|boolean',
        ], [
            'jam_pulang.after' => 'Jam pulang harus lebih akhir dari jam masuk.',
            'logo.image'       => 'File logo harus berupa gambar yang valid.',
            'logo.mimes'       => 'Format logo harus berupa JPG, PNG, WEBP, atau SVG.',
            'logo.max'         => 'Ukuran logo maksimal 2MB.',
            'favicon.max'      => 'Ukuran favicon maksimal 1MB.',
        ]);

        $data = [
            'jam_masuk'  => $request->jam_masuk,
            'jam_pulang' => $request->jam_pulang,
        ];

        if ($request->filled('nama_sekolah')) {
            $data['nama_sekolah'] = $request->nama_sekolah;
        }

        foreach ($data as $key => $value) {
            SchoolSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // Handle Remove Logo
        if ($request->boolean('remove_logo')) {
            $currentLogo = SchoolSetting::query()->where('key', 'app_logo')->value('value');
            if ($currentLogo && Storage::disk('public')->exists($currentLogo)) {
                Storage::disk('public')->delete($currentLogo);
            }
            SchoolSetting::query()->where('key', 'app_logo')->delete();
        } elseif ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $currentLogo = SchoolSetting::query()->where('key', 'app_logo')->value('value');
            if ($currentLogo && Storage::disk('public')->exists($currentLogo)) {
                Storage::disk('public')->delete($currentLogo);
            }

            $logoFile = $request->file('logo');
            $logoName = 'logo_' . time() . '.' . $logoFile->getClientOriginalExtension();
            $path = $logoFile->storeAs('branding', $logoName, 'public');
            SchoolSetting::updateOrCreate(['key' => 'app_logo'], ['value' => $path]);
        }

        // Handle Remove Favicon
        if ($request->boolean('remove_favicon')) {
            $currentFavicon = SchoolSetting::query()->where('key', 'app_favicon')->value('value');
            if ($currentFavicon && Storage::disk('public')->exists($currentFavicon)) {
                Storage::disk('public')->delete($currentFavicon);
            }
            SchoolSetting::query()->where('key', 'app_favicon')->delete();
        } elseif ($request->hasFile('favicon') && $request->file('favicon')->isValid()) {
            $currentFavicon = SchoolSetting::query()->where('key', 'app_favicon')->value('value');
            if ($currentFavicon && Storage::disk('public')->exists($currentFavicon)) {
                Storage::disk('public')->delete($currentFavicon);
            }

            $favFile = $request->file('favicon');
            $favName = 'favicon_' . time() . '.' . $favFile->getClientOriginalExtension();
            $path = $favFile->storeAs('branding', $favName, 'public');
            SchoolSetting::updateOrCreate(['key' => 'app_favicon'], ['value' => $path]);
        }

        SchoolSetting::clearSettingCache();

        if (class_exists(AuditLog::class) && auth()->check()) {
            try {
                AuditLog::log('settings.school.update', auth()->user(), null, null, 'Memperbarui identitas, logo, dan pengaturan jam sekolah');
            } catch (\Throwable $e) {
                // Ignore audit log failure if not critical
            }
        }

        return back()->with('success', 'Pengaturan identitas sekolah, logo, favicon, dan jam presensi berhasil diperbarui.');
    }
}
