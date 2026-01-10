using Cysharp.Threading.Tasks;
using GLOW.Scenes.AccountDeleteConfirmDialog.Presentation.Views;
using GLOW.Scenes.OtherMenu.Domain;
using WonderPlanet.OpenURLExtension;
using WonderPlanet.UniTaskSupporter;
using Zenject;

namespace GLOW.Scenes.AccountDeleteConfirmDialog.Presentation.Presenters
{
    public class AccountDeleteConfirmDialogPresenter : IAccountDeleteConfirmDialogViewDelegate
    {
        const int CloseDelayMilliseconds = 40;

        [Inject] AccountDeleteConfirmDialogViewController ViewController { get; }
        [Inject] GetDeleteAccountUrlUseCase GetDeleteAccountUrlUseCase { get; }

        void IAccountDeleteConfirmDialogViewDelegate.OnAccountDeleteConfirm()
        {
            var url = GetDeleteAccountUrlUseCase.GetDeleteAccountUrl();
            OpenURL(url);
        }

        void IAccountDeleteConfirmDialogViewDelegate.OnClose()
        {
            ViewController.Dismiss();
        }

        void OpenURL(string url)
        {
            DoAsync.Invoke(ViewController.ActualView, async cancellationToken =>
            {
                ViewController.ActualView.UserInteraction = false;
                await UniTask.Delay(CloseDelayMilliseconds, cancellationToken: cancellationToken);
                CustomOpenURL.OpenURL(url);
                ViewController.ActualView.UserInteraction = true;
            });
        }
    }
}
