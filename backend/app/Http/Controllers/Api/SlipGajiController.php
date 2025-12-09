<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SlipGaji;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Dompdf\Dompdf;
use Dompdf\Options;

class SlipGajiController extends Controller
{
    /**
     * Display a listing of slip gaji
     * HRD bisa lihat semua, user biasa hanya milik sendiri
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Check if table exists
            if (!Schema::hasTable('slip_gaji')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Table slip_gaji belum dibuat. Silakan jalankan: php artisan migrate',
                    'error' => 'Table not found',
                ], 500);
            }

            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $isHRD = $user->hasAnyRole(['staff hrd', 'kepala hrd', 'superadmin']);

            // Build query dengan eager loading yang aman
            $query = SlipGaji::with([
                'user' => function($q) {
                    $q->with([
                        'profilePribadi',
                        'profilePekerjaan' => function($q) {
                            $q->with(['departemen', 'jabatan']);
                        }
                    ]);
                }
            ]);

            // Filter berdasarkan role
            if (!$isHRD) {
                // User biasa hanya bisa lihat milik sendiri
                $query->where('id_user', $user->id);
            } else {
                // HRD bisa filter berdasarkan user_id jika diberikan
                if ($request->has('user_id')) {
                    $query->where('id_user', $request->user_id);
                }
            }

            // Filter berdasarkan tanggal
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('tanggal', [
                    $request->start_date,
                    $request->end_date
                ]);
            } elseif ($request->has('start_date')) {
                $query->where('tanggal', '>=', $request->start_date);
            } elseif ($request->has('end_date')) {
                $query->where('tanggal', '<=', $request->end_date);
            }

            $slipGaji = $query->orderBy('tanggal', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Slip gaji retrieved successfully',
                'data' => $slipGaji,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error in SlipGajiController@index: ' . $e->getMessage());
            
            // Check if table doesn't exist
            if (str_contains($e->getMessage(), "doesn't exist") || str_contains($e->getMessage(), "Base table or view not found")) {
                return response()->json([
                    'success' => false,
                    'message' => 'Table slip_gaji belum dibuat. Silakan jalankan: php artisan migrate',
                    'error' => 'Table not found',
                ], 500);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Database error occurred',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error in SlipGajiController@index: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve slip gaji',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created slip gaji
     * Hanya HRD yang bisa create
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check authorization
        if (!$user->hasAnyRole(['staff hrd', 'kepala hrd', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only HRD staff can create slip gaji.',
            ], 403);
        }

        $validated = $request->validate([
            'id_user' => 'required|exists:users,id',
            'tanggal' => 'required|date',
            'total_gaji' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ], [
            'id_user.required' => 'User wajib dipilih.',
            'id_user.exists' => 'User tidak ditemukan.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'total_gaji.required' => 'Total gaji wajib diisi.',
            'total_gaji.numeric' => 'Total gaji harus berupa angka.',
            'total_gaji.min' => 'Total gaji tidak boleh negatif.',
            'keterangan.string' => 'Keterangan harus berupa teks.',
        ]);

        try {
            $slipGaji = SlipGaji::create([
                'id_user' => $validated['id_user'],
                'tanggal' => $validated['tanggal'],
                'total_gaji' => $validated['total_gaji'],
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            $slipGaji->load([
                'user.profilePribadi',
                'user.profilePekerjaan.departemen',
                'user.profilePekerjaan.jabatan'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Slip gaji created successfully',
                'data' => $slipGaji,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create slip gaji',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified slip gaji
     */
    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $slipGaji = SlipGaji::with([
            'user.profilePribadi',
            'user.profilePekerjaan.departemen',
            'user.profilePekerjaan.jabatan'
        ])->findOrFail($id);

        // Check authorization
        $isHRD = $user->hasAnyRole(['staff hrd', 'kepala hrd', 'superadmin']);
        if (!$isHRD && $slipGaji->id_user !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only view your own slip gaji.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Slip gaji retrieved successfully',
            'data' => $slipGaji,
        ]);
    }

    /**
     * Update the specified slip gaji
     * Hanya HRD yang bisa update
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        // Check authorization
        if (!$user->hasAnyRole(['staff hrd', 'kepala hrd', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only HRD staff can update slip gaji.',
            ], 403);
        }

        $slipGaji = SlipGaji::findOrFail($id);

        $validated = $request->validate([
            'id_user' => 'sometimes|exists:users,id',
            'tanggal' => 'sometimes|date',
            'total_gaji' => 'sometimes|numeric|min:0',
            'keterangan' => 'nullable|string',
        ], [
            'id_user.exists' => 'User tidak ditemukan.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'total_gaji.numeric' => 'Total gaji harus berupa angka.',
            'total_gaji.min' => 'Total gaji tidak boleh negatif.',
            'keterangan.string' => 'Keterangan harus berupa teks.',
        ]);

        try {
            $slipGaji->update($validated);
            $slipGaji->load([
                'user.profilePribadi',
                'user.profilePekerjaan.departemen',
                'user.profilePekerjaan.jabatan'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Slip gaji updated successfully',
                'data' => $slipGaji,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update slip gaji',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified slip gaji
     * Hanya HRD yang bisa delete
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        // Check authorization
        if (!$user->hasAnyRole(['staff hrd', 'kepala hrd', 'superadmin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only HRD staff can delete slip gaji.',
            ], 403);
        }

        $slipGaji = SlipGaji::findOrFail($id);

        try {
            $slipGaji->delete();

            return response()->json([
                'success' => true,
                'message' => 'Slip gaji deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete slip gaji',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get slip gaji history for a specific user
     */
    public function getUserHistory(Request $request, $userId): JsonResponse
    {
        $user = $request->user();
        $isHRD = $user->hasAnyRole(['staff hrd', 'kepala hrd', 'superadmin']);

        // Check authorization
        if (!$isHRD && $userId != $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only view your own slip gaji history.',
            ], 403);
        }

        $query = SlipGaji::where('id_user', $userId)
            ->with([
                'user.profilePribadi',
                'user.profilePekerjaan.departemen',
                'user.profilePekerjaan.jabatan'
            ]);

        // Filter berdasarkan tanggal
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('tanggal', [
                $request->start_date,
                $request->end_date
            ]);
        }

        $slipGaji = $query->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Slip gaji history retrieved successfully',
            'data' => $slipGaji,
        ]);
    }

    /**
     * Get employee data for slip gaji form
     * HRD can access any user, regular users can only access their own data
     */
    public function getEmployeeData(Request $request, $userId): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $isHRD = $user->hasAnyRole(['staff hrd', 'kepala hrd', 'superadmin']);

            // Check authorization: HRD can access any user, regular users can only access their own
            if (!$isHRD && $user->id != $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only view your own employee data.',
                ], 403);
            }

            $employee = User::with([
                'profilePribadi',
                'profilePekerjaan.departemen',
                'profilePekerjaan.jabatan'
            ])->findOrFail($userId);

            // Get nomor rekening from profile_pribadi
            $nomorRekening = $employee->profilePribadi->nomor_rekening ?? null;

            return response()->json([
                'success' => true,
                'message' => 'Employee data retrieved successfully',
                'data' => [
                    'id' => $employee->id,
                    'nama' => $employee->profilePribadi->nama_lengkap ?? 'N/A',
                    'nik' => $employee->profilePribadi->nomor_induk_kependudukan ?? 'N/A',
                    'tempat_lahir' => $employee->profilePribadi->tempat_lahir ?? 'N/A',
                    'tanggal_lahir' => $employee->profilePribadi->tanggal_lahir ?? null,
                    'departemen' => $employee->profilePekerjaan->departemen->nama_departemen ?? 'N/A',
                    'jabatan' => $employee->profilePekerjaan->jabatan->nama_jabatan ?? 'N/A',
                    'nomor_rekening' => $nomorRekening,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error in SlipGajiController@getEmployeeData: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve employee data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload Excel file untuk import slip gaji secara massal
     */
    public function uploadExcel(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Hanya HRD yang bisa upload Excel
            if (!$user->hasAnyRole(['staff hrd', 'kepala hrd', 'superadmin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengupload file Excel',
                ], 403);
            }

            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls|max:10240', // Max 10MB
            ], [
                'file.required' => 'File Excel wajib diupload',
                'file.file' => 'File harus berupa file yang valid',
                'file.mimes' => 'File harus berformat Excel (.xlsx atau .xls)',
                'file.max' => 'Ukuran file maksimal 10MB',
            ]);

            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Skip header row (baris pertama)
            $headerRow = array_shift($rows);
            
            // Validasi header
            $expectedHeaders = ['NIK', 'Nama Lengkap', 'Tanggal', 'Total Gaji', 'Keterangan'];
            $headerMap = [];
            foreach ($expectedHeaders as $expected) {
                $index = array_search($expected, $headerRow);
                if ($index === false) {
                    return response()->json([
                        'success' => false,
                        'message' => "Header '{$expected}' tidak ditemukan di file Excel. Pastikan format file sesuai template.",
                    ], 400);
                }
                $headerMap[$expected] = $index;
            }

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($rows as $rowIndex => $row) {
                $lineNumber = $rowIndex + 2; // +2 karena header di baris 1 dan array dimulai dari 0
                
                // Skip baris kosong
                if (empty(array_filter($row))) {
                    continue;
                }

                try {
                    // Get and normalize NIK - handle various formats
                    $nikRaw = $row[$headerMap['NIK']] ?? '';
                    
                    // Handle different Excel cell types (number, string, etc.)
                    // Convert to string first to preserve all digits
                    $nik = (string) $nikRaw;
                    if (stripos($nik, 'e+') !== false || stripos($nik, 'e-') !== false) {
                        throw new \Exception("Format NIK tidak valid pada baris {$lineNumber}. Pastikan kolom NIK di Excel bertipe Text dan tidak dalam format 9.76E+15.");
                    }  
                    
                    // Normalize NIK: trim, remove non-printable characters
                    // Remove non-printable characters (tabs, newlines, etc.)
                   $nik = preg_replace('/[\x00-\x1F\x7F]/u', '', $nik);
                    $nik = preg_replace('/\s+/', ' ', $nik);
                    $nik = trim($nik);
                    
                    // Get Nama Lengkap (optional, for validation)
                    $namaLengkap = trim($row[$headerMap['Nama Lengkap']] ?? '');
                    
                    $tanggal = trim($row[$headerMap['Tanggal']] ?? '');
                    $totalGaji = trim($row[$headerMap['Total Gaji']] ?? '');
                    $keterangan = trim($row[$headerMap['Keterangan']] ?? '');

                    // Validasi data
                    if (empty($nik)) {
                        throw new \Exception("NIK tidak boleh kosong");
                    }
                    
                    // Log NIK being searched for debugging
                    Log::debug("Searching for NIK in uploadExcel", [
                        'nik_raw' => $nikRaw,
                        'nik_raw_type' => gettype($nikRaw),
                        'nik_normalized' => $nik,
                        'nik_length' => strlen($nik),
                        'nik_bytes' => bin2hex($nik),
                        'line' => $lineNumber,
                    ]);

                    if (empty($tanggal)) {
                        throw new \Exception("Tanggal tidak boleh kosong");
                    }

                    if (empty($totalGaji)) {
                        throw new \Exception("Total Gaji tidak boleh kosong");
                    }

                    // Cari user berdasarkan NIK
                    // NIK sudah dinormalisasi di atas, gunakan langsung
                    $nikNormalized = $nik;
                    
                    // Try multiple ways to find user by NIK
                    // Method 1: Exact match with database (normalized on both sides)
                    $employee = User::with('profilePribadi')
                        ->whereHas('profilePribadi', function($q) {
                            $q->whereNotNull('nomor_induk_kependudukan');
                        })
                        ->get()
                        ->first(function($u) use ($nikNormalized) {
                            $dbNik = $u->profilePribadi->nomor_induk_kependudukan ?? null;
                            if (!$dbNik) return false;
                            
                            // Normalize database NIK the same way
                            $dbNikNormalized = (string)$dbNik;
                            $dbNikNormalized = preg_replace('/[\x00-\x1F\x7F]/u', '', $dbNikNormalized);
                            $dbNikNormalized = preg_replace('/\s+/', ' ', $dbNikNormalized);
                            $dbNikNormalized = trim($dbNikNormalized);
                            
                            return $dbNikNormalized === $nikNormalized;
                        });
                    
                    // Method 2: Try with raw database query (faster, but less flexible)
                    if (!$employee) {
                        $employee = User::whereHas('profilePribadi', function($q) use ($nikNormalized) {
                            $q->where('nomor_induk_kependudukan', $nikNormalized);
                        })->first();
                    }
                    
                    // Method 3: Try case-insensitive comparison (if NIK might have case differences)
                    if (!$employee) {
                        $employee = User::with('profilePribadi')
                            ->whereHas('profilePribadi', function($q) {
                                $q->whereNotNull('nomor_induk_kependudukan');
                            })
                            ->get()
                            ->first(function($u) use ($nikNormalized) {
                                $dbNik = $u->profilePribadi->nomor_induk_kependudukan ?? null;
                                if (!$dbNik) return false;
                                
                                // Normalize both for comparison
                                $dbNikNormalized = trim((string)$dbNik);
                                $dbNikNormalized = preg_replace('/[\x00-\x1F\x7F]/u', '', $dbNikNormalized);
                                $dbNikNormalized = preg_replace('/\s+/', ' ', $dbNikNormalized);
                                $dbNikNormalized = trim($dbNikNormalized);
                                
                                return strtolower($dbNikNormalized) === strtolower($nikNormalized);
                            });
                    }
                    
                    // If still not found, try searching by Nama Lengkap if provided
                    if (!$employee && !empty($namaLengkap)) {
                        $employee = User::with('profilePribadi')
                            ->whereHas('profilePribadi', function($q) use ($namaLengkap) {
                                $q->where('nama_lengkap', 'like', '%' . $namaLengkap . '%');
                            })
                            ->first();
                        
                        // If found by name, validate NIK matches
                        if ($employee) {
                            $dbNik = $employee->profilePribadi->nomor_induk_kependudukan ?? null;
                            if ($dbNik) {
                                $dbNikNormalized = (string)$dbNik;
                                $dbNikNormalized = preg_replace('/[\x00-\x1F\x7F]/u', '', $dbNikNormalized);
                                $dbNikNormalized = preg_replace('/\s+/', ' ', $dbNikNormalized);
                                $dbNikNormalized = trim($dbNikNormalized);
                                
                                if ($dbNikNormalized !== $nikNormalized) {
                                    // NIK doesn't match, but we found by name - warn but continue
                                    Log::warning("NIK mismatch but found by name in uploadExcel", [
                                        'searched_nik' => $nikNormalized,
                                        'db_nik' => $dbNikNormalized,
                                        'searched_name' => $namaLengkap,
                                        'db_name' => $employee->profilePribadi->nama_lengkap ?? null,
                                        'line_number' => $lineNumber,
                                    ]);
                                }
                            }
                        }
                    }
                    
                    // If still not found, log for debugging
                    if (!$employee) {
                        // Get sample NIKs for debugging
                        $allNiks = User::with('profilePribadi')
                            ->whereHas('profilePribadi', function($q) {
                                $q->whereNotNull('nomor_induk_kependudukan');
                            })
                            ->get()
                            ->map(function($u) {
                                $dbNik = $u->profilePribadi->nomor_induk_kependudukan ?? null;
                                return [
                                    'nik' => $dbNik,
                                    'nik_trimmed' => $dbNik ? trim((string)$dbNik) : null,
                                    'nik_length' => $dbNik ? strlen((string)$dbNik) : 0,
                                    'user_id' => $u->id,
                                    'user_email' => $u->email,
                                    'nama_lengkap' => $u->profilePribadi->nama_lengkap ?? null,
                                ];
                            })
                            ->filter(function($item) {
                                return $item['nik'] !== null;
                            })
                            ->take(10)
                            ->values()
                            ->toArray();
                        
                        Log::warning("NIK not found in uploadExcel", [
                            'searched_nik_raw' => $nik,
                            'searched_nik_normalized' => $nikNormalized,
                            'searched_nama_lengkap' => $namaLengkap,
                            'searched_nik_length' => strlen($nikNormalized),
                            'searched_nik_bytes' => bin2hex($nikNormalized),
                            'uploader' => $user->id,
                            'uploader_email' => $user->email,
                            'line_number' => $lineNumber,
                            'available_niks_sample' => $allNiks,
                        ]);
                        
                        throw new \Exception("User dengan NIK '{$nikNormalized}'" . (!empty($namaLengkap) ? " dan Nama '{$namaLengkap}'" : "") . " tidak ditemukan pada baris {$lineNumber}. Pastikan NIK sesuai dengan data di profile karyawan. NIK harus sama persis dengan yang ada di database (termasuk spasi dan format).");
                    }
                    
                    // Validate Nama Lengkap if provided
                    if (!empty($namaLengkap) && $employee) {
                        $dbNamaLengkap = $employee->profilePribadi->nama_lengkap ?? null;
                        if ($dbNamaLengkap && stripos($dbNamaLengkap, $namaLengkap) === false && stripos($namaLengkap, $dbNamaLengkap) === false) {
                            Log::warning("Nama Lengkap mismatch in uploadExcel", [
                                'searched_name' => $namaLengkap,
                                'db_name' => $dbNamaLengkap,
                                'nik' => $nikNormalized,
                                'line_number' => $lineNumber,
                            ]);
                            // Don't throw error, just log warning - NIK is the primary identifier
                        }
                    }

                    // Parse tanggal
                    try {
                        $tanggalParsed = Carbon::parse($tanggal)->format('Y-m-d');
                    } catch (\Exception $e) {
                        throw new \Exception("Format tanggal tidak valid: {$tanggal}");
                    }

                    // Parse total gaji
                    $totalGajiParsed = (float) str_replace([',', '.'], '', $totalGaji);
                    if ($totalGajiParsed <= 0) {
                        throw new \Exception("Total Gaji harus lebih dari 0");
                    }

                    // Cek apakah sudah ada slip gaji untuk employee dan tanggal yang sama
                    $existing = SlipGaji::where('id_user', $employee->id)
                        ->whereDate('tanggal', $tanggalParsed)
                        ->first();

                    if ($existing) {
                        // Update jika sudah ada
                        $existing->update([
                            'total_gaji' => $totalGajiParsed,
                            'keterangan' => $keterangan ?: null,
                        ]);
                    } else {
                        // Create baru - HRD bisa membuat untuk siapa saja
                        SlipGaji::create([
                            'id_user' => $employee->id,
                            'tanggal' => $tanggalParsed,
                            'total_gaji' => $totalGajiParsed,
                            'keterangan' => $keterangan ?: null,
                        ]);
                    }

                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = [
                        'line' => $lineNumber,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            if ($errorCount > 0 && $successCount === 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Semua data gagal diimport',
                    'errors' => $errors,
                    'total_errors' => $errorCount,
                ], 400);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Import berhasil: {$successCount} data berhasil, {$errorCount} data gagal",
                'data' => [
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'errors' => $errors,
                ],
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Excel upload error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses file Excel',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Download template Excel untuk import slip gaji
     */
    public function downloadTemplate()
    {
        try {
            $user = request()->user();
            
            // Hanya HRD yang bisa download template
            if (!$user || !$user->hasAnyRole(['staff hrd', 'kepala hrd', 'superadmin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk download template',
                ], 403);
            }
            
            // Check if PhpSpreadsheet is available
            if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
                throw new \Exception('PhpSpreadsheet package tidak terinstall. Jalankan: composer require phpoffice/phpspreadsheet');
            }
            
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->getStyle('A')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            // Set header
            $sheet->setCellValue('A1', 'NIK');
            $sheet->setCellValue('B1', 'Nama Lengkap');
            $sheet->setCellValue('C1', 'Tanggal');
            $sheet->setCellValue('D1', 'Total Gaji');
            $sheet->setCellValue('E1', 'Keterangan');
            // Style header
            $sheet->getStyle('A1:E1')->getFont()->setBold(true);
            $sheet->getStyle('A1:E1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF4472C4');
            $sheet->getStyle('A1:E1')->getFont()->getColor()->setARGB('FFFFFFFF');

            // Set width
            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(30);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(30);

            // Add example data
            $sheet->setCellValue('A2', '3201010101010001');
            $sheet->setCellValue('B2', 'John Doe');
            $sheet->setCellValue('C2', '2025-01-15');
            $sheet->setCellValue('D2', '5000000');
            $sheet->setCellValue('E2', 'Gaji bulan Januari 2025');

            // Add note
            $sheet->setCellValue('A4', 'Catatan:');
            $sheet->setCellValue('A5', '1. NIK harus sesuai dengan NIK di profile karyawan');
            $sheet->setCellValue('A6', '2. Nama Lengkap harus sesuai dengan nama di profile karyawan (untuk validasi)');
            $sheet->setCellValue('A7', '3. Format tanggal: YYYY-MM-DD (contoh: 2025-01-15)');
            $sheet->setCellValue('A8', '4. Total Gaji dalam format angka (tanpa titik atau koma)');
            $sheet->setCellValue('A9', '5. Keterangan bersifat opsional');

            $writer = new Xlsx($spreadsheet);
            $filename = 'template_import_slip_gaji_' . date('Y-m-d') . '.xlsx';

            // Create temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
            $writer->save($tempFile);
            
            return response()->download($tempFile, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Template download error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            // Return JSON error instead of abort for API
            // Make sure to set proper headers for JSON response
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat template Excel: ' . $e->getMessage(),
                'error' => app()->environment('local') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ] : 'Internal server error',
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    /**
     * Get employees grouped by payment status for a specific month/year
     */
    public function getEmployeesByPaymentStatus(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Hanya HRD yang bisa akses
            if (!$user->hasAnyRole(['staff hrd', 'kepala hrd', 'superadmin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengakses data ini',
                ], 403);
            }

            // Get month and year from request (default to current month/year)
            $month = $request->input('month', date('m'));
            $year = $request->input('year', date('Y'));

            // Validate month and year
            if (!is_numeric($month) || $month < 1 || $month > 12) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bulan tidak valid (1-12)',
                ], 400);
            }

            if (!is_numeric($year) || $year < 2000 || $year > 2100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tahun tidak valid',
                ], 400);
            }

            // Get start and end date of the month
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();

            // Get all users with their profile
            $allEmployees = User::with([
                'profilePribadi',
                'profilePekerjaan.departemen',
                'profilePekerjaan.jabatan',
            ])
            ->whereHas('profilePribadi')
            ->get();

            // Get all slip gaji for this month
            $slipGajiThisMonth = SlipGaji::whereBetween('tanggal', [$startDate, $endDate])
                ->with('user')
                ->get()
                ->keyBy('id_user');

            // Separate employees into two groups
            $paidEmployees = [];
            $unpaidEmployees = [];

            foreach ($allEmployees as $employee) {
                $employeeData = [
                    'id' => $employee->id,
                    'email' => $employee->email,
                    'nama' => $employee->profilePribadi->nama_lengkap ?? '-',
                    'nik' => $employee->profilePribadi->nomor_induk_kependudukan ?? '-',
                    'departemen' => $employee->profilePekerjaan->departemen->nama_departemen ?? '-',
                    'jabatan' => $employee->profilePekerjaan->jabatan->nama_jabatan ?? '-',
                ];

                if ($slipGajiThisMonth->has($employee->id)) {
                    // Employee has been paid
                    $slipGaji = $slipGajiThisMonth->get($employee->id);
                    $employeeData['slip_gaji'] = [
                        'id' => $slipGaji->id,
                        'tanggal' => $slipGaji->tanggal->format('Y-m-d'),
                        'total_gaji' => $slipGaji->total_gaji,
                        'keterangan' => $slipGaji->keterangan,
                    ];
                    $paidEmployees[] = $employeeData;
                } else {
                    // Employee has not been paid
                    $unpaidEmployees[] = $employeeData;
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'month' => (int)$month,
                    'year' => (int)$year,
                    'month_name' => Carbon::create($year, $month, 1)->locale('id')->translatedFormat('F Y'),
                    'unpaid_employees' => $unpaidEmployees,
                    'paid_employees' => $paidEmployees,
                    'total_unpaid' => count($unpaidEmployees),
                    'total_paid' => count($paidEmployees),
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error getting employees by payment status: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data karyawan',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Download PDF slip gaji per bulan
     */
    public function downloadPDF(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            // Get slip gaji
            $slipGaji = SlipGaji::with([
                'user.profilePribadi',
                'user.profilePekerjaan.departemen',
                'user.profilePekerjaan.jabatan',
            ])->find($id);

            if (!$slipGaji) {
                return response()->json([
                    'success' => false,
                    'message' => 'Slip gaji tidak ditemukan',
                ], 404);
            }

            // Check authorization: HRD can download any, others can only download their own
            $isHRD = $user->hasAnyRole(['staff hrd', 'kepala hrd', 'superadmin']);
            if (!$isHRD && $slipGaji->id_user !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengakses slip gaji ini',
                ], 403);
            }

            // Get employee data
            $employee = $slipGaji->user;
            $profilePribadi = $employee->profilePribadi ?? null;
            $profilePekerjaan = $employee->profilePekerjaan ?? null;

            // Format data
            $tanggal = Carbon::parse($slipGaji->tanggal);
            $bulanTahun = $tanggal->locale('id')->translatedFormat('F Y');
            $tanggalFormatted = $tanggal->format('d/m/Y');

            // Format currency
            $totalGaji = number_format($slipGaji->total_gaji, 0, ',', '.');

            // Generate HTML for PDF
            $html = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Slip Gaji - ' . htmlspecialchars($profilePribadi->nama_lengkap ?? 'Karyawan') . '</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 12px;
                        margin: 20px;
                        color: #333;
                    }
                    .header {
                        text-align: center;
                        margin-bottom: 30px;
                        border-bottom: 3px solid #1e4d8b;
                        padding-bottom: 20px;
                    }
                    .header h1 {
                        color: #1e4d8b;
                        margin: 0;
                        font-size: 24px;
                    }
                    .header p {
                        margin: 5px 0;
                        color: #666;
                    }
                    .info-section {
                        margin-bottom: 20px;
                    }
                    .info-row {
                        display: table;
                        width: 100%;
                        margin-bottom: 10px;
                    }
                    .info-label {
                        display: table-cell;
                        width: 150px;
                        font-weight: bold;
                        color: #555;
                    }
                    .info-value {
                        display: table-cell;
                        color: #333;
                    }
                    .salary-section {
                        margin-top: 30px;
                        border-top: 2px solid #1e4d8b;
                        padding-top: 20px;
                    }
                    .salary-row {
                        display: table;
                        width: 100%;
                        margin-bottom: 15px;
                    }
                    .salary-label {
                        display: table-cell;
                        width: 200px;
                        font-weight: bold;
                        font-size: 14px;
                    }
                    .salary-value {
                        display: table-cell;
                        font-size: 16px;
                        color: #1e4d8b;
                        font-weight: bold;
                    }
                    .footer {
                        margin-top: 40px;
                        text-align: center;
                        font-size: 10px;
                        color: #666;
                        border-top: 1px solid #ddd;
                        padding-top: 20px;
                    }
                    .keterangan {
                        margin-top: 20px;
                        padding: 10px;
                        background-color: #f5f5f5;
                        border-radius: 5px;
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>SLIP GAJI</h1>
                    <p>Bulan: ' . htmlspecialchars($bulanTahun) . '</p>
                    <p>Tanggal: ' . htmlspecialchars($tanggalFormatted) . '</p>
                </div>

                <div class="info-section">
                    <h2 style="color: #1e4d8b; border-bottom: 2px solid #1e4d8b; padding-bottom: 5px; margin-bottom: 15px;">Data Karyawan</h2>
                    <div class="info-row">
                        <div class="info-label">Nama:</div>
                        <div class="info-value">' . htmlspecialchars($profilePribadi->nama_lengkap ?? '-') . '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">NIK:</div>
                        <div class="info-value">' . htmlspecialchars($profilePribadi->nomor_induk_kependudukan ?? '-') . '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Tempat, Tgl Lahir:</div>
                        <div class="info-value">' . htmlspecialchars(($profilePribadi->tempat_lahir ?? '-') . ', ' . ($profilePribadi->tanggal_lahir ? Carbon::parse($profilePribadi->tanggal_lahir)->format('d/m/Y') : '-')) . '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Departemen:</div>
                        <div class="info-value">' . htmlspecialchars($profilePekerjaan->departemen->nama_departemen ?? '-') . '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Jabatan:</div>
                        <div class="info-value">' . htmlspecialchars($profilePekerjaan->jabatan->nama_jabatan ?? '-') . '</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Nomor Rekening:</div>
                        <div class="info-value">' . htmlspecialchars($profilePribadi->nomor_rekening ?? '-') . '</div>
                    </div>
                </div>

                <div class="salary-section">
                    <h2 style="color: #1e4d8b; border-bottom: 2px solid #1e4d8b; padding-bottom: 5px; margin-bottom: 15px;">Rincian Gaji</h2>
                    <div class="salary-row">
                        <div class="salary-label">Total Gaji:</div>
                        <div class="salary-value">Rp ' . htmlspecialchars($totalGaji) . '</div>
                    </div>
                </div>';

            if ($slipGaji->keterangan) {
                $html .= '
                <div class="keterangan">
                    <strong>Keterangan:</strong><br>
                    ' . nl2br(htmlspecialchars($slipGaji->keterangan)) . '
                </div>';
            }

            $html .= '
                <div class="footer">
                    <p>Dokumen ini adalah bukti pembayaran gaji yang sah</p>
                    <p>Dicetak pada: ' . Carbon::now()->format('d/m/Y H:i:s') . '</p>
                </div>
            </body>
            </html>';

            // Configure Dompdf
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'Arial');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Generate filename
            $namaFile = 'Slip_Gaji_' . 
                str_replace(' ', '_', $profilePribadi->nama_lengkap ?? 'Karyawan') . '_' . 
                $tanggal->format('Y_m') . '.pdf';

            return response()->streamDownload(function () use ($dompdf) {
                echo $dompdf->output();
            }, $namaFile, [
                'Content-Type' => 'application/pdf',
            ]);

        } catch (\Exception $e) {
            Log::error('PDF download error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat PDF',
                'error' => app()->environment('local') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }
}
