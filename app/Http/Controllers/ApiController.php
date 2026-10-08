<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class ApiController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Respons sukses standar JSON ({success, message, data}).
     */
    protected function ok(mixed $data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    /**
     * Alias lama — dipertahankan agar kode lain tetap kompatibel.
     */
    protected function successResponse(mixed $data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return $this->ok($data, $message, $code);
    }

    /**
     * Respons error standar JSON.
     */
    protected function fail(string $message = 'Error', int $code = 400, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $code);
    }

    /**
     * Alias lama — dipertahankan agar kode lain tetap kompatibel.
     */
    protected function errorResponse(string $message = 'Error', int $code = 400, mixed $errors = null): JsonResponse
    {
        return $this->fail($message, $code, $errors);
    }

    /**
     * Batasi akses berdasarkan peran (RBAC). Super-admin selalu lolos.
     *
     * @param  array<int, string>  $roles
     */
    protected function requireRole(Request $request, array $roles): void
    {
        $user = $request->user();

        abort_unless($user && $user->is_active, 401, 'Unauthenticated.');
        abort_if($user->role !== 'super-admin' && ! in_array($user->role, $roles, true), 403,
            'Akses ditolak: peran Anda tidak memiliki izin.');
    }

    /**
     * Operator pencarian teks yang kompatibel lintas driver database
     * (PostgreSQL memakai `ilike`, MySQL/SQLite memakai `like`).
     */
    protected function likeOperator(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }

    /**
     * Tulis jejak audit permanen (UU PDP) — dipakai hampir semua aksi sensitif.
     */
    protected function audit(
        Request $request,
        string $action,
        string $auditableType,
        ?int $auditableId = null,
        ?int $patientId = null,
        ?string $notes = null,
    ): AuditLog {
        $user = $request->user();

        return AuditLog::create([
            'user_id'         => $user?->id,
            'role_snapshot'   => $user?->role ?? 'guest',
            'action'          => $action,
            'auditable_type'  => $auditableType,
            'auditable_id'    => $auditableId,
            'patient_id'      => $patientId,
            'old_values'      => null,
            'new_values'      => null,
            'ip_address'      => $request->ip(),
            'user_agent'      => substr((string) $request->userAgent(), 0, 500),
            'url'             => substr((string) $request->fullUrl(), 0, 255),
            'notes'           => $notes,
        ]);
    }

    /**
     * Format kode tanggal baku (YYYYMMDD) untuk kode dokumen.
     */
    protected function dateCode(): string
    {
        return now()->format('Ymd');
    }
}
