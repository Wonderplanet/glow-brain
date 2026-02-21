using System;
using UIKit;
using UnityHTTPLibrary;
using WPFramework.Application.ErrorHandle;
using WPFramework.Constants.Zenject;
using WPFramework.Modules.Localization.Terms;
using WPFramework.Presentation.Modules;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Exceptions;
using GLOW.Core.Presentation.Modules.Systems;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.AccountBanDialog.Presentation.View;
using GLOW.Scenes.ClientUpdate.Presentation.View;
using GLOW.Scenes.MaintenanceDialog.Presentation.View;
using GLOW.Scenes.TitleMenu.Domain;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using Zenject;

namespace GLOW.Core.Application.ErrorHandle.Handlers
{
    public sealed class ServerErrorExceptionHandler : IServerErrorExceptionPreHandler, IServerErrorExceptionPostHandler
    {
        const string MaintenanceAlertViewPrefabName = "MaintenanceAlertView";

        [Inject(Id = FrameworkInjectId.Canvas.System)] UICanvas Canvas { get; }
        [Inject] ILocalizationTermsSource Terms { get; }
        [Inject] ICommonExceptionViewer CommonExceptionViewer { get; }
        [Inject] IApplicationRebootor ApplicationRebootor { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] UserDataDeleteUseCase UserDataDeleteUseCase { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] IContentMaintenanceCoordinator ContentMaintenanceCoordinator { get; }
        [Inject(Id = FrameworkInjectId.Canvas.System)]
        UICanvas SystemCanvas { get; }

        bool IServerErrorExceptionPreHandler.Handle(ServerErrorException exception, Action completion)
        {
            // NOTE: ハンドリング済みの場合はtrueを返す
            if (HandleStatusCode(exception, completion))
            {
                return true;
            }

            // NOTE: ハンドリング済みの場合はtrueを返す
            if (HandleServerErrorCode(exception, completion))
            {
                return true;
            }

            // NOTE: ハンドリングしなかったものは処理をコンプリートさせて後続のハンドラーへ通知を送る
            completion?.Invoke();

            // NOTE: ハンドリングしないものはfalseを返す
            return false;
        }

        bool IServerErrorExceptionPostHandler.Handle(ServerErrorException exception, Action completion)
        {
            // NOTE: ハンドリング済みの場合はtrueを返す
            return HandleCommon(exception, completion);
        }

        bool HandleStatusCode(ServerErrorException exception, Action completion)
        {
            switch (exception.StatusCode)
            {
                case HTTPStatusCodes.BadGateway:
                    // NOTE: BadGatewayは文言詳細がないため固定文言を表示する
                    MessageViewUtil.ShowMessageWithOk(
                        "通信エラー",
                        "通信エラーが発生しました。",
                        string.Empty,
                        () =>
                        {
                            ApplicationRebootor.Reboot();
                            completion?.Invoke();
                        },
                        false);
                    return true;
            }

            return false;
        }

        bool HandleServerErrorCode(ServerErrorException exception, Action completion)
        {
            var errorCode = (ServerErrorCode)exception.ServerErrorCode;
            switch (errorCode)
            {
                case ServerErrorCode.MultipleDeviceLoginDetected:
                    MessageViewUtil.ShowMessageWithOk(
                        "確認",
                        "アカウント連携済みの\n別端末がログインしました。\nタイトル画面へ戻ります。",
                        string.Empty,
                        () =>
                        {
                            ApplicationRebootor.Reboot();
                            completion?.Invoke();
                        });
                    return true;
                // NOTE: 部分メンテナンス中
                case ServerErrorCode.ContentMaintenanceNeedCleanup:
                case ServerErrorCode.ContentMaintenance:
                    bool needsCleanup = errorCode == ServerErrorCode.ContentMaintenanceNeedCleanup;
                    return ContentMaintenanceCoordinator.TryHandle(needsCleanup, completion);
                // NOTE: 部分メンテナンス外(Cleanup時にメンテナンスが終了していた場合)
                case ServerErrorCode.ContentMaintenanceOutside:
                    MessageViewUtil.ShowMessageWithOk(
                        "データ更新",
                        "データが更新されています。" +
                        "\nタイトル画面へ戻ります。",
                    string.Empty,
                    () =>
                    {
                        ApplicationRebootor.Reboot();
                        completion?.Invoke();
                    });
                    return true;
                // NOTE: メンテナンス中
                case ServerErrorCode.Maintenance:
                    // NOTE: ServerErrorException.ServerErrorMessage にメンテナンスメッセージが入っている
                    ShowMaintenanceView(exception.ServerErrorMessage, exception, completion);
                    return true;
                // NOTE: クライアントのバージョンアップが必要
                case ServerErrorCode.RequireClientVersionUpdate:
                    var controller = ViewFactory.Create<ClientUpdateDialogViewController>();
                    SystemCanvas.RootViewController.PresentModally(controller);
                    completion?.Invoke();
                    return true;
                // NOTE: マスターデータやアセットデータの再取得が必要なため確認ダイアログを表示した後にタイトルへ戻しログインをさせ直す
                case ServerErrorCode.RequireResourceUpdate:
                    MessageViewUtil.ShowMessageWithOk(
                        Terms.Get("data_upgrade_required_dialog_title"),
                        Terms.Get("data_upgrade_required_dialog_message"),
                        string.Empty,
                        () =>
                        {
                            ApplicationRebootor.Reboot();
                            completion?.Invoke();
                        });
                    return true;
                // NOTE: 日付変更(日跨ぎ)が発生したらログインさせなおす
                case ServerErrorCode.CrossDay:
                    MessageViewUtil.ShowMessageWithOk(
                        "日付変更",
                        "日付が変わりました。\nタイトル画面へ戻ります。",
                        string.Empty,
                        () =>
                        {
                            ApplicationRebootor.Reboot();
                            completion?.Invoke();
                        });
                    return true;
                // NOTE: アカウントBAN
                case ServerErrorCode.UserAccountBanTemporaryByCheating:
                    ShowAccountBanView(
                        AccountBanType.TemporaryByCheating,
                        () =>
                        {
                            ApplicationRebootor.Reboot();
                            completion?.Invoke();
                        });
                    return true;
                case ServerErrorCode.UserAccountBanTemporaryByDetectedAnomaly:
                    ShowAccountBanView(
                        AccountBanType.TemporaryByDetectedAnomaly,
                        () =>
                        {
                            ApplicationRebootor.Reboot();
                            completion?.Invoke();
                        });
                    return true;
                case ServerErrorCode.UserAccountRefunding:
                    ShowAccountBanView(
                        AccountBanType.TemporaryByUserAccountRefunding,
                        () =>
                        {
                            ApplicationRebootor.Reboot();
                            completion?.Invoke();
                        });
                    return true;
                case ServerErrorCode.UserAccountBanPermanent:
                    ShowAccountBanView(
                        AccountBanType.Permanent,
                        () =>
                        {
                            ApplicationRebootor.Reboot();
                            completion?.Invoke();
                        });
                    return true;
                case ServerErrorCode.UserAccountDeleted:
                    MessageViewUtil.ShowMessageWithOk(
                        "ログイン不可",
                        "この端末で利用していたゲームアカウントは削除されました。\n新しいアカウントでゲームを始めるために、端末に保存されているユーザーデータを削除します。",
                        string.Empty,
                        () =>
                        {
                            DoAsync.Invoke(Canvas.RootViewController.View, ScreenInteractionControl, async cancellationToken =>
                            {
                                await UserDataDeleteUseCase.DeleteUserData(cancellationToken);
                                ApplicationRebootor.Reboot();
                                completion?.Invoke();
                            });
                        });
                    return true;
            }

            return false;
        }

        bool HandleCommon(ServerErrorException exception, Action completion)
        {
            var message = GetCommonErrorMessage(exception);

            CommonExceptionViewer.Show(
                Terms.Get("common_server_error_dialog_title"),
                message,
                exception,
                completion);

            return true;
        }

        string GetCommonErrorMessage(ServerErrorException exception)
        {
            // NOTE: DataInconsistencyServerErrorExceptionの場合は専用メッセージを表示する
            if (exception is DataInconsistencyServerErrorException)
            {
                return "データ不整合が発生しました。\n再ログインしてください。";
            }

            return Terms.Get("common_error_dialog_message");
        }

        void ShowMaintenanceView(string message, ServerErrorException exception, Action completion)
        {
            var argument = new MaintenanceDialogViewController.Argument(message);

            var controller = ViewFactory
                .Create<MaintenanceDialogViewController, MaintenanceDialogViewController.Argument>(argument);

            controller.OnClose = completion;
            SystemCanvas.RootViewController.PresentModally(controller, false);
        }

        void ShowAccountBanView(AccountBanType accountBanType, Action completion)
        {
            var argument = new AccountBanDialogViewController.Argument(accountBanType);

            var controller = ViewFactory
                .Create<AccountBanDialogViewController, AccountBanDialogViewController.Argument>(argument);

            controller.OnClose = completion;
            SystemCanvas.RootViewController.PresentModally(controller, false);
        }
    }
}
