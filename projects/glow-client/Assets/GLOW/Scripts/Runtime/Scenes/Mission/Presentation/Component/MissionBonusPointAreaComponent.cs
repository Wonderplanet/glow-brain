using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.Mission.Presentation.ViewModel.BonusPointMission;
using UnityEngine;
using UnityEngine.EventSystems;

namespace GLOW.Scenes.Mission.Presentation.Component
{
    public class MissionBonusPointAreaComponent : UIBehaviour
    {
        [SerializeField] MissionBonusPointProgressIconComponent _progressIconComponent;
        [SerializeField] MissionBonusPointRewardBoxComponent[] _rewardBoxComponents;
        [SerializeField] MissionProgressGaugeComponent _progressGaugeComponent;
        [SerializeField] UIText _bonusPointText;
        
        public UIText BonusPointText => _bonusPointText;
        
        Dictionary<BonusPoint, MissionBonusPointRewardBoxComponent> RewardBoxComponents { get; } = new();

        public void Setup(IBonusPointMissionViewModel viewModel, Action<IReadOnlyList<PlayerResourceIconViewModel>, RectTransform> rewardListWindowAction)
        {
            SetBonusPointNumber(viewModel.BonusPoint);

            RewardBoxComponents.Clear();
            for (var i = 0; i < _rewardBoxComponents.Length; i++)
            {
                if (i >= viewModel.BonusPointMissionCellViewModels.Count)
                    continue;

                _rewardBoxComponents[i].Setup(viewModel.BonusPointMissionCellViewModels[i], rewardListWindowAction);

                RewardBoxComponents.Add(viewModel.BonusPointMissionCellViewModels[i].CriterionCount.ToBonusPoint(), _rewardBoxComponents[i]);
            }
            
            var maxBonusPoint = RewardBoxComponents.Keys.Max();
            SetProgressGaugeRate(viewModel.BonusPoint.ToGaugeRate(maxBonusPoint));
        }

        public async UniTask OpenRewardBoxAnimationAsync(BonusPoint bonusPoint, CancellationToken cancellationToken)
        {
            if (RewardBoxComponents.TryGetValue(bonusPoint, out var rewardBoxComponent))
            {
                await rewardBoxComponent.OpenRewardBoxAnimationAsync(cancellationToken);
            }
        }

        public void SetProgressGaugeRate(float rate)
        {
            _progressGaugeComponent.SetProgressGaugeRate(rate);
        }
        
        public async UniTask PlayProgressGaugeAnimation(
            CancellationToken cancellationToken,
            BonusPoint updatedBonusPoint, 
            BonusPoint maxBonusPoint)
        {
            await _progressGaugeComponent.ProgressGaugeAnimation(
                cancellationToken, 
                updatedBonusPoint,
                maxBonusPoint);
        }

        public void SetBonusPointNumber(BonusPoint bonusPoint)
        {
            _progressIconComponent.Setup(bonusPoint);
        }
    }
}
