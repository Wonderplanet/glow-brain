<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;

/**
 * @property Form $form
 */
class Login extends \Filament\Pages\Auth\Login
{
    /**
     * 継承元のクラスの関数の処理をそのまま持ってきてrateLimitの部分だけ変更しています
     */
    public function authenticate(): ?LoginResponse
    {
        try {
            // 5回ログイン失敗したら3時間ロック
            $this->rateLimit(
                config('admin.loginLimit.maxAttempts'),
                config('admin.loginLimit.decayMinutes')
            );
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/login.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/login.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/login.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();

        if (! Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();

        if (
            ($user instanceof FilamentUser) &&
            (! $user->canAccessPanel(Filament::getCurrentPanel()))
        ) {
            Filament::auth()->logout();

            $this->throwFailureValidationException();
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }

    protected function getFormActions(): array
    {
        $actions = parent::getFormActions();
        $actions[] = Action::make('sign_in_with_google')
            ->label('Sign in with Google')
            ->icon(asset('image/google.png'))
            ->color('info')
            ->url(route('Login', 'google'));

        return $actions;
    }
}
