using Cysharp.Text;
using GLOW.Core.Domain.Modules.LocalNotification;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Exceptions;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Modules.Tutorial.Domain.UseCases;
using GLOW.Scenes.UserNameEdit.Domain.UseCases;
using GLOW.Scenes.UserNameEdit.Presentation.ViewModels;
using GLOW.Scenes.UserNameEdit.Presentation.Views;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using Zenject;

namespace GLOW.Scenes.UserNameEdit.Presentation.Presenters
{
    public class TutorialUserNameEditDialogPresenter : IUserNameEditDialogViewDelegate
    {
        [Inject] GetUserNameUseCase GetUserNameUseCase { get; }
        [Inject] UpdateUserNameUseCase UpdateUserNameUseCase { get; }
        [Inject] UserNameEditDialogViewController ViewController { get; }
        [Inject] ProgressTutorialStatusUseCase ProgressTutorialStatusUseCase { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] ILocalNotificationScheduler LocalNotificationScheduler { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }

        UserName _beforeUserName;

        void IUserNameEditDialogViewDelegate.ViewDidLoad()
        {
            var model = GetUserNameUseCase.GetUserName();

            var viewModel = new UserNameEditDialogViewModel(model.UserName, model.IsCanChangeName, model.RemainingTimeSpan);
            _beforeUserName = viewModel.UserName;
            ViewController.SetUserName(viewModel);

            // チュートリアル専用処理
            ViewController.SetTutorialLayout();
        }

        void IUserNameEditDialogViewDelegate.OnSaveButtonTapped(string newUserName)
        {
            if (newUserName == string.Empty)
            {
                ViewController.SetEmptyNameMessage();
                return;
            }

            if (_beforeUserName.Value == newUserName)
            {
                ViewController.SetDifferentNameMessage();
                return;
            }

            MessageViewUtil.ShowConfirmMessage(
                "リーダーネームの確認",
                ZString.Format("リーダーネームは\n{0}\nでよろしいですか？", newUserName),
                "",
                onOk:() => OnSave(newUserName),
                prefabName:"UserNameEditConfirmView"//richText対策に独自のPrefabを指定
                );

        }

        void OnSave(string newUserName)
        {
            DoAsync.Invoke(ViewController.View, ScreenInteractionControl, async cancellationToken =>
            {
                try
                {
                    await UpdateUserNameUseCase.UpdateUserName(cancellationToken, newUserName);
                    // チュートリアル情報の更新
                    await ProgressTutorialStatusUseCase.ProgressTutorialStatus(cancellationToken);
                    // ローカル通知の更新
                    LocalNotificationScheduler.RefreshTutorialSchedule();
                    ViewController.OnConfirmed?.Invoke();
                    ViewController.Dismiss();
                }
                catch (NgWordException)
                {
                    ViewController.SetCannotUseNameMessage();
                }
                catch (PlayerNameSpaceFirstException)
                {
                    ViewController.SetCannotUseNameMessage();
                }
                catch (PlayerNameOverByteException)
                {
                    ViewController.SetCannotUseNameMessage();
                }
                catch (ChangeNameCoolTimeException)
                {
                    ViewController.SetCannotChangeNameMessage();
                }
            });
        }

        void IUserNameEditDialogViewDelegate.OnCloseButtonTapped()
        {
            ViewController.Dismiss();
        }
    }
}
