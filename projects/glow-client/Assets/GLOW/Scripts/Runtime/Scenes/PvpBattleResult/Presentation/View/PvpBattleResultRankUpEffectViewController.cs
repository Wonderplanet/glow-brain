using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.Pvp;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.PvpBattleResult.Presentation.View
{
    public class PvpBattleResultRankUpEffectViewController : 
        UIViewController<PvpBattleResultRankUpEffectView>, 
        IEscapeResponder
    {
        public record Argument(PvpRankClassType RankType, PvpRankLevel RankLevel);
        
        public Action OnCloseCompletion { get; set; }
        
        [Inject] IPvpBattleResultRankUpEffectViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        
        public override void ViewDidAppear()
        {
            base.ViewDidAppear();
            ViewDelegate.OnViewDidAppear();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            
            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }

        public override void UnloadView()
        {
            base.UnloadView();
            ViewDelegate.OnUnloadView();
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            EscapeResponderRegistry.Unregister(this);
        }
        
        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            OnCloseButtonTapped();
            return true;
        }

        public void SetupRank(
            PvpRankClassType rankType, 
            PvpRankLevel rankLevel)
        {
            ActualView.SetupRankIcon(rankType);
            ActualView.SetupRankText(rankType, rankLevel);
        }

        public async UniTask PlayRankUpEffectAnimation(
            CancellationToken cancellationToken)
        {
            await ActualView.PlayRankUpEffectAnimation(cancellationToken);
        }

        public async UniTask PlayFadeInAnimation(
            PvpRankLevel rankLevel,
            CancellationToken cancellationToken)
        {
            await ActualView.PlayFadeInRankIcon(rankLevel, cancellationToken);
            
            await ActualView.PlayFadeInRankLabel(cancellationToken);
            
            await ActualView.PlayFadeInCloseLabel(cancellationToken);
        }
        
        public void SkipRankUpEffectAnimation()
        {
            ActualView.SkipAnimation();
        }
        
        public void SkipFadeInAnimation(PvpRankLevel rankLevel)
        {
            ActualView.SkipFadeInRankIcon(rankLevel);
            
            ActualView.ShowRankLabel();
            
            ActualView.ShowCloseLabel();
        }
        
        public void ShowSkipButton()
        {
            ActualView.ShowSkipButton();
        }
        
        public void HideSkipButton()
        {
            ActualView.HideSkipButton();
        }
        
        public void ShowCloseButton()
        {
            ActualView.ShowCloseButton();
        }
        
        public void HideCloseButton()
        {
            ActualView.HideCloseButton();
        }
        
        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnCloseButtonTapped();
            OnCloseCompletion?.Invoke();
        }
        
        [UIAction]
        void OnSkipButtonTapped()
        {
            ViewDelegate.OnSkipButtonTapped();
        }
    }
}