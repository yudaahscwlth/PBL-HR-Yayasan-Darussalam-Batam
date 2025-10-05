<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PeriodePenilaian;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UpdatePeriodeStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'periode:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status periode penilaian berdasarkan tanggal mulai dan selesai';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai update status periode penilaian...');
        
        try {
            $now = Carbon::now();
            $updatedCount = 0;
            
            // Update status ke aktif untuk periode yang sudah waktunya dimulai
            $periodeToActivate = PeriodePenilaian::where('status', 'belum_dibuka')
                ->where('tanggal_mulai', '<=', $now->toDateString())
                ->get();
            
            foreach ($periodeToActivate as $periode) {
                $periode->update(['status' => 'aktif']);
                $updatedCount++;
                $this->info("✓ Periode '{$periode->nama_periode}' diaktifkan");
                Log::info("Periode {$periode->id} diaktifkan otomatis", [
                    'nama_periode' => $periode->nama_periode,
                    'tanggal_mulai' => $periode->tanggal_mulai,
                    'updated_at' => $now
                ]);
            }
            
            // Update status ke selesai untuk periode yang sudah berakhir
            $periodeToFinish = PeriodePenilaian::where('status', 'aktif')
                ->where('tanggal_selesai', '<', $now->toDateString())
                ->get();
            
            foreach ($periodeToFinish as $periode) {
                $periode->update(['status' => 'selesai']);
                $updatedCount++;
                $this->info("✓ Periode '{$periode->nama_periode}' diselesaikan");
                Log::info("Periode {$periode->id} diselesaikan otomatis", [
                    'nama_periode' => $periode->nama_periode,
                    'tanggal_selesai' => $periode->tanggal_selesai,
                    'updated_at' => $now
                ]);
            }
            
            if ($updatedCount > 0) {
                $this->info("✅ Total {$updatedCount} periode berhasil diupdate");
                Log::info("Update status periode selesai", [
                    'total_updated' => $updatedCount,
                    'timestamp' => $now
                ]);
            } else {
                $this->info("ℹ️  Tidak ada periode yang perlu diupdate");
            }
            
            // Tampilkan summary status saat ini
            $this->displayCurrentStatus();
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            Log::error("Error updating periode status", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
        
        return 0;
    }
    
    /**
     * Display current status summary
     */
    private function displayCurrentStatus()
    {
        $this->info("\n📊 Status Periode Saat Ini:");
        $this->line("├─ Belum dibuka: " . PeriodePenilaian::where('status', 'belum_dibuka')->count());
        $this->line("├─ Aktif: " . PeriodePenilaian::where('status', 'aktif')->count());
        $this->line("└─ Selesai: " . PeriodePenilaian::where('status', 'selesai')->count());
        
        // Tampilkan periode yang akan dimulai dalam 7 hari
        $upcomingPeriodes = PeriodePenilaian::where('status', 'belum_dibuka')
            ->where('tanggal_mulai', '>', Carbon::now()->toDateString())
            ->where('tanggal_mulai', '<=', Carbon::now()->addDays(7)->toDateString())
            ->get();
            
        if ($upcomingPeriodes->count() > 0) {
            $this->info("\n🔔 Periode yang akan dimulai dalam 7 hari:");
            foreach ($upcomingPeriodes as $periode) {
                $this->line("   • {$periode->nama_periode} - {$periode->tanggal_mulai->format('d/m/Y')}");
            }
        }
        
        // Tampilkan periode yang akan berakhir dalam 7 hari
        $endingPeriodes = PeriodePenilaian::where('status', 'aktif')
            ->where('tanggal_selesai', '>', Carbon::now()->toDateString())
            ->where('tanggal_selesai', '<=', Carbon::now()->addDays(7)->toDateString())
            ->get();
            
        if ($endingPeriodes->count() > 0) {
            $this->info("\n⏰ Periode yang akan berakhir dalam 7 hari:");
            foreach ($endingPeriodes as $periode) {
                $this->line("   • {$periode->nama_periode} - {$periode->tanggal_selesai->format('d/m/Y')}");
            }
        }
    }
}
