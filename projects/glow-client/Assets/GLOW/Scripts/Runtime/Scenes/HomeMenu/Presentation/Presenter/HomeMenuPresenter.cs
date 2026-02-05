using System;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.Community.Presentation.View;
using GLOW.Scenes.HomeHelpDialog.Presentation.Views;
using GLOW.Scenes.HomeMenu.Domain.UseCase;
using GLOW.Scenes.HomeMenu.Presentation.View;
using GLOW.Scenes.HomeMenuSetting.Presentation.View;
using GLOW.Scenes.Inquiry.Domain.UseCases;
using GLOW.Scenes.Inquiry.Presentation.View;
using GLOW.Scenes.LinkBnIdDialog.Presentation.Views;
using GLOW.Scenes.OtherMenu.Presentation;
using GLOW.Scenes.UnlinkBnIdDialog.Presentation.Views;
using WonderPlanet.ToastNotifier;
using WPFramework.Modules.Localization.Terms;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.HomeMenu.Presentation.Presenter
{
    public class HomeMenuPresenter : IHomeMenuDelegate
    {
        [Inject] HomeMenuViewController ViewController { get; }
        [Inject] IApplicationRebootor ApplicationRebootor { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] ILocalizationTermsSource Terms { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] GetInquiryModelUseCase GetInquiryModelUseCase { get; }
        [Inject] LinkedBnIdCheckUseCase LinkedBnIdCheckUseCase { get; }

        void IHomeMenuDelegate.OnCloseSelected()
        {
            ViewController.Dismiss();
        }

        void IHomeMenuDelegate.OnSettingSelected()
        {
            var controller = ViewFactory.Create<HomeMenuSettingViewController>();
            ViewController.PresentModally(controller);
        }

        void IHomeMenuDelegate.OnAccountCooperateSelected()
        {
            var linkedDateTimeOffset = LinkedBnIdCheckUseCase.GetLinkedBnIdAt();
            // BNID連携日時が初期値(未登録)の場合
            if (linkedDateTimeOffset == DateTimeOffset.MinValue)
            {
                var controller = ViewFactory.Create<LinkBnIdDialogViewController>();
                ViewController.PresentModally(controller);
            }
            else
            {
                var controller = ViewFactory.Create<UnlinkBnIdDialogViewController>();
                ViewController.PresentModally(controller);
            }
        }

        void IHomeMenuDelegate.OnHelpSelected()
        {
            var controller = ViewFactory.Create<HomeHelpDialogViewController>();
            ViewController.PresentModally(controller);
        }

        void IHomeMenuDelegate.OnCommunitySelected()
        {
            var controller = ViewFactory.Create<CommunityMenuViewController>();
            ViewController.PresentModally(controller);
        }

        void IHomeMenuDelegate.OnInquirySelected()
        {
            var useCaseModel = GetInquiryModelUseCase.GetInquiryModel();
            var argument = new InquiryDialogViewController.Argument(
                new InquiryDialogViewModel(
                    useCaseModel.MyId,
                    useCaseModel.InquiryURL));
            var inquiryDialogViewController = ViewFactory.Create<
                InquiryDialogViewController,
                InquiryDialogViewController.Argument>(argument);
            ViewController.PresentModally(inquiryDialogViewController);
        }

        void IHomeMenuDelegate.OnOtherMenuSelected()
        {
            var otherMenuViewController = ViewFactory.Create<OtherMenuViewController>();
            ViewController.PresentModally(otherMenuViewController);
        }

        void IHomeMenuDelegate.OnTitleBackSelected()
        {
            MessageViewUtil.ShowMessageWith2Buttons(
                Terms.Get("common_confirm_title"),
                "タイトル画面に戻りますか？",
                "",
                "はい",
                "キャンセル",
                () => ViewController.Dismiss(completion: () => ApplicationRebootor.Reboot()),
                () => { });
        }
    }
}
