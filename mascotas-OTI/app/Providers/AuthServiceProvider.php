<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Animal;
use App\Models\Vaccination;
use App\Models\HealthProcedure;
use App\Models\Campaign;
use App\Models\Adoption;
use App\Models\StrayAnimal;
use App\Models\Post;
use App\Models\Comment;
use App\Models\User;
use App\Models\Notification;
use App\Policies\AnimalPolicy;
use App\Policies\VaccinationPolicy;
use App\Policies\HealthProcedurePolicy;
use App\Policies\CampaignPolicy;
use App\Policies\AdoptionPolicy;
use App\Policies\StrayAnimalPolicy;
use App\Policies\PostPolicy;
use App\Policies\CommentPolicy;
use App\Policies\UserPolicy;
use App\Policies\NotificationPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Animal::class          => AnimalPolicy::class,
        Vaccination::class     => VaccinationPolicy::class,
        HealthProcedure::class => HealthProcedurePolicy::class,
        Campaign::class        => CampaignPolicy::class,
        Adoption::class        => AdoptionPolicy::class,
        StrayAnimal::class     => StrayAnimalPolicy::class,
        Post::class            => PostPolicy::class,
        Comment::class         => CommentPolicy::class,
        User::class            => UserPolicy::class,
        Notification::class    => NotificationPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}