using System.Collections.Generic;
using Cysharp.Threading.Tasks;
using GLOW.Debugs.AdminDebug.Domain.UseCases;
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
    public sealed class AdminDebugInputPresenter : IAdminDebugInputViewDelegate
    {
        [Inject] AdminDebugUseCases UseCases { get; }
        [Inject] IApplicationRebootor ApplicationRebootor { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject(Id = FrameworkInjectId.Canvas.System)] UICanvas Canvas { get; }

        AdminDebugInputViewController ViewController { get; set; }

        void IAdminDebugInputViewDelegate.OnViewDidLoad(
            AdminDebugInputViewController viewController)
        {
            ApplicationLog.Log(
                nameof(AdminDebugInputPresenter),
                nameof(IAdminDebugInputViewDelegate.OnViewDidLoad));
            ViewController = viewController;
        }

        void IAdminDebugInputViewDelegate.OnSubmit(
            string command,
            Dictionary<string, object> parameters)
        {
            DoAsync.Invoke(
                ViewController.ActualView,
                ScreenInteractionControl,
                async cancellationToken =>
                {
                    try
                    {
                        await UseCases.ExecuteCommand(
                            cancellationToken, command, parameters);
                        ApplicationRebootor.Reboot();
                    }
                    catch (ServerErrorException)
                    {
                        ShowAlert(
                            "AdminDebugエラー",
                            $"【{command}】が利用できませんでした。");
                    }
                });
        }

        void ShowAlert(string title, string message)
        {
            var controller = MessageViewController.WithTitleAndMessage(
                title,
                message,
                "");
            var closeAction = new UIMessageAction(
                "閉じる",
                UIMessageActionStyle.Cancel);
            controller.AddAction(closeAction);
            Canvas.RootViewController.PresentModally(controller);
        }
    }
}
