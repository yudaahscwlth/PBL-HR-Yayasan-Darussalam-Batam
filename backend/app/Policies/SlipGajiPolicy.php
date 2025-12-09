<?php

namespace App\Policies;

use App\Models\SlipGaji;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SlipGajiPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // HRD staff dan kepala HRD bisa lihat semua
        // User biasa hanya bisa lihat milik sendiri (dicek di controller)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SlipGaji $slipGaji): bool
    {
        // HRD staff dan kepala HRD bisa lihat semua
        if ($user->hasAnyRole(['staff hrd', 'kepala hrd', 'superadmin'])) {
            return true;
        }

        // User biasa hanya bisa lihat milik sendiri
        return $user->id === $slipGaji->id_user;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Hanya HRD staff dan kepala HRD yang bisa membuat
        return $user->hasAnyRole(['staff hrd', 'kepala hrd', 'superadmin']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SlipGaji $slipGaji): bool
    {
        // Hanya HRD staff dan kepala HRD yang bisa edit
        return $user->hasAnyRole(['staff hrd', 'kepala hrd', 'superadmin']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SlipGaji $slipGaji): bool
    {
        // Hanya HRD staff dan kepala HRD yang bisa hapus
        return $user->hasAnyRole(['staff hrd', 'kepala hrd', 'superadmin']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SlipGaji $slipGaji): bool
    {
        return $user->hasAnyRole(['staff hrd', 'kepala hrd', 'superadmin']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SlipGaji $slipGaji): bool
    {
        return $user->hasAnyRole(['staff hrd', 'kepala hrd', 'superadmin']);
    }
}
