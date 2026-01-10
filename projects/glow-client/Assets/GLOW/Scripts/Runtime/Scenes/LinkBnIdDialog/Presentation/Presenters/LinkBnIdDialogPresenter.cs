using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Exceptions;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.HomeMenu.Presentation.View;
using GLOW.Scenes.LinkBnIdDialog.Domain;
using GLOW.Scenes.LinkBnIdDialog.Presentation.Views;
using GLOW.Scenes.LinkBnIdWebViewDialog.Presentation.Views;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.LinkBnIdDialog.Presentation.Presenters
{
    public class LinkBnIdDialogPresenter : ILinkBnIdDialogViewDelegate
    {
        [Inject] LinkBnIdDialogViewController ViewController { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] LinkBnIdUseCase LinkBnIdUseCase { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }

        void ILinkBnIdDialogViewDelegate.OnLinkBnId()
        {
            var controller = ViewFactory.Create<LinkBnIdWebViewDialogViewController>();
            controller.OnRedirected = OnBnIdLinked;
            ViewController.PresentModally(controller);
        }

        void OnBnIdLinked(BnIdCode bnIdCode)
        {
            DoAsync.Invoke(ViewController.View, ScreenInteractionControl, async cancellationToken =>
            {
                try
                {
                    await LinkBnIdUseCase.LinkBnId(cancellationToken, bnIdCode);
                    MessageViewUtil.ShowMessageWithOk(
                        "連携完了",
                        "アカウント連携が完了しました。\n\n他の端末でアプリを開き、「タイトル画面メニュー」から「アカウント連携」をすると、ゲームデータを共有した状態でプレイを開始できます。",
                        "※一度アカウント連携をすると、\nこのゲームデータに連携する\nバンダイナムコIDは変更できません。\n※有償プリズムは、異なるOS間では共有され\nません。購入したOSでのみ使用できます。");

                    ViewController.Dismiss();
                }
                catch (UserBnidAccessTokenApiErrorException)
                {
                    MessageViewUtil.ShowMessageWithClose(
                        "連携失敗",
                        "連携に失敗しました。\n\n再度お試しいただくか、お手数ですがお問い合わせください。");
                }
                catch (UserBnidLinkLimitException)
                {
                    MessageViewUtil.ShowMessageWithClose(
                        "アカウント連携凍結中",
                        "入力されたバンダイナムコIDに\n連携されたゲームデータは、\n不正が疑われる操作を確認したため\nアカウント連携機能を凍結中です。\n\nゲームデータを共有できませんでした。");
                }
                catch (UserBnidLinkLimitMyAccountException)
                {
                    MessageViewUtil.ShowMessageWithClose(
                        "アカウント連携停止中",
                        "ご利用中のゲームデータにて\n不正行為が疑われる操作を確認したため、\nアカウント連携機能を凍結しております。\n\n<color=red>上記に関して異議申し立ては、\nアカウント連携機能の凍結から1ヶ月以内に\n運営へお問い合わせください。</color>",
                        "<color=#222222>※アカウント連携機能の凍結実施日は\nメールBOXに届いているメールを\nご確認ください。</color>");
                }
                catch (UserBnidLinkedOtherUserException)
                {
                    MessageViewUtil.ShowMessageWithClose(
                        "連携失敗",
                        "このバンダイナムコIDは他のゲームデータと連携済みのため、連携できません。\n\nアカウント連携したゲームデータを共有してプレイする時は、「タイトル画面メニュー」から「アカウント連携」を選んでください。");
                }
            });
        }

        void ILinkBnIdDialogViewDelegate.OnClose()
        {
            ViewController.Dismiss();
        }
    }
}
