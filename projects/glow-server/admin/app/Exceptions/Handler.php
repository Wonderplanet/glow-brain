<?php

namespace App\Exceptions;

use App\Notifications\ExceptionSlackNotification;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * slack通知を行わない例外一覧
     * 必要に応じて追加する
     * (APIはエラーコードで除外するがツール側はエラーコードがないので例外を指定して除外する)
     */
    private const array EXCEPTIONS_WITHOUT_SLACK_NOTIFICATION = [
        FileNotFoundException::class,
        // ログイン認証切れ
        AuthenticationException::class,
        NotFoundHttpException::class,
    ];

    /**
     * エラーログへの書き込みを行わないエラーコード一覧
     * 必要に応じて追加する
     */
    private const array ERROR_CODES_WITHOUT_WRITE_LOG = [
    ];

    /**
     * Slack通知のメッセージの最大文字数
     * slackの公式情報が見つからなかったので実測して7850文字程度で切られていたので7800文字とした
     */
    private const int MAX_SLACK_MESSAGE_LENGTH = 7800;

    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * 外部から呼ばれる想定
     */
    public static function sendPostSlack(Throwable $e): void
    {
        $request = request();
        $header = json_encode($request->headers->all());
        $body = json_encode($request->collect());
        self::postSlack($request, $header, $body, $e);
    }

    private static function postSlack(
        Request $request,
        string $header,
        string $body,
        Throwable $e
    ): void {
        // Webhook URL
        $webhookUrl = env('ERROR_SLACK_WEBHOOK_URL');
        if (!$webhookUrl || !env('ERROR_SLACK_ACTIVE', false)) {
            return;
        }

        $channel = "#" . env('ERROR_SLACK_CHANNEL', 'glow_admin_error-wp');
        $username = env('ERROR_SLACK_USERNAME', 'ANIMA_ADMIN_ERROR');
        $requestFullUrl = $request->fullUrl();

        // 通知しないケース
        if(self::disableNotification($requestFullUrl, $e)) {
            return;
        }

        // エラー内容は10行目まで
        $trace = $e->getTrace();
        $noticeText = get_class($e) . ": [{$e->getCode()}]: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}" . PHP_EOL;
        for ($i = 0; $i < 10; $i++) {
            $noticeText .= '#' . $i . ' ';
            $noticeText .= isset($trace[$i]['file']) ? $trace[$i]['file'] : 'unknown file';
            $noticeText .= ' (';
            $noticeText .= isset($trace[$i]['line']) ? $trace[$i]['line'] : 'unknown line';
            $noticeText .= '): ';
            $noticeText .= isset($trace[$i]['class']) ? $trace[$i]['class'] : 'unknown class';
            $noticeText .= isset($trace[$i]['type']) ? $trace[$i]['type'] : 'unknown type';
            $noticeText .= isset($trace[$i]['function']) ? $trace[$i]['function'] : 'unknown function';
            $noticeText .= '()' . PHP_EOL;
        }

        $message = "【エラー通知】\n\n";
        $message .= "<URL>\n```{$requestFullUrl}```\n\n";
        $message .= "<エラー内容>\n```" . $noticeText . "```\n";
        $message .= "<HTTPヘッダ>\n```" . $header . "```\n\n";

        if (!empty($body)) {
            $message .= "<HTTPボディ>\n```" . $body . "```\n\n";
        }

        // メッセージ
        $messageArray = array(
            "channel"    => $channel,
            "username"   => $username,
            "icon_emoji" => ":slack:",
            "text"       => $message
        );

        // メッセージをjson化
        $message_json = json_encode($messageArray);

        // payloadの値としてURLエンコード
        $message_post = "payload=" . urlencode($message_json);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $webhookUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $message_post);
        curl_exec($ch);
        curl_close($ch);
    }

    public function report(\Throwable $e)
    {
        // Slack通知
        $this->sendSlackNotification($e);
        if ($this->errorLogSkipForSpecificCodesEnabled($e->getCode())) {
            // 特定のエラーコードの場合はエラーログへの書き込みをスキップ
            return;
        }

        parent::report($e);
    }

    private function sendSlackNotification(\Throwable $e): void
    {
        if (!$this->slackNotificationEnabled()) {
            // Slack通知を無効にしている場合は何もしない
            return;
        }

        if (in_array($e::class, self::EXCEPTIONS_WITHOUT_SLACK_NOTIFICATION)) {
            // 除外対象の例外はslack通知しない
            return;
        }

        $errorCode = $e->getCode();
        $errorMessage = $this->createErrorMessage($e);
        $message = "*env:* [tool]" . config('app.env') . "\n" .
            "*error_code:* {$errorCode}\n" .
            "*message:* \n``` $errorMessage";
        if (mb_strlen($message) >= self::MAX_SLACK_MESSAGE_LENGTH) {
            $message = mb_substr($message, 0, self::MAX_SLACK_MESSAGE_LENGTH) . '...';
        }
        $message .= " ```";
        try {
            // エラーの無限ループを避けるためにtry-catchで囲む
            Notification::route('slack', config('services.slack.webhook_url'))
                ->notify(new ExceptionSlackNotification($message));
        } catch (\Throwable $e) {
            // Slack通知に失敗した場合は、ログに出力する
            Log::error('', [$e]);
        }
    }

    /**
     * @return string
     */
    private function createErrorMessage(\Throwable $e)
    {
        return get_class($e) .
            ": [{$e->getCode()}]: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}\n{$e->getTraceAsString()}";
    }

    /**
     * 特定のエラーコードの場合はエラーログへの書き込みをスキップするかどうか
     * @param int|string $errorCode
     * @return bool
     */
    private function errorLogSkipForSpecificCodesEnabled(int|string $errorCode): bool
    {
        return env('ENABLE_ERROR_LOG_SKIP_FOR_SPECIFIC_CODES', false)
            && in_array($errorCode, self::ERROR_CODES_WITHOUT_WRITE_LOG);
    }

    /**
     * Slack通知を有効にするかどうか
     * 環境変数 ENABLE_SLACK_ERROR_NOTIFICATION が true の場合に有効
     * @return bool
     */
    private function slackNotificationEnabled(): bool
    {
        return env('ENABLE_SLACK_ERROR_NOTIFICATION', false);
    }

    /**
     * 特定の条件でエラーをslackに通知したくない場合はこちらに条件を記載
     * @return bool 
     */
    private static function disableNotification(string $uri, $e): bool
    {
        // セッションが切れるとエラーになるためlivewire/updateのエラーは通知しない
        if (strpos($uri, 'livewire/update') !== false) {
            return true;
        }
        // 同じくセッションが切れるとエラーになるケースがあるので通知しない
        if ($e instanceof AuthenticationException) {
            return true;
        }

        return false;
    }
}
