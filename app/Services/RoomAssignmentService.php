<?php
namespace App\Services;

use App\Models\RoomAssignment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RoomAssignmentService
{
    public function create(array $data): RoomAssignment
    {
        return DB::transaction(function () use ($data) {
            $this->assertNoConflict($data);
            return RoomAssignment::create($data);
        }, 3);
    }

    public function assertNoConflict(array $data, ?int $ignoreId = null): void
    {
        $base = RoomAssignment::query()->where('status', 'active')->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->whereDate('starts_on', '<=', $data['ends_on'] ?? '9999-12-31')
            ->where(function ($q) use ($data) { $q->whereNull('ends_on')->orWhereDate('ends_on', '>=', $data['starts_on']); });
        if (!empty($data['day_of_week'])) $base->where('day_of_week', $data['day_of_week']);
        if (!empty($data['start_time']) && !empty($data['end_time'])) {
            $base->where(function ($q) use ($data) { $q->whereNull('start_time')->orWhere(function ($slot) use ($data) { $slot->where('start_time', '<', $data['end_time'])->where('end_time', '>', $data['start_time']); }); });
        }
        if ((clone $base)->where('room_id', $data['room_id'])->exists()) throw ValidationException::withMessages(['room_id' => 'Ruangan sudah dialokasikan pada periode atau waktu yang bertabrakan.']);
        if ((clone $base)->where('kelas_id', $data['kelas_id'])->exists()) throw ValidationException::withMessages(['kelas_id' => 'Rombel sudah dialokasikan ke ruangan lain pada periode atau waktu yang bertabrakan.']);
    }

    public function activeForToken(string $token, Carbon $at): ?RoomAssignment
    {
        $day = $at->locale('en')->dayName;
        return RoomAssignment::with(['room', 'kelas'])->whereHas('room', fn ($q) => $q->where('qr_token', $token)->where('qr_is_active', true)->where('is_active', true))
            ->where('status', 'active')->whereDate('starts_on', '<=', $at)->where(fn ($q) => $q->whereNull('ends_on')->orWhereDate('ends_on', '>=', $at))
            ->where(fn ($q) => $q->whereNull('day_of_week')->orWhere('day_of_week', $day))
            ->where(fn ($q) => $q->whereNull('start_time')->orWhere(fn ($slot) => $slot->where('start_time', '<=', $at->format('H:i:s'))->where('end_time', '>=', $at->format('H:i:s'))))->first();
    }
}
