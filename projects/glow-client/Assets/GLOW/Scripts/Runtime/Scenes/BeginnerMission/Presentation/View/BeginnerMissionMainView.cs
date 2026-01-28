using System;
using System.Collections.Generic;
using System.Diagnostics.CodeAnalysis;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Core.Presentation.Views.Interaction;
using GLOW.Scenes.BeginnerMission.Presentation.Component;
using GLOW.Scenes.Mission.Presentation.Component;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.BeginnerMission.Presentation.View
{
    /// <summary>
    /// 61_ミッション
    /// 　61-1-5_初心者ミッション
    /// </summary>
    public class BeginnerMissionMainView : UIView
    {
        [SerializeField] Transform _contentRoot;

        [SerializeField] UIToggleableComponentGroup _tab;

        [SerializeField] RewardBoxWindowLayerComponent _rewardBoxWindowLayerComponent;
        
        [SerializeField] BeginnerMissionBonusPointComponent _bonusPointComponent;

        [Serializable]
        [SuppressMessage("ReSharper", "InconsistentNaming")]
        public struct DayTabComponentInfo
        {
            public int Key;
            public UIImage BadgeComponent;
            public GameObject LockIconObject;
            public Animator OpenAnimator;
        }
        [SerializeField] DayTabComponentInfo[] _dayTabComponentInfos;

        [SerializeField] Button _bulkReceiveButton;

        [SerializeField] ScreenActivityIndicatorView _indicator;

        [SerializeField] UIObject _bonusPointMissionObject;
        
        [SerializeField] UIObject _tabObject;
        
        public Transform ContentRoot => _contentRoot;

        public ScreenActivityIndicatorView Indicator => _indicator;
        
        public MissionBonusPointAreaComponent BonusPointComponent => _bonusPointComponent.BonusPointAreaComponent;
        
        public void SetMissionComponentVisible(bool visible)
        {
            _bonusPointMissionObject.Hidden = !visible;
            _tabObject.Hidden = !visible;
        }
        
        public void SetToggleOn(BeginnerMissionDayNumber dayNumber)
        {
            var key = ZString.Format("Day{0}", dayNumber.Value);
            _tab.SetToggleOn(key);
        }
        
        public void SetUpLockIconVisible(BeginnerMissionDaysFromStart daysFromStart)
        {
            foreach (var dayTabComponent in _dayTabComponentInfos)
            {
                dayTabComponent.LockIconObject.SetActive(!(dayTabComponent.Key <= daysFromStart.Value));
            }
        }

        public void SetBadgeVisible(BeginnerMissionDayNumber number, bool visible)
        {
            var badgeComponent = _dayTabComponentInfos.Find(x => x.Key == number.Value);
            badgeComponent.BadgeComponent.Hidden = !visible;
        }
        
        public void SetReceivableTotalDiamondAmount(BeginnerMissionPromptPhrase promptPhrase)
        {
            _bonusPointComponent.SetReceivableTotalDiamondAmount(promptPhrase);
        }

        public async UniTask ShowRewardListWindow(
            IReadOnlyList<PlayerResourceIconViewModel> playerResourceIconViewModels, 
            RectTransform windowPosition, 
            CancellationToken cancellationToken)
        {
            await _rewardBoxWindowLayerComponent.SetupWindowComponent(
                playerResourceIconViewModels, 
                windowPosition, 
                cancellationToken);
        }

        public void SetOnSelectRewardInWindow(Action<PlayerResourceIconViewModel> action)
        {
            _rewardBoxWindowLayerComponent.OnSelectRewardInWindow = action;
        }

        public void SetBulkReceiveButtonInteractable(bool interactable)
        {
            _bulkReceiveButton.interactable = interactable;
        }

        public async UniTask OpenRewardBoxAnimationAsync(BonusPoint bonusPoint, CancellationToken cancellationToken)
        {
            await _bonusPointComponent.OpenRewardBoxAnimationAsync(bonusPoint, cancellationToken);
        }
        
        public async UniTask PlayUnlockDayAnimation(int daysFromStart, float delayTime, CancellationToken cancellationToken)
        {
            await UniTask.Delay(TimeSpan.FromSeconds(delayTime), cancellationToken: cancellationToken);
            var dayTabComponent = _dayTabComponentInfos.Find(x => x.Key == daysFromStart);
            if (dayTabComponent.LockIconObject == null)
                return;
            
            dayTabComponent.LockIconObject.SetActive(true);
            
            var animator = dayTabComponent.OpenAnimator;
            animator.Play("StageRelease");
            await UniTask.WaitUntil(
                () => animator.GetCurrentAnimatorStateInfo(0).normalizedTime >= 0.8, 
                cancellationToken: cancellationToken);
            dayTabComponent.LockIconObject.SetActive(false);
        }
    }
}