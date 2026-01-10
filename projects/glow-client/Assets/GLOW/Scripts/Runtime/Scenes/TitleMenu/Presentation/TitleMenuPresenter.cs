using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.AnnouncementWindow.Presentation.Facade;
using GLOW.Scenes.DataRepair.Presentation;
using GLOW.Scenes.Inquiry.Domain.UseCases;
using GLOW.Scenes.Inquiry.Presentation.View;
using GLOW.Scenes.Title.Presentations.Views;
using GLOW.Scenes.TitleLinkBnIdDialog.Presentation.Views;
using GLOW.Scenes.TitleMenu.Domain;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.TitleMenu.Presentation
{
    public class TitleMenuPresenter : ITitleMenuViewDelegate
    {
        [Inject] TitleMenuViewController ViewController { get; }
        [Inject] TitleMenuViewController.Argument Argument { get; }
        [Inject] IAnnouncementViewFacade AnnouncementViewFacade { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] GetInquiryModelUseCase GetInquiryModelUseCase { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] IApplicationRebootor ApplicationRebootor { get; }
        [Inject] TitleViewController TitleViewController { get; }
        [Inject] UserDataDeleteUseCase UserDataDeleteUseCase { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }

        void ITitleMenuViewDelegate.OnViewDidLoad()
        {
            ViewController.SetAnnouncementAlreadyAnnouncementBadge(Argument.AlreadyReadAnnouncementFlag);
        }

        void ITitleMenuViewDelegate.OnAnnouncement()
        {
            AnnouncementViewFacade.ShowMenuAnnouncement(
                ViewController,
                (alreadyReadAnnouncement) =>
                {
                    ViewController.SetAnnouncementAlreadyAnnouncementBadge(alreadyReadAnnouncement);
                });
        }

        void ITitleMenuViewDelegate.OnInquiry()
        {
            var useCaseModel = GetInquiryModelUseCase.GetInquiryModel();
            var argument = new InquiryDialogViewController.Argument(
                new InquiryDialogViewModel(useCaseModel.MyId, useCaseModel.InquiryURL));
            var inquiryDialogViewController = ViewFactory.Create<
                InquiryDialogViewController,
                InquiryDialogViewController.Argument>(argument);
            ViewController.PresentModally(inquiryDialogViewController);
        }

        void ITitleMenuViewDelegate.OnRepairData()
        {
            var controller = ViewFactory.Create<DataRepairViewController>();
            ViewController.PresentModally(controller);
        }

        void ITitleMenuViewDelegate.OnLinkAccount()
        {
            var controller = ViewFactory.Create<TitleLinkBnIdDialogViewController>();
            ViewController.PresentModally(controller);
        }

        void ITitleMenuViewDelegate.OnDeleteUserData()
        {
            // ユーザーデータ消去
            UserDataDeleteDialog();
        }

        void UserDataDeleteDialog()
        {
            // ユーザーデータ削除確認ダイアログ表示
            MessageViewUtil.ShowMessageWith2Buttons(
                "ユーザーデータ削除",
                "端末に保存されている\n" +
                "ユーザーデータを削除しますか？\n\n" +
                "<size=24>※必ずお読みください※</size>\n\n" +
                "<size=24>ユーザーデータを削除するとゲームを</size>\n" +
                "<size=24>最初から始められます</size>\n\n" +
                "<size=24>ユーザーデータを復旧したい場合は</size>\n" +
                "<size=24>削除前にアカウント連携を行なってください</size>\n",
                "アカウント連携されていない\n" +
                "ユーザーデータは元に戻せません",
                "OK",
                "キャンセル",
                async () =>
                {
                    // ユーザーデータ削除確認ダイアログ表示
                    UserDataDeleteConfirmDialog();
                });
        }

        void UserDataDeleteConfirmDialog()
        {
            // ユーザーデータ削除確認ダイアログ表示
            MessageViewUtil.ShowMessageWith2Buttons(
                "確認",
                "ユーザーデータを削除して\n" +
                "アプリを再起動します\n\n" +
                "よろしいですか？\n",
                "アカウント連携されていないユーザーデータは\n" +
                "元に戻せません",
                "OK",
                "キャンセル",
                async () =>
                {
                    DoAsync.Invoke(TitleViewController.ActualView, ScreenInteractionControl, async cancellationToken =>
                    {
                        await UserDataDeleteUseCase.DeleteUserData(cancellationToken);

                        // アプリ再起動
                        ApplicationRebootor.Reboot();
                    });
                });
        }
    }
}
