<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Adm\AdmRole;
use App\Models\Adm\AdmUser;
use App\Providers\RouteServiceProvider;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    private const LOGIN_PROVIDER = 'google';

    use AuthenticatesUsers;

    protected string $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * 認証ページへユーザーをリダイレクト
     */
    public function redirectToProvider()
    {
        return Socialite::driver(self::LOGIN_PROVIDER)->redirect();
    }

    /**
     * ログアウト
     * @return Application|\Illuminate\Http\RedirectResponse|Redirector
     */
    public function logout()
    {
        if (Auth::check()) {
            Auth::logout();
            return redirect(RouteServiceProvider::HOME)->with('info', 'ログアウトしました');
        }

        return redirect(RouteServiceProvider::HOME);
    }

    /**
     * ユーザー情報を取得
     *
     * @param Request $req
     * @return \Illuminate\Http\RedirectResponse|Redirector
     */
    public function handleProviderCallback(Request $req)
    {
        try {
            $admUser = new AdmUser();
            $provideUser = Socialite::driver(self::LOGIN_PROVIDER)->user();
            $user = $admUser->findByEmail($provideUser->getEmail());

            if (is_null($user)) {
                Notification::make()
                    ->title('アカウントが許可されていません。')
                    ->color('danger')
                    ->send();
                throw new Exception('アカウントが許可されていません。');
            } elseif ($user->active !== 1) {
                Notification::make()
                    ->title('現在、アカウントが停止されています')
                    ->color('danger')
                    ->send();
                throw new Exception('現在、アカウントが停止されています');
            }

            if (empty($user->google_id)) {
                $user->google_id = $provideUser->getId();
                $user->avatar = $provideUser->getAvatar();
                $user->first_name = $provideUser->user['given_name'];
                $user->last_name = $provideUser->user['family_name'];
                $user->remember_token = $provideUser->token;
            }
            $user->last_login_at = now();
            $user->save();

            Auth::login($user);
            return redirect()->intended(route('filament.admin.pages.bn-user-search'));
        } catch (Exception $e) {
            Notification::make()
                ->title('ログインに失敗')
                ->color('danger')
                ->send();
            return redirect()->intended(route('filament.admin.auth.login'));
        }
    }
}
