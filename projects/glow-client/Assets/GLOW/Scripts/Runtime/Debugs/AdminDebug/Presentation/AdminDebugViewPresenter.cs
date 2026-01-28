using System;
using Cysharp.Threading.Tasks;
using Cysharp.Threading.Tasks.Linq;
using GLOW.Debugs.AdminDebug.Domain.Models;
using GLOW.Debugs.AdminDebug.Domain.UseCases;
using GLOW.Debugs.Command.Presentations.ViewModels;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Modules.MessageView.Presentation.Constants;
using UIKit;
using UnityHTTPLibrary;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Constants.Zenject;
using WPFramework.Modules.Log;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Debugs.AdminDebug.Presentation
{
    public sealed class AdminDebugViewPresenter : IAdminDebugViewDelegate
    {
        [Inject] AdminDebugUseCases UseCases { get; }
        [Inject] IApplicationRebootor ApplicationRebootor { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject(Id = FrameworkInjectId.Canvas.System)] UICanvas Canvas { get; }

        AdminDebugViewController ViewController { get; set; }
        bool _isInitialized;

        void IAdminDebugViewDelegate.OnViewDidLoad(AdminDebugViewController viewController)
        {
            ApplicationLog.Log(nameof(AdminDebugViewPresenter), nameof(IAdminDebugViewDelegate.OnViewDidLoad));
            ViewController = viewController;

            // NOTE: 現在時刻を見続け処理を走らせる
            UpdateCurrentTime();
            MonitorCurrentTime();

            // NOTE: 画面更新
            var model = UseCases.GetUseCaseModel();
            ViewController.SetEnvName(model.EnvName);
        }

        void IAdminDebugViewDelegate.OnViewWillAppear()
        {
            if (_isInitialized) return;

            DoAsync.Invoke(ViewController.ActualView, ScreenInteractionControl, async (cancellationToken) =>
            {
                AdminDebugViewModel adminDebugViewModel = null;
                try
                {
                    var debugMenuCommandList = await UseCases.GetCommandList(cancellationToken);
                    adminDebugViewModel = new AdminDebugViewModel(debugMenuCommandList.DebugMenuCommandModels);
                    _isInitialized = true;
                }
                catch (ServerErrorException)
                {
                    adminDebugViewModel = new AdminDebugViewModel(Array.Empty<AdminDebugMenuCommandModel>());
                    // NOTE: デバッグメニューなのでローカライズはしない
                    ShowAlert("AdminDebugエラー", $"AdminDebugが利用できません。");
                }
                SetCommandList(adminDebugViewModel);
            });
        }

        void ShowAlert(string title, string message)
        {
            var controller = MessageViewController.WithTitleAndMessage(
                title,
                message,
                ""
            );//ここの名前でUIViewRepositoryのプレハブ参照される。CustomAlertViewだと例えMessageViewControllerでもTemplateのプレハブが呼ばれる
            var closeAction = new UIMessageAction(
                "閉じる",
                UIMessageActionStyle.Cancel);
            controller.AddAction(closeAction);
            // controller.AddOptionalDestructiveAction(new UIAlertAction("", UIAlertActionStyle.Destructive));
            Canvas.RootViewController.PresentModally(controller);
        }

        void IAdminDebugViewDelegate.OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(AdminDebugViewPresenter), nameof(IAdminDebugViewDelegate.OnViewDidUnload));
        }

        void IAdminDebugViewDelegate.OnSelectCommand(AdminDebugMenuCommandModel command)
        {
            DoAsync.Invoke(ViewController.ActualView, ScreenInteractionControl,async cancellationToken =>
            {
                try
                {
                    await UseCases.ExecuteCommand(cancellationToken, command.Command);
                    // NOTE: 処理に成功した場合はアプリケーションを再起動する
                    ApplicationRebootor.Reboot();
                }
                catch (ServerErrorException)
                {
                    // NOTE: デバッグメニューなのでローカライズはしない
                    ShowAlert("AdminDebugエラー", $"【{command.Command}】が利用できませんでした。");
                }
            });
        }

        void MonitorCurrentTime()
        {
            DoAsync.Invoke(ViewController.ActualView, async cancellationToken =>
            {
                // NOTE: 毎ループ確認する
                await foreach (var _ in UniTaskAsyncEnumerable.EveryUpdate())
                {
                    // NOTE: CancellationTokenがキャンセルされたら処理を終了する
                    if (cancellationToken.IsCancellationRequested)
                    {
                        break;
                    }

                    await UniTask.Delay(TimeSpan.FromSeconds(1.0), cancellationToken: cancellationToken);

                    UpdateCurrentTime();
                }
            });
        }
        void UpdateCurrentTime()
        {
            var timeUseCaseModel = UseCases.GetUseCaseModel();
            var timeViewModel = new DebugCommandTimeViewModel(timeUseCaseModel.CurrentTime);
            ViewController.SetTime(timeViewModel);
        }

        void SetCommandList(AdminDebugViewModel viewModel)
        {
            ViewController.SetViewModel(viewModel);
        }
    }
}
