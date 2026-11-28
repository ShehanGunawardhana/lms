<?php

namespace App\Providers;

use App\Contracts\SmsChannelInterface;
use App\Contracts\WhatsAppChannelInterface;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Student;
use App\Policies\AssignmentPolicy;
use App\Policies\AssignmentSubmissionPolicy;
use App\Policies\QuestionPolicy;
use App\Policies\QuizAttemptPolicy;
use App\Policies\QuizPolicy;
use App\Policies\StudentPolicy;
use App\Services\InstitutionContext;
use App\Services\Notifications\Channels\LogSmsChannel;
use App\Services\Notifications\Channels\LogWhatsAppChannel;
use App\Services\SettingsService;
use App\Support\Security\PortalTwoFactorProvider;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use PragmaRX\Google2FA\Google2FA;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(InstitutionContext::class);
        $this->app->singleton(SettingsService::class);
        $this->app->bind(SmsChannelInterface::class, LogSmsChannel::class);
        $this->app->bind(WhatsAppChannelInterface::class, LogWhatsAppChannel::class);

        $this->app->singleton(TwoFactorAuthenticationProvider::class, function ($app) {
            return new PortalTwoFactorProvider(
                $app->make(Google2FA::class),
                $app->make(Repository::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(Assignment::class, AssignmentPolicy::class);
        Gate::policy(AssignmentSubmission::class, AssignmentSubmissionPolicy::class);
        Gate::policy(Question::class, QuestionPolicy::class);
        Gate::policy(Quiz::class, QuizPolicy::class);
        Gate::policy(QuizAttempt::class, QuizAttemptPolicy::class);
    }
}
