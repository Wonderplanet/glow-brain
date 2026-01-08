using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Scenes.BattleResult.Domain.Evaluator;
using GLOW.Scenes.BattleResult.Presentation.ViewModels;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.PvpBattleFinishAnimation.Presentation.View
{
    public class PvpBattleFinishAnimationViewController : 
        UIViewController<PvpBattleFinishAnimationView>,
        IEscapeResponder
    {
        public record Argument(PvpResultViewModel ViewModel);
        
        public Action OnCloseButtonTappedAction { get; set; }
        
        [Inject] IPvpBattleFinishAnimationViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        
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
        
        public void InitializeProgressBar(
            PvpResultEvaluator.PvpResultType pvpResultType)
        {
            ActualView.SetCloseButtonInteractable(false);
            ActualView.InitializeProgressBar(pvpResultType);
        }

        public async UniTask PlayFinishAnimation(
            CancellationToken cancellationToken,
            PvpResultViewModel viewModel)
        {
            if (viewModel.FinishType == PvpResultEvaluator.PvpFinishType.OutPostHpZero)
            {
                await ActualView.PlayOutPostHpZeroFinishAnimation(
                    cancellationToken,
                    viewModel.ResultType,
                    viewModel.PlayerDistanceRatio,
                    viewModel.OpponentDistanceRatio);
            }
            else
            {
                await ActualView.PlayTimeUpFinishAnimation(
                    cancellationToken,
                    viewModel.ResultType,
                    viewModel.PlayerDistanceRatio,
                    viewModel.OpponentDistanceRatio);
            }
            ActualView.SetCloseButtonInteractable(true);
        }
        
        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;
            
            ViewDelegate.OnScreenTapped();
            return true;
        }

        [UIAction]
        void OnScreenTapped()
        {
            ViewDelegate.OnScreenTapped();
        }
    }
}