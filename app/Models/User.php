<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'nric',
        'race',
        'date_of_birth',
        'gender',
        'occupation',
        'height_cm',
        'weight_kg',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'medical_consultation_2_years',
        'serious_illness_history',
        'insurance_rejection_history',
        'serious_injury_history',
        'relationship_with_agent',
        'balance',
        'wallet_balance',
        'address',
        'city',
        'state',
        'postal_code',
        'bank_name',
        'bank_account_number',
        'bank_account_owner',
        'mlm_level',
        'total_commission_earned',
        'monthly_commission_target',
        'status',
        'email_verified_at',
        'phone_verified_at',
        'mlm_activation_date',
        'plan_name',
        'payment_mode',
        'medical_card_type',
        'current_insurance_plan_id',
        'policy_start_date',
        'policy_end_date',
        'next_payment_due',
        'policy_status',
        'premium_amount',
        'current_payment_mode',
        'password',
        'agent_code',
        'referrer_code',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    // Relationships
    public function memberPolicies()
    {
        return $this->hasMany(MemberPolicy::class);
    }

    public function networkLevels()
    {
        return $this->hasMany(NetworkLevel::class);
    }

    public function policies()
    {
        return $this->hasMany(MemberPolicy::class);
    }

    public function agentWallet()
    {
        return $this->hasOne(AgentWallet::class);
    }

    public function wallet()
    {
        return $this->hasOne(AgentWallet::class);
    }

    public function referredUsers()
    {
        return $this->hasMany(User::class, 'referrer_code', 'agent_code');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referrer_code', 'agent_code');
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_code', 'agent_code');
    }

    public function commissionTransactions()
    {
        return $this->hasMany(CommissionTransaction::class, 'earner_user_id');
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    // JWT implementation
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
