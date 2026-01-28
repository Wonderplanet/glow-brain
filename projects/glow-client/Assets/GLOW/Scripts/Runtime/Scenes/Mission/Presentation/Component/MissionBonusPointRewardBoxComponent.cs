using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.Mission.Presentation.ViewModel.BonusPointMission;
using GLOW.Scenes.Mission.Presentation.ViewModel.DailyBonus;
using UnityEngine;
using UnityEngine.EventSystems;
using UnityEngine.UI;

namespace GLOW.Scenes.Mission.Presentation.Component
{
    public class MissionBonusPointRewardBoxComponent : UIBehaviour
    {
        [SerializeField] MissionBonusPointRewardBoxAnimationController _boxAnimationController;
        [SerializeField] UIText _criterionBonusPointText;
        [SerializeField] Button _button;

        Button.ButtonClickedEvent OnRewardBoxTapped => _button.onClick;

        Action<IReadOnlyList<PlayerResourceIconViewModel>, RectTransform> _rewardListWindowAction;

        public void Setup(IBonusPointMissionCellViewModel viewModel, Action<IReadOnlyList<PlayerResourceIconViewModel>, RectTransform> rewardListWindowAction)
        {
            _criterionBonusPointText.SetText(viewModel.CriterionCount.ToStringSeparated());
            _rewardListWindowAction = rewardListWindowAction;
            
            PlayRewardBoxAnimation(viewModel.MissionStatus);

            // 更新する関係で何度も呼ばれるので都度空にする
            OnRewardBoxTapped.RemoveAllListeners();
            OnRewardBoxTapped.AddListener(() =>
            {
                _rewardListWindowAction?.Invoke(viewModel.PlayerResourceIconViewModels, transform as RectTransform);
            });
        }

        public void Setup(IDailyBonusTotalTypeMissionCellViewModel viewModel, Action<IReadOnlyList<PlayerResourceIconViewModel>, RectTransform> rewardListWindowAction)
        {
            _criterionBonusPointText.SetText(viewModel.LoginDayCount.ToStringSeparated());
            _rewardListWindowAction = rewardListWindowAction;
            
            PlayRewardBoxAnimation(viewModel.MissionStatus);

            // 更新する関係で何度も呼ばれるので都度空にする
            OnRewardBoxTapped.RemoveAllListeners();
            OnRewardBoxTapped.AddListener(() =>
            {
                _rewardListWindowAction?.Invoke(viewModel.PlayerResourceIconViewModels, transform as RectTransform);
            });
        }

        public async UniTask OpenRewardBoxAnimationAsync(CancellationToken cancellationToken)
        {
            await PlayOpenAnimationAsync(cancellationToken);
        }

        void PlayRewardBoxAnimation(MissionStatus missionStatus)
        {
            switch (missionStatus)
            {
                case MissionStatus.Nothing:
                case MissionStatus.Receivable:
                    _boxAnimationController.PlayNormalAnimation();
                    break;
                case MissionStatus.Received:
                    _boxAnimationController.PlayOpenedAnimation();
                    break;
                
            }
        }

        async UniTask PlayOpenAnimationAsync(CancellationToken cancellationToken)
        {
            await _boxAnimationController.PlayOpenAnimationAsync(cancellationToken);
        }
        
    }
}
