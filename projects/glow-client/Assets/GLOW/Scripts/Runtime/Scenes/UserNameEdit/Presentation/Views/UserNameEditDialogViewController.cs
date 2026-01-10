using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UserNameEdit.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.UserNameEdit.Presentation.Views
{
    public class UserNameEditDialogViewController : UIViewController<UserNameEditDialogView>
    {
        [Inject] IUserNameEditDialogViewDelegate ViewDelegate { get; }

        public Action OnConfirmed { get; set; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.ViewDidLoad();
        }

        public void SetUserName(UserNameEditDialogViewModel viewModel)
        {
            ActualView.SetUserName(viewModel.UserName);
        }

        public void SetCannotChangeNameMessage()
        {
            ActualView.SetTitle("前回の変更から24時間は\r\n再変更が行えません");
        }

        public void SetEmptyNameMessage()
        {
            ActualView.SetMessage("リーダーネームを入力してください");
        }

        public void SetCannotUseNameMessage()
        {
            ActualView.SetMessage("このリーダーネームは使用できません");
        }

        public void SetDifferentNameMessage()
        {
            ActualView.SetMessage("リーダーネームを変更してください");
        }

        public void SetOkButtonGrayOut()
        {
            ActualView.SetOkButtonGrayOut();
        }

        public void SetInputFieldGrayOut()
        {
            ActualView.SetInputFieldGrayOut();
        }

        public void SetRemainingTimeSpan(RemainingTimeSpan remainingTimeSpan)
        {
            ActualView.SetRemainingTimeSpan(remainingTimeSpan);
        }

        public void SetTutorialLayout()
        {
            ActualView.SetHeaderText("リーダーネームの登録");
            ActualView.SetUserName(new UserName(""));
            ActualView.HideTutorialButton();
            ActualView.SetAttentionText(
                "※リーダーネームはゲーム内で公開されます。" +
                "\n※リーダーネームはあとから変更できます" +
                "\n※本名、メールアドレス、電話番号などの" +
                "\n\u3000個人情報や個人の特定につながる情報は" +
                "\n\u3000入力しないでください。");
        }

        [UIAction]
        void OnSaveButtonClicked()
        {
            ViewDelegate.OnSaveButtonTapped(ActualView.InputText);
        }

        [UIAction]
        void OnCloseButtonClicked()
        {
            ViewDelegate.OnCloseButtonTapped();
        }
    }
}
