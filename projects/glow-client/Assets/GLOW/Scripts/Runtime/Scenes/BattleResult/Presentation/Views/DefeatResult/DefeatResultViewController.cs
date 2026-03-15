using System;
using Cysharp.Threading.Tasks;
using GLOW.Scenes.BattleResult.Presentation.ViewModels;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UIKit;
using Zenject;

namespace GLOW.Scenes.BattleResult.Presentation.Views.DefeatResult
{
    /// <summary>
    /// 53_バトルリザルト
    /// 　53-2_敗北リザルト
    /// 　　53-2-1_敗北画面
    /// </summary>
    public class DefeatResultViewController : UIViewController<DefeatResultView>
    {
        public record Argument(DefeatResultViewModel ViewModel, Action OnViewClosed, Func<UniTask> OnRetrySelected);

        [Inject] IDefeatResultViewDelegate ViewDelegate { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();

            ViewDelegate.OnViewDidUnload();
        }

        public void Setup(DefeatResultViewModel viewModel)
        {
            ActualView.Setup(viewModel);
        }

        public void PlayDefeatResultAnimation()
        {
            ActualView.PlayDefeatResultAnimation();
        }

        public void ActiveCloseButton()
        {
            ActualView.ActiveCloseButton();
        }

        public void ActiveCloseText()
        {
            ActualView.ActiveCloseText();
        }
        
        public void SetActiveRetryButton(RetryAvailableFlag isRetryAvailable)
        {
            ActualView.SetActiveRetryButton(isRetryAvailable);
        }

        [UIAction]
        void OnCloseSelected()
        {
            ViewDelegate.OnCloseSelected();
        }
        
        [UIAction]
        void OnRetryTapped()
        {
            ViewDelegate.OnRetrySelected();
        }
    }
}
