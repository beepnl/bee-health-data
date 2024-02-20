<?php

namespace App\Providers;

use App\Models\Dataset;
use App\Models\Keyword;
use App\Models\Organisation;
use App\Models\User;
use App\Policies\DatasetPolicy;
use App\Policies\KeywordPolicy;
use App\Policies\OrganisationPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Keyword::class => KeywordPolicy::class,
        Organisation::class => OrganisationPolicy::class,
        Dataset::class => DatasetPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('isSuperAdmin', function(User $user){
            return $user->isSuperAdmin();
        });

        Gate::define('isAdmin', function (User $user) {
            if($user->isSuperAdmin()){
                return true;
            }
            return $user->isAdminMember();
        });
        
        Gate::define('isAdminOf', function (User $user, Organisation $organisation) {
            if($user->isSuperAdmin()){
                return true;
            }
            return $user->isAdminMemberOf($organisation);
        });
    }
}
