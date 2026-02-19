using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Scenes.PvpBattleResult.Presentation.ViewModel;
using UIKit;
using Zenject;

namespace GLOW.Scenes.PvpBattleResult.Presentation.View
{
    public class PvpBattleResultViewController : UIViewController<PvpBattleResultView>
    {
        public record Argument(PvpBattleResultPointViewModel ViewModel);

        public Action OnCloseAction { get; set; }

        [Inject] IPvpBattleResultViewDelegate PvpBattleResultViewDelegate { get; }

        public override void ViewDidAppear()
        {
            base.ViewDidAppear();
            PvpBattleResultViewDelegate.OnViewDidAppear();
        }

        public override void UnloadView()
        {
            base.UnloadView();
            PvpBattleResultViewDelegate.OnUnloadView();
        }

        public async UniTask PlayDetailPointSlideInAnimation(
            CancellationToken cancellationToken,
            PvpPoint resultPoint,
            PvpPoint opponentBonusPoint,
            PvpPoint timeBonusPoint)
        {
            ActualView.InitializePvpResultUi();
            await ActualView.PlayDetailPointSlideInAnimation(cancellationToken);
            await ActualView.PlayDetailPointCountAnimation(
                cancellationToken,
                resultPoint,
                opponentBonusPoint,
                timeBonusPoint);
            
            await ActualView.PlayArrowFadeInAnimation(cancellationToken);
        }

        public async UniTask PlayTotalPointSlideInAnimation(CancellationToken cancellationToken)
        {
            await ActualView.PlayTotalPointSlideInAnimation(cancellationToken);
        }

        public async UniTask PlayTotalPointCountAnimation(
            CancellationToken cancellationToken,
            PvpPoint winAddPoint,
            PvpPoint totalPoint)
        {
            await ActualView.PlayTotalPointCountAnimation(
                cancellationToken,
                winAddPoint,
                totalPoint);
        }

        public async UniTask PlayRankPanelAnimation(CancellationToken cancellationToken, PvpBattleResultPointViewModel viewModel)
        {
            await ActualView.PlayRankPanelAnimation(cancellationToken, viewModel);

            ActualView.PlayCloseTextFadeAnimation();

            ActualView.PlayArrowFadeOutAnimation(cancellationToken).Forget();;

            ActualView.PlayScrollBarFadeAnimation(cancellationToken).Forget();
        }

        public void ShowActionButton()
        {
            ActualView.ShowActionButton();
        }

        public void HideActionButton()
        {
            ActualView.HideActionButton();
        }

        public void HideCloseText()
        {
            ActualView.HideCloseTapLabel();
        }

        public void SkipDetailPointAnimation(
            PvpPoint resultPoint,
            PvpPoint opponentBonusPoint,
            PvpPoint timeBonusPoint)
        {
            ActualView.SkipDetailPointSlideInAnimation();

            ActualView.SkipDetailPointCountAnimation(resultPoint, opponentBonusPoint, timeBonusPoint);
            
            ActualView.SkipArrowFadeInAnimation();
        }

        public void SkipTotalPointSlideInAnimation()
        {
            ActualView.SkipTotalPointSlideInAnimation();
        }

        public void SkipTotalPointCountAnimation(PvpPoint winAddPoint, PvpPoint totalPoint)
        {
            ActualView.SkipTotalPointCountAnimation(
                winAddPoint,
                totalPoint);
        }


        public void SkipRankPanelAnimation(PvpBattleResultPointViewModel viewModel)
        {
            ActualView.SkipRankPanelAnimation(viewModel);
            
            ActualView.PlayCloseTextFadeAnimation();
            
            ActualView.SkipArrowFadeOutAnimation();
            
            ActualView.SkipScrollBarFadeAnimation();
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            PvpBattleResultViewDelegate.OnCloseButtonTapped();
            OnCloseAction?.Invoke();
        }

        [UIAction]
        void OnActionButtonTapped()
        {
            PvpBattleResultViewDelegate.OnActionButtonTapped();
        }
    }
}
