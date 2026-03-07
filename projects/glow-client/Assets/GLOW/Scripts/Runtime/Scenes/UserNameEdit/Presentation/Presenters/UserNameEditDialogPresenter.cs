using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Exceptions;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.UserNameEdit.Domain.UseCases;
using GLOW.Scenes.UserNameEdit.Presentation.ViewModels;
using GLOW.Scenes.UserNameEdit.Presentation.Views;
using WonderPlanet.UniTaskSupporter;
using Zenject;

namespace GLOW.Scenes.UserNameEdit.Presentation.Presenters
{
    public class UserNameEditDialogPresenter : IUserNameEditDialogViewDelegate
    {
        [Inject] GetUserNameUseCase GetUserNameUseCase { get; }
        [Inject] UpdateUserNameUseCase UpdateUserNameUseCase { get; }
        [Inject] UserNameEditDialogViewController ViewController { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }

        UserName _beforeUserName;
        bool _isCanChangeName;

        void IUserNameEditDialogViewDelegate.ViewDidLoad()
        {
            var model = GetUserNameUseCase.GetUserName();
            var viewModel = new UserNameEditDialogViewModel(model.UserName, model.IsCanChangeName, model.RemainingTimeSpan);
            _beforeUserName = viewModel.UserName;
            _isCanChangeName = viewModel.IsCanChangeName;
            ViewController.SetUserName(viewModel);

            if (!viewModel.IsCanChangeName)
            {
                ViewController.SetOkButtonGrayOut();
                ViewController.SetInputFieldGrayOut();
                ViewController.SetCannotChangeNameMessage();
                ViewController.SetRemainingTimeSpan(viewModel.RemainingTimeSpan);
            }
        }

        void IUserNameEditDialogViewDelegate.OnSaveButtonTapped(string newUserName)
        {
            if (!_isCanChangeName) return;

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
            DoAsync.Invoke(ViewController.View, async cancellationToken =>
            {
                try
                {
                    await UpdateUserNameUseCase.UpdateUserName(cancellationToken, newUserName);
                    HomeHeaderDelegate.UpdateStatus();
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
