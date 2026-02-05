using System;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Modules.CommonToast.Presentation;
using UIKit;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Views
{
    public class QuestReleaseViewController : UIViewController<QuestReleaseView>, IEscapeResponder
    {
        public record Argument(
            QuestImageAssetPath QuestImageAssetPath,
            QuestName QuestName,
            QuestFlavorText FlavorText);

        [Inject] Argument Args { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        public Action OnAnimationCompletion { get; set; }
        public Action OnCloseCompletion { get; set; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            EscapeResponderRegistry.Bind(this, ActualView);
            ActualView.QuestImageAssetPath = Args.QuestImageAssetPath.Value;
            ActualView.QuestName = Args.QuestName.Value;
            ActualView.FlavorText = Args.FlavorText.Value;
            //アニメーションで「タップで閉じる」が表示されるあたりで閉じるボタン有効にする
            DoAsync.Invoke(ActualView, async cancellationToken =>
            {
                await UniTask.Delay(TimeSpan.FromSeconds(2.5f), cancellationToken: cancellationToken);
                OnAnimationCompletion?.Invoke();
                ActualView.CloseButton.interactable = true;
            });
        }

        void CloseAnimation()
        {
            ActualView.CanvasGroup.alpha = 1f;
            ActualView.CanvasGroup
                .DOFade(0f, 0.2f)
                .OnComplete(() => Dismiss())
                .Play();
        }

        bool IEscapeResponder.OnEscape()
        {
            if(View.Hidden) return false;

            if (ActualView.CloseButton.interactable)
            {
                CloseAnimation();
                OnCloseCompletion?.Invoke();
            }
            else
            {
                CommonToastWireFrame.ShowInvalidOperationMessage();
            }
            return true;
        }

        [UIAction]
        void OnClose()
        {
            CloseAnimation();
            OnCloseCompletion?.Invoke();
        }


    }
}
