<?php

namespace App\Models\CompanyModule;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CompanyUser extends Pivot // ← Changed from Model to Pivot
{
    protected $table = 'company_module_users';
    protected $primaryKey = 'id';
    public $timestamps = true;

    const CREATED_AT = 'Created_At';
    const UPDATED_AT = 'Updated_At';

    protected $fillable = [
        'Company_ID',
        'User_ID',
        'Role',
        'Is_Primary',
        'Invited_By',
        'Invited_At',
        'Accepted_At',
        'Invitation_Token',
        'Invitation_Expires_At',
        'Is_Active',
    ];

    protected $casts = [
        'Is_Primary' => 'boolean',
        'Is_Active' => 'boolean',
        'Invited_At' => 'datetime',
        'Accepted_At' => 'datetime',
        'Invitation_Expires_At' => 'datetime',
        'Created_At' => 'datetime',
        'Updated_At' => 'datetime',
    ];

    /**
     * Company this user belongs to
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'Company_ID', 'id');
    }

    /**
     * User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'User_ID', 'User_ID');
    }

    /**
     * User who invited this user
     */
    public function invitedBy()
    {
        return $this->belongsTo(User::class, 'Invited_By', 'User_ID');
    }

    /**
     * Scope: Active users
     */
    public function scopeActive($query)
    {
        return $query->where('Is_Active', true);
    }

    /**
     * Scope: Owners only
     */
    public function scopeOwners($query)
    {
        return $query->where('Role', 'owner');
    }

    /**
     * Scope: Pending invitations
     */
    public function scopePendingInvitation($query)
    {
        return $query->whereNull('Accepted_At')
                     ->where('Invitation_Expires_At', '>', now());
    }

    /**
     * Check if user is owner
     */
    public function isOwner()
    {
        return $this->Role === 'owner';
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return in_array($this->Role, ['owner', 'admin']);
    }

    /**
     * Check if invitation is expired
     */
    public function isInvitationExpired()
    {
        return $this->Invitation_Expires_At && $this->Invitation_Expires_At->isPast();
    }

    /**
     * Accept invitation
     */
    public function acceptInvitation()
    {
        $this->update([
            'Accepted_At' => now(),
            'Invitation_Token' => null,
            'Is_Active' => true,
        ]);
    }
}