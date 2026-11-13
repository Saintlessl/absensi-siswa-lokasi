<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoleManagementController extends Controller
{
    public function __construct()
    {
        // Enforce granular permission: only superadmin or user with 'pengaturan.role'
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (!$user || (!$user->hasRole('superadmin') && !$user->hasPermission('pengaturan.role'))) {
                abort(403, 'Akses ditolak: Anda tidak memiliki wewenang untuk mengelola Role & Permission.');
            }
            return $next($request);
        });
    }

    /**
     * Display role list
     */
    public function index(Request $request)
    {
        $query = Role::withCount(['users', 'permissions']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('display_name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $roles = $query->orderByDesc('is_system')->orderBy('id')->paginate(15)->withQueryString();
        $totalPermissions = Permission::count();

        return view('settings.roles.index', compact('roles', 'totalPermissions'));
    }

    /**
     * Store new role
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:50|unique:roles,name|alpha_dash',
            'display_name' => 'required|string|max:100',
            'description'  => 'nullable|string|max:500',
            'data_scope'   => 'required|in:all,department,own,assigned',
        ], [
            'name.unique'     => 'Identifier role (nama sistem) sudah terdaftar.',
            'name.alpha_dash' => 'Identifier role hanya boleh berisi huruf, angka, tanda hubung (-) dan garis bawah (_).',
        ]);

        $role = Role::create([
            'name'         => Str::lower($request->name),
            'display_name' => $request->display_name,
            'description'  => $request->description,
            'data_scope'   => $request->data_scope,
            'is_system'    => false,
            'status'       => 'active',
        ]);

        AuditLog::log('role.create', $role, null, $role->toArray(), "Menambahkan role baru: {$role->display_name}");

        return redirect()->route('settings.roles.index')->with('success', "Role '{$role->display_name}' berhasil ditambahkan.");
    }

    /**
     * Update role details
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'display_name' => 'required|string|max:100',
            'description'  => 'nullable|string|max:500',
            'data_scope'   => 'required|in:all,department,own,assigned',
            'status'       => 'required|in:active,inactive',
        ]);

        // Protect Superadmin core configuration
        if ($role->name === 'superadmin') {
            if ($request->status === 'inactive') {
                return back()->with('error', 'Role Super Administrator tidak boleh dinonaktifkan.');
            }
        }

        $oldValues = $role->toArray();

        $role->update([
            'display_name' => $request->display_name,
            'description'  => $request->description,
            'data_scope'   => $request->data_scope,
            'status'       => $role->is_system ? 'active' : $request->status,
        ]);

        AuditLog::log('role.update', $role, $oldValues, $role->toArray(), "Memperbarui konfigurasi role {$role->display_name}");

        return back()->with('success', "Role '{$role->display_name}' berhasil diperbarui.");
    }

    /**
     * Show permission management form for a specific role
     */
    public function permissions(Role $role)
    {
        $modules = Permission::all()->groupBy('module');
        $activePermissionIds = $role->permissions()->pluck('permissions.id')->toArray();

        return view('settings.roles.permissions', compact('role', 'modules', 'activePermissionIds'));
    }

    /**
     * Update permissions for a role
     */
    public function updatePermissions(Request $request, Role $role)
    {
        // 1. Superadmin Protection: Superadmin must always retain full permissions
        if ($role->name === 'superadmin') {
            $allPermissions = Permission::pluck('id')->toArray();
            $role->permissions()->sync($allPermissions);
            $role->flushPermissionCache();

            return back()->with('success', 'Role Superadmin dilindungi dan otomatis mempertahankan seluruh hak akses sistem.');
        }

        $permissionIds = $request->input('permissions', []);
        $permissionIds = is_array($permissionIds) ? array_map('intval', $permissionIds) : [];

        $oldPermissionIds = $role->permissions()->pluck('permissions.id')->toArray();

        DB::transaction(function () use ($role, $permissionIds) {
            $role->permissions()->sync($permissionIds);
            $role->flushPermissionCache();
        });

        // Instant cache flush for all cached instances
        $role->flushPermissionCache();

        AuditLog::log(
            'role.permission.update',
            $role,
            ['permission_ids' => $oldPermissionIds],
            ['permission_ids' => $permissionIds],
            "Memperbarui granular permission role {$role->display_name} (Total: " . count($permissionIds) . " permission aktif)"
        );

        return back()->with('success', "Hak akses permission untuk role '{$role->display_name}' berhasil disimpan dan langsung aktif.");
    }

    /**
     * Delete custom role
     */
    public function destroy(Role $role)
    {
        // 1. Cannot delete system core roles
        if ($role->is_system || in_array($role->name, ['superadmin', 'admin', 'guru', 'siswa', 'orangtua', 'kaprog'])) {
            return back()->with('error', 'Role sistem ini merupakan role inti MySkanic dan tidak boleh dihapus.');
        }

        // 2. Cannot delete role if assigned to users
        $userCount = $role->users()->count() + User::where('role', $role->name)->count();
        if ($userCount > 0) {
            return back()->with('error', "Role '{$role->display_name}' masih digunakan oleh {$userCount} pengguna. Harap pindahkan pengguna ke role lain terlebih dahulu.");
        }

        $oldValues = $role->toArray();
        $roleName = $role->display_name;
        $role->delete();

        AuditLog::log('role.delete', null, $oldValues, null, "Menghapus role kustom: {$roleName}");

        return redirect()->route('settings.roles.index')->with('success', "Role '{$roleName}' berhasil dihapus.");
    }
}
