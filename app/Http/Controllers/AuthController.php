<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends ApiController
{
    /**
     * POST /api/auth/login — login token (Sanctum personal access token).
     *
     * Body: {email, password, deviceName?}
     */
    public function login(Request $request): JsonResponse
    {
        $v = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'deviceName' => 'nullable|string|max:80',
        ]);

        /** @var User|null $user */
        $user = User::where('email', $v['email'])->first();

        if (! $user || ! Hash::check($v['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        abort_unless($user->is_active, 403, 'Akun Anda tidak aktif. Hubungi admin rumah sakit.');

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        $token = $user->createToken($v['deviceName'] ?? 'healthhub-api')->plainTextToken;

        $this->audit($request, 'AUTH_LOGIN', 'User', $user->id, null, "Login {$user->email} ({$user->role})");

        return $this->ok([
            'token' => $token,
            'tokenType' => 'Bearer',
            'user' => $this->profile($user),
        ], 'Login berhasil. Gunakan token ini pada header Authorization: Bearer <token>.');
    }

    /**
     * GET /api/auth/me — profil pengguna yang sedang login.
     */
    public function me(Request $request): JsonResponse
    {
        return $this->ok($this->profile($request->user()));
    }

    /**
     * POST /api/auth/logout — hapus token yang sedang dipakai.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $user?->currentAccessToken()?->delete();

        $this->audit($request, 'AUTH_LOGOUT', 'User', $user?->id, null, "Logout {$user?->email}");

        return $this->ok(null, 'Logout berhasil. Token telah dihapus.');
    }

    /**
     * POST /api/auth/register — pendaftaran pasien baru (opsional, publik).
     */
    public function register(Request $request): JsonResponse
    {
        $v = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'nik' => 'required|string|size:16',
            'birthDate' => 'required|date',
            'gender' => 'required|in:L,P',
            'address' => 'required|string',
            'city' => 'required|string',
        ]);

        $user = DB::transaction(function () use ($v) {
            /** @var User $user */
            $user = User::create([
                'name' => $v['name'],
                'email' => $v['email'],
                'password' => $v['password'],
                'phone' => $v['phone'],
                'role' => 'patient',
                'is_active' => true,
            ]);

            Patient::create([
                'user_id' => $user->id,
                'medical_record_number' => 'RM-' . now()->format('Ym') . '-' . str_pad((string) (Patient::count() + 1), 3, '0', STR_PAD_LEFT),
                'nik' => $v['nik'],
                'nik_hash' => hash('sha256', $v['nik']),
                'full_name' => $v['name'],
                'birth_date' => $v['birthDate'],
                'gender' => $v['gender'],
                'phone' => $v['phone'],
                'address' => $v['address'],
                'city' => $v['city'],
                'consent_at' => now(),
                'consent_version' => 'v1.0-UU-PDP',
            ]);

            return $user;
        });

        Notification::create([
            'user_id' => $user->id,
            'category' => 'hospital_info',
            'title' => 'Selamat Datang di HealthHub',
            'message' => 'Akun Anda aktif. Silakan lengkapi profil, booking dokter, atau gunakan AI Health Assistant.',
            'action_url' => '/',
        ]);

        $this->audit($request, 'PATIENT_REGISTERED', 'User', $user->id, null, "Registrasi pasien {$user->email}");

        $token = $user->createToken('healthhub-api')->plainTextToken;

        return $this->ok([
            'token' => $token,
            'tokenType' => 'Bearer',
            'user' => $this->profile($user->fresh()),
        ], 'Registrasi berhasil.', 201);
    }

    /**
     * @return array<string, mixed>
     */
    private function profile(User $user): array
    {
        $patient = $user->patientRecord;
        $doctor = $user->doctorProfile;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'phone' => $user->phone,
            'avatarPath' => $user->avatar_path,
            'isActive' => (bool) $user->is_active,
            'patient' => $patient ? [
                'id' => $patient->id,
                'medicalRecordNumber' => $patient->medical_record_number,
                'fullName' => $patient->full_name,
                'bloodType' => $patient->blood_type,
                'allergiesSummary' => $patient->allergies_summary,
            ] : null,
            'doctor' => $doctor ? [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'titleSuffix' => $doctor->title_suffix,
                'polyclinicId' => $doctor->polyclinic_id,
                'specializationId' => $doctor->specialization_id,
            ] : null,
        ];
    }
}
