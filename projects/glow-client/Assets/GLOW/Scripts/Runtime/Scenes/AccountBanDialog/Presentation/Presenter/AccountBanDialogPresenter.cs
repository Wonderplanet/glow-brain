using GLOW.Scenes.AccountBanDialog.Presentation.View;
using GLOW.Scenes.AdventBattle.Domain.UseCase;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using Zenject;

namespace GLOW.Scenes.AccountBanDialog.Presentation.Presenter
{
    /// <summary>
    /// 11_タイトル
    /// 　11-2_ログイン
    /// 　　11-2-7_アカウント停止ダイアログ
    ///
    /// 800-1-3_BANメッセージ
    /// 800-1-4_BANメッセージ
    /// </summary>
    public class AccountBanDialogPresenter : IAccountBanDialogViewDelegate
    {
        [Inject] AccountBanDialogViewController ViewController { get; }
        [Inject] AccountBanDialogViewController.Argument Argument { get; }
        [Inject] AccountBanNoticeUseCase AccountBanNoticeUseCase { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }

        void IAccountBanDialogViewDelegate.ViewDidLoad()
        {
            ViewController.SetContent(Argument.AccountBanType);

            DoAsync.Invoke(ViewController.ActualView, ScreenInteractionControl, async cancellationToken =>
            {
                var result = await AccountBanNoticeUseCase.GetAccountBanUserMyId(cancellationToken);
                ViewController.SetUserMyId(result);
            });
        }

        void IAccountBanDialogViewDelegate.OnClose()
        {
            ViewController.OnClose?.Invoke();
            ViewController.Dismiss();
        }
    }
}
