using Cysharp.Threading.Tasks;
using GLOW.Scenes.Inquiry.Presentation.View;
using UnityEngine;
using WonderPlanet.OpenURLExtension;
using WonderPlanet.ToastNotifier;
using WonderPlanet.UniTaskSupporter;
using Zenject;

namespace GLOW.Scenes.Inquiry.Presentation.Presenter
{
    public class InquiryDialogPresenter : IInquiryDialogViewDelegate
    {
        const int CloseDelayMilliseconds = 40;

        [Inject] InquiryDialogViewController ViewController { get; }
        [Inject] InquiryDialogViewController.Argument Argument { get; }

        public void OnDidLoad()
        {
            ViewController.Initialize(Argument.ViewModel);
        }

        public void OnCopyUserID()
        {
            if (Argument.ViewModel.MyId.IsEmpty())
            {
                Debug.LogError($"UserIdが空のためコピーしませんでした");
                return;
            }

            GUIUtility.systemCopyBuffer = Argument.ViewModel.MyId.Value;
            var toast = Toast.MakeText("IDをコピーしました");
            toast.SetGravity(Gravities.Bottom, new Vector2(0, 200));
            toast.Show();
        }

        public void OnInquiry()
        {
            DoAsync.Invoke(ViewController.ActualView, async cancellationToken =>
            {
                ViewController.ActualView.UserInteraction = false;
                await UniTask.Delay(CloseDelayMilliseconds, cancellationToken: cancellationToken);
                CustomOpenURL.OpenURL(Argument.ViewModel.InquiryURL);
                ViewController.ActualView.UserInteraction = true;
            });
        }

        public void OnCancel()
        {
            ViewController.Dismiss();
        }
    }
}
