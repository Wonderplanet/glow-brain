using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.Mission.Presentation.Component;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.Mission.Presentation.View.WeeklyMission
{
    public class WeeklyMissionView : UIView
    {
        [SerializeField] WeeklyMissionBonusPointComponent _bonusPointComponent;

        [SerializeField] UICollectionView _collectionView;

        public UICollectionView CollectionView => _collectionView;

        public MissionBonusPointAreaComponent BonusPointComponent => _bonusPointComponent.BonusPointAreaComponent;

        public void SetUpdateTime(RemainingTimeSpan nextUpdateTime)
        {
            _bonusPointComponent.SetUpdateTime(nextUpdateTime);
        }

        public async UniTask OpenRewardBoxAnimationAsync(BonusPoint bonusPoint, CancellationToken cancellationToken)
        {
            await _bonusPointComponent.OpenRewardBoxAnimationAsync(bonusPoint, cancellationToken);
        }
    }
}
