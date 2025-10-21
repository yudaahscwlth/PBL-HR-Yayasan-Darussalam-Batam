<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\User;
use App\Models\PengajuanCuti;
use App\Models\Jabatan;
use App\Models\Departemen;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    /**
     * Get dashboard statistics for HRD/Admin
     */
    public function getDashboardStats(Request $request): JsonResponse
    {
        try {
            $today = Carbon::today();
            
            // Get today's attendance count
            $todayAttendance = Absensi::whereDate('tanggal', $today)
                ->whereNotNull('check_in')
                ->count();
                
            // Get total employees using JOIN
            $totalEmployees = DB::table('users')
                ->join('profile_pekerjaans', 'users.id', '=', 'profile_pekerjaans.id_user')
                ->whereNotNull('profile_pekerjaans.id_jabatan')
                ->count();
            
            // Get today's leave count
            $todayLeave = PengajuanCuti::whereDate('tanggal_mulai', '<=', $today)
                ->whereDate('tanggal_selesai', '>=', $today)
                ->where('status_pengajuan', 'disetujui')
                ->count();
                
            // Get late employees count (assuming late is after 8:00 AM)
            $lateEmployees = Absensi::whereDate('tanggal', $today)
                ->whereNotNull('check_in')
                ->whereTime('check_in', '>', '08:00:00')
                ->count();
                
            // Get employees who didn't check in today
            $absentEmployees = $totalEmployees - $todayAttendance;
                
            // Get employee distribution by job title using JOIN
            $jobTitleDistribution = DB::table('users')
                ->join('profile_pekerjaans', 'users.id', '=', 'profile_pekerjaans.id_user')
                ->join('jabatans', 'profile_pekerjaans.id_jabatan', '=', 'jabatans.id')
                ->select('jabatans.nama_jabatan', DB::raw('count(DISTINCT users.id) as count'))
                ->groupBy('jabatans.nama_jabatan')
                ->get()
                ->map(function($item, $index) {
                    $colors = ['#1e40af', '#3b82f6', '#60a5fa', '#93c5fd', '#dbeafe'];
                    return [
                        'name' => $item->nama_jabatan,
                        'count' => $item->count,
                        'color' => $colors[$index % count($colors)]
                    ];
                });
                
            // Get employee distribution by department using JOIN
            $departmentDistribution = DB::table('users')
                ->join('profile_pekerjaans', 'users.id', '=', 'profile_pekerjaans.id_user')
                ->join('departemens', 'profile_pekerjaans.id_departemen', '=', 'departemens.id')
                ->select('departemens.nama_departemen', DB::raw('count(DISTINCT users.id) as count'))
                ->groupBy('departemens.nama_departemen')
                ->get()
                ->map(function($item, $index) {
                    $colors = ['#1e40af', '#3b82f6', '#60a5fa', '#93c5fd', '#dbeafe'];
                    return [
                        'name' => $item->nama_departemen,
                        'count' => $item->count,
                        'color' => $colors[$index % count($colors)]
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Dashboard statistics retrieved successfully',
                'data' => [
                    'today_attendance' => $todayAttendance,
                    'total_employees' => $totalEmployees,
                    'today_leave' => $todayLeave,
                    'late_employees' => $lateEmployees,
                    'absent_employees' => $absentEmployees,
                    'job_title_distribution' => $jobTitleDistribution,
                    'department_distribution' => $departmentDistribution,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Dashboard stats error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving dashboard statistics',
                'error' => $e->getMessage(),
                'data' => [
                    'today_attendance' => 0,
                    'total_employees' => 0,
                    'today_leave' => 0,
                    'late_employees' => 0,
                    'absent_employees' => 0,
                    'job_title_distribution' => [],
                    'department_distribution' => [],
                ],
            ], 500);
        }
    }

    /**
     * Get personal dashboard statistics for employee
     */
    public function getPersonalDashboardStats(Request $request): JsonResponse
    {
        $user = $request->user();
        $today = Carbon::today();
        
        // Load user with relationships
        $user->load(['profilePekerjaan.jabatan', 'profilePekerjaan.departemen']);
        
        // Get today's attendance
        $todayAttendance = Absensi::where('id_user', $user->id)
            ->whereDate('tanggal', $today)
            ->first();
            
        // Get attendance statistics for current month
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        
        $monthlyStats = Absensi::where('id_user', $user->id)
            ->whereBetween('tanggal', [$monthStart, $monthEnd])
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');
            
        // Get work anniversary info
        $workAnniversary = null;
        if ($user->profilePekerjaan && $user->profilePekerjaan->tanggal_masuk) {
            $joinDate = Carbon::parse($user->profilePekerjaan->tanggal_masuk);
            $diff = $joinDate->diff(Carbon::now());
            $workAnniversary = [
                'years' => $diff->y,
                'months' => $diff->m,
                'days' => $diff->d,
            ];
        }
        
        // Get recent leave requests
        $recentLeaves = PengajuanCuti::where('id_user', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Personal dashboard statistics retrieved successfully',
            'data' => [
                'today_attendance' => $todayAttendance,
                'monthly_stats' => $monthlyStats,
                'work_anniversary' => $workAnniversary,
                'recent_leaves' => $recentLeaves,
                'user_info' => [
                    'job_title' => $user->profilePekerjaan?->jabatan?->nama_jabatan,
                    'department' => $user->profilePekerjaan?->departemen?->nama_departemen,
                ],
            ],
        ]);
    }

    /**
     * Get attendance summary for today
     */
    public function getTodayAttendanceSummary(Request $request): JsonResponse
    {
        $today = Carbon::today();
        
        $summary = Absensi::whereDate('tanggal', $today)
            ->select(
                DB::raw('count(*) as total'),
                DB::raw('sum(case when jam_masuk is not null then 1 else 0 end) as checked_in'),
                DB::raw('sum(case when jam_keluar is not null then 1 else 0 end) as checked_out'),
                DB::raw('sum(case when jam_masuk > "08:00:00" then 1 else 0 end) as late')
            )
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Today attendance summary retrieved successfully',
            'data' => $summary,
        ]);
    }

    /**
     * Get all users with their job titles and departments
     */
    public function getUsersWithJobAndDepartment(Request $request): JsonResponse
    {
        $users = DB::table('users')
            ->join('profile_pekerjaans', 'users.id', '=', 'profile_pekerjaans.id_user')
            ->leftJoin('profile_pribadis', 'users.id', '=', 'profile_pribadis.id_user')
            ->leftJoin('jabatans', 'profile_pekerjaans.id_jabatan', '=', 'jabatans.id')
            ->leftJoin('departemens', 'profile_pekerjaans.id_departemen', '=', 'departemens.id')
            ->select(
                'users.id',
                'users.email',
                'profile_pribadis.nama_lengkap',
                'jabatans.nama_jabatan',
                'departemens.nama_departemen',
                'profile_pekerjaans.nomor_induk_karyawan',
                'profile_pekerjaans.tanggal_masuk',
                'profile_pekerjaans.status'
            )
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->nama_lengkap ?? 'N/A',
                    'job_title' => $user->nama_jabatan ?? 'N/A',
                    'department' => $user->nama_departemen ?? 'N/A',
                    'employee_id' => $user->nomor_induk_karyawan ?? 'N/A',
                    'join_date' => $user->tanggal_masuk ?? null,
                    'status' => $user->status ?? 'N/A',
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Users with job and department retrieved successfully',
            'data' => $users,
        ]);
    }

    /**
     * Get all job titles
     */
    public function getJobTitles(Request $request): JsonResponse
    {
        $jobTitles = DB::table('jabatans')
            ->leftJoin('profile_pekerjaans', 'jabatans.id', '=', 'profile_pekerjaans.id_jabatan')
            ->select('jabatans.id', 'jabatans.nama_jabatan', DB::raw('count(profile_pekerjaans.id) as employee_count'))
            ->groupBy('jabatans.id', 'jabatans.nama_jabatan')
            ->get()
            ->map(function($jobTitle) {
                return [
                    'id' => $jobTitle->id,
                    'name' => $jobTitle->nama_jabatan,
                    'employee_count' => $jobTitle->employee_count,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Job titles retrieved successfully',
            'data' => $jobTitles,
        ]);
    }

    /**
     * Get all departments
     */
    public function getDepartments(Request $request): JsonResponse
    {
        $departments = DB::table('departemens')
            ->leftJoin('users', 'departemens.id_kepala_departemen', '=', 'users.id')
            ->leftJoin('profile_pribadis', 'users.id', '=', 'profile_pribadis.id_user')
            ->leftJoin('profile_pekerjaans', 'departemens.id', '=', 'profile_pekerjaans.id_departemen')
            ->select(
                'departemens.id',
                'departemens.nama_departemen',
                'profile_pribadis.nama_lengkap as head_name',
                DB::raw('count(profile_pekerjaans.id) as employee_count')
            )
            ->groupBy('departemens.id', 'departemens.nama_departemen', 'profile_pribadis.nama_lengkap')
            ->get()
            ->map(function($department) {
                return [
                    'id' => $department->id,
                    'name' => $department->nama_departemen,
                    'head_name' => $department->head_name ?? 'N/A',
                    'employee_count' => $department->employee_count,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Departments retrieved successfully',
            'data' => $departments,
        ]);
    }
}
