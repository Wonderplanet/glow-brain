using GLOW.Core.Exceptions;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.HomeMenu.Presentation.View;
using GLOW.Scenes.UnlinkBnIdDialog.Domain.UseCases;
using GLOW.Scenes.UnlinkBnIdDialog.Presentation.Views;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.UnlinkBnIdDialog.Presentation.Presenters
{
    public class UnlinkBnIdDialogPresenter : IUnlinkBnIdDialogViewDelegate
    {
        [Inject] UnlinkBnIdDialogViewController ViewController { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] UnlinkBnIdUseCase UnlinkBnIdUseCase { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] IApplicationRebootor ApplicationRebootor { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }

        void IUnlinkBnIdDialogViewDelegate.OnViewDidLoad()
        {
        }

        void IUnlinkBnIdDialogViewDelegate.OnUnlinkBnId()
        {
            MessageViewUtil.ShowMessageWith2Buttons(
                "ゲームデータ変更",
                "この端末でプレイ中のゲームデータを変更するために、一度この端末からゲームデータを削除します。\n\nゲームデータを削除すると、この端末からはプレイ中のゲームデータにアクセスできなくなります。\n他の端末でのプレイには影響しません。\n\nゲームデータを削除しますか？",
                "※同じバンダイナムコIDで再度タイトル画面からアカウント連携をすると、変更前のゲームデータを復元できます。\n※連携したバンダイナムコIDを紛失した場合、ゲームデータを復元できなくなります。\n",
                "データ削除",
                "キャンセル",
                () =>
                {
                    DoAsync.Invoke(ViewController.View, ScreenInteractionControl, async cancellationToken =>
                    {
                        try
                        {
                            await UnlinkBnIdUseCase.UnlinkBnId(cancellationToken);

                            MessageViewUtil.ShowMessageWithOk(
                                "確認",
                                "ゲームデータを削除しました。\nタイトル画面へ戻ります。\n\nアカウント連携済みのゲームデータでプレイを開始するときは、「タイトル画面メニュー」から「アカウント連携」を選んでください。",
                                "",
                                () => ApplicationRebootor.Reboot());
                        }
                        catch (UserBnidLinkLimitMyAccountException)
                        {
                            MessageViewUtil.ShowMessageWithClose(
                                "アカウント連携停止中",
                                "ご利用中のゲームデータにて\n不正行為が疑われる操作を確認したため、\nアカウント連携機能を凍結しております。\n\n<color=red>上記に関して異議申し立ては、\nアカウント連携機能の凍結から1ヶ月以内に\n運営へお問い合わせください。</color>",
                                "<color=#222222>※アカウント連携機能の凍結実施日は\nメールBOXに届いているメールを\nご確認ください。</color>");
                        }
                    });
                },
                () => { });
        }

        void IUnlinkBnIdDialogViewDelegate.OnClose()
        {
            ViewController.Dismiss();
        }
    }
}
