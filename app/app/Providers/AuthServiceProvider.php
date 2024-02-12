<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Checkpoint;
use App\Models\Event;
use App\Models\Organisation;
use App\Models\Participation;
use App\Models\Route;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Gate::define('delete-organisation', function (User $user, Organisation $organisation) {
            return $user->organisation_id === $organisation->id;
        });
        Gate::define('update-event', function (User $user, Event $event) {
            return $user->organisation_id === $event->organisation_id;
        });
        Gate::define('delete-event', function (User $user, Event $event) {
            return $user->organisation_id === $event->organisation_id;
        });
        Gate::define('delete-checkpoint', function (User $user, Checkpoint $checkpoint, Route $route,) {
            return $user->organisation_id === $checkpoint->route->event->organisation_id && $checkpoint->route_id === $route->id;
        });
        Gate::define('participate', function (User $user, Event $event) {
            return Participation::where('user_id', $user->id)->where('event_id', $event->id)->doesntExist();
        });
        Gate::define('update-participation', function (User $user, Event $event) {
            return Participation::where('user_id', $user->id)->where('event_id', $event->id)->exists();
        });
        Gate::define('post-checkpoint', function (User $user, Route $route) {
            return $user->organisation_id === $route->event->organisation_id;
        });
        Gate::define('complete-achievement', function (User $user, Event $event) {
            return Participation::where('user_id', $user->id)->where('event_id', $event->id)->exists();
        });
        Gate::define('follow-organisation', function (User $user, Organisation $organisation) {
            return $organisation->followers()->where('user_id', $user->id)->doesntExist();
        });
        Gate::define('unfollow-organisation', function (User $user, Organisation $organisation) {
            return $organisation->followers()->where('user_id', $user->id)->exists();
        });
        Gate::define('set-participant-present', function (User $user, Event $event) {
            return $user->organisation_id === $event->organisation_id;
        });
        Gate::define('invite-users', function (User $user, Event $event) {
            return $user->organisation_id === $event->organisation_id;
        });
    }
}
