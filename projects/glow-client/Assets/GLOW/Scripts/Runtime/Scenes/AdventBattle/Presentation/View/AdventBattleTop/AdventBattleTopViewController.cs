using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.AdventBattle.Presentation.Calculator.Model;
using GLOW.Scenes.AdventBattle.Presentation.ViewModel;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Presentation.Field;
using UIKit;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.AdventBattle.Presentation.View
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-1_降臨バトル基礎実装
    /// 　　44-1-1_降臨バトルトップ
    /// </summary>
    public class AdventBattleTopViewController : UIViewController<AdventBattleTopView>, IEscapeResponder, IAsyncActivityControl
    {
        [Inject] IAdventBattleTopViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] IUnitImageContainer UnitImageContainer { get; }

        readonly CancellationTokenSource _cancellationTokenSource = new();

        public CancellationToken CancellationToken => _cancellationTokenSource.Token;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            EscapeResponderRegistry.Bind(this, ActualView);
            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);

            ViewDelegate.OnViewWillAppear();
        }

        public void SetupAdventBattleTopView(AdventBattleTopViewModel viewModel)
        {
            ActualView.Setup(viewModel, OnRewardIconSelected);
            SetupRankingButtonBalloon(viewModel.CalculatingRankings);
        }

        public void SetupAdventBattleTopBackgroundImage(KomaBackgroundAssetPath backgroundAssetPath)
        {
            ActualView.SetTopBackgroundImage(backgroundAssetPath);
        }

        public void SetupRankingButtonBalloon(AdventBattleRankingCalculatingFlag calculatingRankings)
        {
            ActualView.SetUpRankingButtonBalloon(calculatingRankings);
        }

        public void SetupEnemyUnitImages(
            UnitImageAssetPath enemyUnitFirstImageAssetPath,
            UnitImageAssetPath enemyUnitSecondImageAssetPath,
            UnitImageAssetPath enemyUnitThirdImageAssetPath)
        {
            ActualView.SetUpEnemyUnitImages(
                enemyUnitFirstImageAssetPath,
                enemyUnitSecondImageAssetPath,
                enemyUnitThirdImageAssetPath,
                InstantiateCharacterImage(enemyUnitFirstImageAssetPath),
                InstantiateCharacterImage(enemyUnitSecondImageAssetPath),
                InstantiateCharacterImage(enemyUnitThirdImageAssetPath));

        }

        public async UniTask PlayHighScoreGaugeScrollAnimation(
            CancellationToken cancellationToken,
            AdventBattleHighScoreGaugeRateElementModel rateModel,
            bool scrollAnimationPlaying)
        {
            await ActualView.PlayHighScoreGaugeAndRewardAnimation(cancellationToken, rateModel, scrollAnimationPlaying);
        }

        public void UpdateHighScoreRewardsAfterObtained(
            IReadOnlyList<AdventBattleHighScoreRewardViewModel> highScoreRewards)
        {
            ActualView.UpdateHighScoreRewardsAfterObtained(highScoreRewards, OnRewardIconSelected);
        }

        public IDisposable ViewTapGuard()
        {
            return this.Activate();
        }

        public void SetAdventBattleMissionBadge(NotificationBadge badge)
        {
            ActualView.SetAdventBattleMissionBadge(badge);
        }

        public void UpdatePartyName(PartyName partyName)
        {
            ActualView.UpdatePartyName(partyName);
        }

        public void PlayPickUpRewardEffect()
        {
            ActualView.PlayPickUpRewardEffect();
        }

        public void SetUpAdventBattleScoreComponent(AdventBattleTopViewModel viewModel)
        {
            ActualView.SetUpAdventBattleScoreComponent(viewModel);
        }
        
        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            ViewDelegate.OnEscape();
            return true;
        }

        void IAsyncActivityControl.ActivityBegin()
        {
            SetInteractable(false);
        }

        void IAsyncActivityControl.ActivityEnd()
        {
            SetInteractable(true);
        }

        UnitImage InstantiateCharacterImage(UnitImageAssetPath imageAssetPath)
        {
            if(imageAssetPath.IsEmpty())
            {
                return null;
            }

            var go = UnitImageContainer.Get(imageAssetPath);
            var characterImage = go.GetComponent<UnitImage>();
            characterImage.SortingOrder = 0;
            return characterImage;
        }

        void OnRewardIconSelected(PlayerResourceIconViewModel viewModel)
        {
            ViewDelegate.OnRewardIconSelected(viewModel);
        }

        void SetInteractable(bool interactable)
        {
            ActualView.UserInteraction = interactable;
            ActualView.SetButtonInteractable(interactable);
        }

        [UIAction]
        void OnHelpButtonTapped()
        {
            ViewDelegate.OnHelpButtonTapped();
        }

        [UIAction]
        void OnEnemyDetailButtonTapped()
        {
            ViewDelegate.OnEnemyDetailButtonTapped();
        }

        [UIAction]
        void OnRankingButtonTapped()
        {
            ViewDelegate.OnRankingButtonTapped();
        }

        [UIAction]
        void OnRewardListButtonTapped()
        {
            ViewDelegate.OnRewardListButtonTapped();
        }

        [UIAction]
        void OnMissionButtonTapped()
        {
            ViewDelegate.OnMissionButtonTapped();
        }

        [UIAction]
        void OnBonusUnitButtonTapped()
        {
            ViewDelegate.OnBonusUnitButtonTapped();
        }

        [UIAction]
        void OnPartyFormationButtonTapped()
        {
            ViewDelegate.OnPartyFormationButtonTapped();
        }

        [UIAction]
        void OnBattleStartButtonTapped()
        {
            ViewDelegate.OnBattleStartButtonTapped();
        }

        [UIAction]
        void OnBackButtonTapped()
        {
            ViewDelegate.OnBackButtonTapped();
        }

        [UIAction]
        void OnSpecialRuleButtonTapped()
        {
            ViewDelegate.OnSpecialRuleButtonTapped();
        }
    }
}
