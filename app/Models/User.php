<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;




class User extends Authenticatable implements JWTSubject
{
    protected static function booted(): void
    {
        // the sales team sees every new student in the bell (and in New joins)
        static::created(function (self $u) {
            // joined through a friend's link (website cookie, or ref_code / X-Referral-Code sent by the apps)? Never let this break a sign up.
            if ($u->type === 'Student' && !app()->runningInConsole()) {
                try {
                    app(\App\Services\ReferralService::class)->attach($u);
                } catch (\Throwable $e) {
                    report($e);
                }
            }
            if ($u->type === 'Student') {
                \App\Services\StaffNotifier::push('new_join', ($u->name ?: 'A new student') . ' joined',
                    trim(($u->mobile ? 'Mobile ' . $u->mobile : 'No mobile yet') . ($u->email ? ' · ' . $u->email : '')), url('admin/new-joins'), null, $u->id);
            }
        });
    }

   use HasFactory, Notifiable, HasRoles;
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];


   public function isAdmin()
    {
        // Logic to check if the user is an admin
        // You need to replace this with your own logic based on your user model's structure
        return $this->type === 'Admin'; // Assuming you have a 'role' column in the users table
    }

    public function isStaff()
    {
        // Logic to check if the user is a staff member
        // You need to replace this with your own logic based on your user model's structure
        return $this->role === 'Admin Staff'; // Assuming you have a 'role' column in the users table
    }

    public function check_filledform($user_id = null){
        $form_filled = auth()->user()->filled_column;
        if($form_filled == 0){
             return ('web.stage1');
        }
        else if($form_filled == 1){
            return ('web.stage2');
        }
        else if($form_filled == 2){
     
            return ('dashboard');

            
        }
       
    }
      public function getProfilePhotoUrlAttribute()
    {
        return $this->image; 
    }

    /** Competitive exams this student is interested in (student_exam_interests). */
    public function examInterests()
    {
        return $this->belongsToMany(CompetitiveExam::class, 'student_exam_interests', 'user_id', 'exam_id')
            ->withPivot('source')->withTimestamps();
    }

    /** Exam categories this student marked as favourite (student_favourite_categories). */
    public function favouriteCategories()
    {
        return $this->belongsToMany(ExamCategory::class, 'student_favourite_categories', 'user_id', 'category_id')->withTimestamps();
    }

    /** Courses the student can open right now. */
    public function activeAdmissions()
    {
        return CourseAdmission::with('course.exam:id,slug,short_name,name,color')->where('user_id', $this->id)->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>', now()))->orderByDesc('admitted_at')->get();
    }

    /** Profile picture address: a full url (Google), a stored name (users/...), or null. */
    public function photoUrl(): ?string
    {
        if (!$this->image) {
            return null;
        }

        return preg_match('#^https?://#', $this->image) ? $this->image : \Illuminate\Support\Facades\Storage::url(str_contains($this->image, '/') ? $this->image : 'users/' . $this->image);
    }

    public function role()
    {
        return $this->hasMany(RoleUser::class, 'user_id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
