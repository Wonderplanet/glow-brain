using System;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.TitleLinkBnIdDialog.Domain.Models;
using GLOW.Scenes.TitleResultLinkBnIdDialog.Presentation.ViewModels;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.TitleResultLinkBnIdDialog.Presentation.Views
{
    public class TitleBnIdLinkageResultDialogViewController : UIViewController<TitleBnIdLinkageResultDialogView>, IEscapeResponder
    {
        public record Argument(TitleBnIdLinkageResultDialogViewModel ViewModel);

        public Action OnLeftButton { get; set; }
        public Action OnRightButton { get; set; }

        [Inject] ITitleBnIdLinkageResultDialogViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        bool _isLeftButtonTextEmpty;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);

            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();

            EscapeResponderRegistry.Unregister(this);
        }

        public void Setup(TitleBnIdLinkageResultDialogViewModel viewModel)
        {
            ActualView.SetTitleText(viewModel.Title);
            ActualView.SetMessageText(viewModel.Message);
            ActualView.SetDataRootEnabled(
                viewModel.DateTitle,
                viewModel.MyId,
                viewModel.Name,
                viewModel.Level);
            ActualView.SetDateTitleText(viewModel.DateTitle);
            ActualView.SetMyIdText(viewModel.MyId);
            ActualView.SetUserNameText(viewModel.Name);
            ActualView.SetLevelText(viewModel.Level);
            ActualView.SetAttentionMessageText(viewModel.AttentionMessage);
            ActualView.SetLeftButtonText(viewModel.LeftButtonTitle);
            ActualView.SetRightButtonText(viewModel.RightButtonTitle);

            _isLeftButtonTextEmpty = viewModel.LeftButtonTitle.IsEmpty();
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_003);

            // 左ボタンが存在する場合は左ボタンを
            // 左ボタンが存在しない場合は右ボタンを押したことにする
            if (_isLeftButtonTextEmpty) ViewDelegate.OnRightButton();
            else ViewDelegate.OnLeftButton();

            return true;
        }

        [UIAction]
        void OnLeftButtonTapped()
        {
            ViewDelegate.OnLeftButton();
        }

        [UIAction]
        void OnRightButtonTapped()
        {
            ViewDelegate.OnRightButton();
        }
    }
}
