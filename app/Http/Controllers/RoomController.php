<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Services\RoomAssignmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $query = Room::with(['assignments' => function ($q) {
            $today = Carbon::today()->toDateString();
            $q->where(function ($sub) use ($today) {
                $sub->whereNull('starts_on')->orWhere('starts_on', '<=', $today);
            })->where(function ($sub) use ($today) {
                $sub->whereNull('ends_on')->orWhere('ends_on', '>=', $today);
            })->with('kelas.jurusan');
        }]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('code', 'LIKE', '%' . $search . '%')
                    ->orWhere('building', 'LIKE', '%' . $search . '%');
            });
        }

        if ($request->filled('building')) {
            $query->where('building', $request->building);
        }

        $rooms = $query->orderBy('name')->paginate(12)->withQueryString();
        $buildings = Room::whereNotNull('building')->distinct()->pluck('building');
        $totalRooms = Room::count();
        $activeRooms = Room::where('is_active', true)->count();
        $labRooms = Room::where('type', 'LIKE', '%lab%')->orWhere('name', 'LIKE', '%lab%')->count();

        return view('rooms.index', compact('rooms', 'buildings', 'totalRooms', 'activeRooms', 'labRooms') + [
            'search'   => $request->search ?? '',
            'building' => $request->building ?? '',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:50|unique:rooms,code',
            'building'    => 'nullable|string|max:255',
            'capacity'    => 'nullable|integer|min:1',
            'type'        => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ]);

        $data['qr_token'] = Str::random(64);
        $data['qr_is_active'] = true;
        $data['is_active'] = $request->boolean('is_active', true);

        Room::create($data);

        return back()->with('success', 'Ruangan fisik dan QR Code permanen berhasil ditambahkan.');
    }

    public function update(Request $request, Room $room)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:50|unique:rooms,code,' . $room->id,
            'building'    => 'nullable|string|max:255',
            'capacity'    => 'nullable|integer|min:1',
            'type'        => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $room->update($data);

        return back()->with('success', "Data ruangan {$room->name} berhasil diperbarui.");
    }

    public function destroy(Room $room)
    {
        if ($room->assignments()->exists()) {
            return back()->with('error', "Ruangan {$room->name} memiliki riwayat alokasi kelas dan tidak dapat dihapus.");
        }

        $room->delete();
        return back()->with('success', "Ruangan berhasil dihapus.");
    }

    public function regenerate(Room $room)
    {
        $room->update([
            'qr_token'     => Str::random(64),
            'qr_is_active' => true,
        ]);

        return back()->with('success', "QR Code untuk {$room->name} berhasil diregenerasi. Token lama telah dibatalkan.");
    }

    public function assignments(Request $request)
    {
        $user = Auth::user();
        $query = RoomAssignment::with(['room', 'kelas.jurusan'])->latest();

        // KAPROG scoping
        if ($user->role === 'kaprog' && $user->jurusan_id) {
            $query->whereHas('kelas', function ($q) use ($user) {
                $q->where('jurusan_id', $user->jurusan_id);
            });
        }

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $assignments = $query->paginate(20)->withQueryString();
        $rooms = Room::active()->orderBy('name')->get();

        $kelasQuery = Kelas::with('jurusan')->where('is_active', true)->orderBy('nama_kelas');
        if ($user->role === 'kaprog' && $user->jurusan_id) {
            $kelasQuery->where('jurusan_id', $user->jurusan_id);
        }
        $kelas = $kelasQuery->get();

        return view('rooms.assignments', [
            'assignments' => $assignments,
            'rooms'       => $rooms,
            'kelas'       => $kelas,
            'room_id'     => $request->room_id ?? '',
            'kelas_id'    => $request->kelas_id ?? '',
        ]);
    }

    public function storeAssignment(Request $request, RoomAssignmentService $service)
    {
        $data = $request->validate([
            'room_id'      => 'required|exists:rooms,id',
            'kelas_id'     => 'required|exists:kelas,id',
            'day_of_week'  => 'nullable|string|max:20',
            'start_time'   => 'nullable|date_format:H:i',
            'end_time'     => 'nullable|date_format:H:i|after:start_time',
            'starts_on'    => 'required|date',
            'ends_on'      => 'nullable|date|after_or_equal:starts_on',
            'tahun_ajaran' => 'required|string|max:20',
        ]);

        $kelas = Kelas::with('jurusan')->findOrFail($data['kelas_id']);
        $user = Auth::user();

        abort_unless(
            in_array($user->role, ['superadmin', 'admin', 'kaprog']) &&
            ($user->role !== 'kaprog' || $user->jurusan_id === $kelas->jurusan_id),
            403,
            'Anda tidak memiliki wewenang untuk rombel ini.'
        );

        try {
            $service->create($data + [
                'status'     => 'active',
                'created_by' => $user->id,
            ]);
            return back()->with('success', 'Alokasi ruangan berhasil disimpan.');
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroyAssignment(RoomAssignment $assignment)
    {
        $user = Auth::user();
        if ($user->role === 'kaprog' && $user->jurusan_id && $assignment->kelas?->jurusan_id !== $user->jurusan_id) {
            abort(403);
        }

        $assignment->delete();
        return back()->with('success', 'Alokasi ruangan berhasil dihapus.');
    }
}
