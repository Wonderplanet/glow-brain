using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.Home.Presentation.Components;
using GLOW.Scenes.IdleIncentiveTop.Presentation.ViewModels;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using GLOW.Scenes.InGame.Presentation.Field;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.IdleIncentiveTop.Presentation.Views
{
    public sealed class IdleIncentiveTopView : UIView
    {
        [Header("現在探索情報")]
        [SerializeField] UIText _elapsedTime;
        [SerializeField] Color _maxTimeColor;
        [SerializeField] UIText _oneHourCoinReward;
        [SerializeField] UIText _passEffectCoinReward;
        [SerializeField] UIText _oneHourExpReward;
        [SerializeField] UIText _passEffectExpReward;
        [SerializeField] UIText _maxHour;

        [Header("ボタン")]
        [SerializeField] IdleIncentiveTopButtonControl _buttonControl;

        [Header("探索アニメーション/報酬一覧")]
        [SerializeField] IdleIncentiveRewardList _rewardList;
        [SerializeField] IdleIncentiveTimeLineAnimationControl _timeLineAnimationControl;
        [SerializeField] GameObject _explorationTextObj;

        [Header("パス効果適用表示")]
        [SerializeField] HomeHeldPassBannerComponent _heldPassBannerComponent;

        public UniTask InitializeBattleEffectManager(CancellationToken cancellationToken)
        {
            return _timeLineAnimationControl.InitializeBattleEffectManager(cancellationToken);
        }

        public void Setup(IdleIncentiveTopViewModel topViewModel)
        {
            _buttonControl.Setup(topViewModel.EnableQuickReward);
            _oneHourCoinReward.SetText(topViewModel.ToDisplayAmountPerHour(topViewModel.OneHourCoinReward, 16));
            _oneHourExpReward.SetText(topViewModel.ToDisplayAmountPerHour(topViewModel.OneHourExpReward, 16));
            _passEffectCoinReward.SetText("+{0}", topViewModel.ToDisplayAmountPerHour(topViewModel.PassEffectCoinReward, 14));
            _passEffectExpReward.SetText("+{0}", topViewModel.ToDisplayAmountPerHour(topViewModel.PassEffectExpReward, 14));
            _passEffectCoinReward.IsVisible = !topViewModel.PassEffectCoinReward.IsEmpty();
            _passEffectExpReward.IsVisible = !topViewModel.PassEffectExpReward.IsEmpty();
            _maxHour.SetText(topViewModel.MaxIdleIncentiveHour);
            _heldPassBannerComponent.SetUp(topViewModel.HeldPassEffectDisplayViewModels);
        }

        public void UpdateReceiveInterval(string intervalTime)
        {
            _buttonControl.UpdateInterval(intervalTime);
        }

        public void UpdateRewardList(IdleIncentiveRewardListViewModel viewModel)
        {
            _rewardList.Setup(viewModel);
            _explorationTextObj.SetActive(viewModel.Rewards.IsEmpty());
        }

        public void SetElapsedTime(string elapsedTime, bool isMax)
        {
            _elapsedTime.SetText(elapsedTime);
            _elapsedTime.SetColor(isMax ? _maxTimeColor : Color.white);
        }

        public void SetupBackground(KomaBackgroundAssetPath assetPath)
        {
            _timeLineAnimationControl.SetupBackground(assetPath);
        }

        public void SetupAnimation(
            UnitImage playerUnit,
            UnitImage enemyUnit,
            UnitAttackViewInfo attackViewInfo,
            IdleIncentiveTopCharacterViewModel characterViewModel)
        {
            _timeLineAnimationControl.SetupPlayableAsset(characterViewModel.PlayerUnitRoleType);

            _timeLineAnimationControl.SetupAnimation(
                playerUnit,
                enemyUnit,
                characterViewModel.PlayerCharacterAttackDelay,
                characterViewModel.PlayerCharacterAttackRange,
                attackViewInfo,
                characterViewModel.EnemyIsPhantomized);
        }

        public void PlayRewardListCellAppearanceAnimation()
        {
            _rewardList.PlayCellAppearanceAnimation();
        }
    }
}
