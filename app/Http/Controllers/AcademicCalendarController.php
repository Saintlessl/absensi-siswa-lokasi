<?php

namespace App\Http\Controllers;

use App\Models\AcademicCalendarDay;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AcademicCalendarController extends Controller
{
    /**
     * List non-effective days for a month, with a form to add new ones.
     */
    public function index(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        $start = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $days = AcademicCalendarDay::whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->orderBy('tanggal')
            ->get();

        return view('academic_calendar.index', [
            'days' => $days,
            'bulan' => $bulan,
            'tahun' => $tahun,
        ]);
    }

    /**
     * Add a non-effective day.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal'       => 'required|date',
            'nama_kegiatan' => 'required|string|max:255',
        ]);

        AcademicCalendarDay::firstOrCreate(
            ['tanggal' => $request->tanggal],
            ['nama_kegiatan' => $request->nama_kegiatan, 'is_active' => true]
        );

        return back()->with('success', 'Hari non-efektif ditambahkan ke kalender akademik.');
    }

    /**
     * Toggle active state (a day can be marked non-effective again later).
     */
    public function toggle($id)
    {
        $day = AcademicCalendarDay::findOrFail($id);
        $day->update(['is_active' => !$day->is_active]);

        return back()->with('success', $day->is_active
            ? 'Hari non-efektif diaktifkan kembali.'
            : 'Hari non-efektif dinonaktifkan.');
    }

    /**
     * Remove a non-effective day.
     */
    public function destroy($id)
    {
        AcademicCalendarDay::findOrFail($id)->delete();

        return back()->with('success', 'Hari non-efektif dihapus dari kalender.');
    }
}
