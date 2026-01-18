using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Modules.Spine.Presentation;
using GLOW.Scenes.AdventBattle.Presentation.Calculator.Model;
using GLOW.Scenes.AdventBattle.Presentation.Component;
using GLOW.Scenes.AdventBattle.Presentation.ValueObject;
using GLOW.Scenes.AdventBattle.Presentation.ViewModel;
using GLOW.Scenes.InGame.Presentation.Field;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.AdventBattle.Presentation.View
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-1_降臨バトル基礎実装
    /// 　　44-1-1_降臨バトルトップ
    /// </summary>
    public class AdventBattleTopView : UIView
    {
        [Header("タイトル")]
        [SerializeField] UIText _titleText;

        [Header("上段UI")]
        [SerializeField] AdventRaidBattleScoreComponent _adventRaidBattleScoreComponent;

        [Header("右側ボタンの通知")]
        [SerializeField] UIImage _rewardListButtonNotification;
        [SerializeField] UIImage _missionButtonNotification;

        [Header("吹き出し")]
        [SerializeField] UIObject _bossDescriptionBalloon;
        [SerializeField] UIText _bossDescriptionText;

        [Header("敵キャラ")]
        [SerializeField] UISpineWithOutlineAvatar _enemyUnitFirst;
        [SerializeField] UISpineWithOutlineAvatar _enemyUnitSecond;
        [SerializeField] UISpineWithOutlineAvatar _enemyUnitThird;

        [Header("降臨バトルTOP背景")]
        [SerializeField] UIImage _adventBattleTopBackgroundImage;

        [Header("終了までの時間表示")]
        [SerializeField] UIObject _remainingTimeSpanRoot;
        [SerializeField] UIText _remainingTimeSpanText;

        [Header("中段UI")]
        [SerializeField] AdventBattleScoreComponent _adventBattleScoreComponent;
        [SerializeField] AdventBattleHighScoreRewardListComponent _adventBattleHighScoreRewardListComponent;

        [Header("ボタン一覧")]
        [SerializeField] Button _infoButton;
        [SerializeField] Button _bonusUnitButton;
        [SerializeField] Button _rankingButton;
        [SerializeField] Button _rewardListButton;
        [SerializeField] Button _missionButton;

        [Header("下部ボタン")]
        [SerializeField] AdventBattleTopStartButtonComponent _startButtonComponent;
        [SerializeField] Button _ruleBalloon;
        [SerializeField] UIObject _ruleBalloonButton;

        [Header("ランキングボタン部分")]
        [SerializeField] UIObject _rankingButtonBalloon;

        [Header("キャンペーン部分")]
        [SerializeField] CampaignBalloonMultiSwitcherComponent _campaignBalloonSwitcher;

        public void Setup(AdventBattleTopViewModel viewModel,
            Action<PlayerResourceIconViewModel> rewardIconAction)
        {
            _titleText.SetText(viewModel.AdventBattleName.Value);

            if(viewModel.BattleType == AdventBattleType.Raid)
            {
                _adventRaidBattleScoreComponent.Hidden = false;
                _adventRaidBattleScoreComponent.Setup(viewModel.RaidTotalScore, viewModel.RequiredLowerRaidTotalScore);
            }
            else
            {
                _adventRaidBattleScoreComponent.Hidden = true;
            }

            SetUpRemainingTimeSpan(viewModel.AdventBattleRemainingTimeSpan);
            SetUpBossDescription(viewModel.AdventBattleBossDescription);

            _adventBattleHighScoreRewardListComponent.Setup(
                viewModel.HighScoreRewards,
                viewModel.HighScoreGaugeViewModel,
                rewardIconAction);

            // 特別ルール吹き出し(設定がなければ非表示)
            _ruleBalloonButton.Hidden = !viewModel.ExistsSpecialRule;

            _startButtonComponent.Setup(
                viewModel.ChallengeableCount,
                viewModel.AdChallengeableCount,
                viewModel.PartyName,
                viewModel.HeldAdSkipPassInfoViewModel);

            SetUpCampaignBalloons(viewModel.CampaignViewModels);
        }

        public async UniTask PlayHighScoreGaugeAndRewardAnimation(
            CancellationToken cancellationToken,
            AdventBattleHighScoreGaugeRateElementModel rateModel,
            bool scrollAnimationPlaying)
        {
            await PlayHighScoreListScrollAnimation(cancellationToken, rateModel.AdventBattleHighScoreGaugeRate);
            if (rateModel.AdventBattleHighScoreRewardObtainedFlag)
            {
                await PlayObtainRewardAnimation(cancellationToken, rateModel.HighScoreRewardCellIndex);
                if (scrollAnimationPlaying)
                {
                    await UniTask.Delay(TimeSpan.FromSeconds(0.15f), cancellationToken: cancellationToken);
                    PlayHighScoreGaugeScrollAnimation();
                }
            }
        }

        public void SetButtonInteractable(bool interactable)
        {
            _infoButton.interactable = interactable;
            _bonusUnitButton.interactable = interactable;
            _rankingButton.interactable = interactable;
            _rewardListButton.interactable = interactable;
            _missionButton.interactable = interactable;
            _ruleBalloon.interactable = interactable;
            _startButtonComponent.SetButtonInteractable(interactable);
        }

        public void UpdateHighScoreRewardsAfterObtained(
            IReadOnlyList<AdventBattleHighScoreRewardViewModel> highScoreRewards,
            Action<PlayerResourceIconViewModel> rewardAction)
        {
            _adventBattleHighScoreRewardListComponent.UpdatedHighScoreRewardsAfterObtained(highScoreRewards, rewardAction);
        }

        public void UpdatePartyName(PartyName partyName)
        {
            _startButtonComponent.SetPartyName(partyName);
        }

        public void PlayPickUpRewardEffect()
        {
            _adventBattleHighScoreRewardListComponent.PlayPickUpRewardEffect();
        }

        public void SetUpAdventBattleScoreComponent(AdventBattleTopViewModel viewModel)
        {
            _adventBattleScoreComponent.Setup(
                viewModel.TotalScore,
                viewModel.RequiredLowerScore,
                viewModel.MaxScore,
                viewModel.CurrentRankType,
                viewModel.CurrentScoreRankLevel);
        }

        public void SetUpEnemyUnitImages(
            UnitImageAssetPath enemyUnitFirstImageAssetPath,
            UnitImageAssetPath enemyUnitSecondImageAssetPath,
            UnitImageAssetPath enemyUnitThirdImageAssetPath,
            UnitImage unitImageFirst,
            UnitImage unitImageSecond,
            UnitImage unitImageThird)
        {
            if (enemyUnitFirstImageAssetPath.IsEmpty())
            {
                _enemyUnitFirst.Hidden = true;
            }
            else
            {
                _enemyUnitFirst.Hidden = false;
                _enemyUnitFirst.SetAvatarScale(unitImageFirst.SkeletonScale);
                _enemyUnitFirst.SetSkeleton(unitImageFirst.SkeletonAnimation.skeletonDataAsset);
                _enemyUnitFirst.Flip = true;

                if (_enemyUnitFirst.IsFindAnimation(CharacterUnitAnimation.MirrorWait.Name))
                {
                    _enemyUnitFirst.Animate(CharacterUnitAnimation.MirrorWait.Name);
                }
                else
                {
                    _enemyUnitFirst.Animate(CharacterUnitAnimation.Wait.Name);
                }
            }

            if (enemyUnitSecondImageAssetPath.IsEmpty())
            {
                _enemyUnitSecond.Hidden = true;
            }
            else
            {
                _enemyUnitSecond.Hidden = false;
                _enemyUnitSecond.SetAvatarScale(unitImageSecond.SkeletonScale);
                _enemyUnitSecond.SetSkeleton(unitImageSecond.SkeletonAnimation.skeletonDataAsset);
                _enemyUnitSecond.Flip = true;

                if (_enemyUnitSecond.IsFindAnimation(CharacterUnitAnimation.MirrorWait.Name))
                {
                    _enemyUnitSecond.Animate(CharacterUnitAnimation.MirrorWait.Name);
                }
                else
                {
                    _enemyUnitSecond.Animate(CharacterUnitAnimation.Wait.Name);
                }
            }

            if (enemyUnitThirdImageAssetPath.IsEmpty())
            {
                _enemyUnitThird.Hidden = true;
            }
            else
            {
                _enemyUnitThird.Hidden = false;
                _enemyUnitThird.SetAvatarScale(unitImageThird.SkeletonScale);
                _enemyUnitThird.SetSkeleton(unitImageThird.SkeletonAnimation.skeletonDataAsset);
                _enemyUnitThird.Flip = true;

                if (_enemyUnitThird.IsFindAnimation(CharacterUnitAnimation.MirrorWait.Name))
                {
                    _enemyUnitThird.Animate(CharacterUnitAnimation.MirrorWait.Name);
                }
                else
                {
                    _enemyUnitThird.Animate(CharacterUnitAnimation.Wait.Name);
                }
            }
        }

        public void SetAdventBattleMissionBadge(NotificationBadge badge)
        {
            _missionButtonNotification.Hidden = !badge;
        }

        public void SetUpRankingButtonBalloon(AdventBattleRankingCalculatingFlag calculatingRankings)
        {
            _rankingButtonBalloon.Hidden = !calculatingRankings;
        }

        public void SetTopBackgroundImage(KomaBackgroundAssetPath komaBackgroundAssetPath)
        {
            UISpriteUtil.LoadSpriteWithFadeIfNotLoaded(
                _adventBattleTopBackgroundImage.Image,
                komaBackgroundAssetPath.Value);
        }

        async UniTask PlayHighScoreListScrollAnimation(
            CancellationToken cancellationToken,
            AdventBattleHighScoreGaugeRate gaugeRate)
        {
            await _adventBattleHighScoreRewardListComponent.PlayHighScoreGaugeScrollAnimation(cancellationToken, gaugeRate);
        }

        void PlayHighScoreGaugeScrollAnimation()
        {
            _adventBattleHighScoreRewardListComponent.PlayScrollToNextRewardAnimation();
        }

        async UniTask PlayObtainRewardAnimation(CancellationToken cancellationToken, HighScoreRewardCellIndex obtainedRewardIndex)
        {
            await _adventBattleHighScoreRewardListComponent.PlayObtainRewardAnimation(cancellationToken, obtainedRewardIndex);
        }

        void SetUpRemainingTimeSpan(RemainingTimeSpan remainingTimeSpan)
        {
            var isInvisibleRemainingTimeSpan = remainingTimeSpan.IsEmpty();
            _remainingTimeSpanRoot.Hidden = isInvisibleRemainingTimeSpan;
            if (!isInvisibleRemainingTimeSpan)
            {
                _remainingTimeSpanText.SetText(TimeSpanFormatter.FormatUntilEnd(remainingTimeSpan));
            }
        }

        void SetUpBossDescription(AdventBattleBossDescription bossDescription)
        {
            _bossDescriptionText.SetText(bossDescription.Value);
            _bossDescriptionBalloon.Hidden = bossDescription.IsEmpty();
        }

        void SetUpCampaignBalloons(List<CampaignViewModel> campaignViewModels)
        {
            _campaignBalloonSwitcher.SetUpCampaignBalloons(campaignViewModels);
        }
    }
}
