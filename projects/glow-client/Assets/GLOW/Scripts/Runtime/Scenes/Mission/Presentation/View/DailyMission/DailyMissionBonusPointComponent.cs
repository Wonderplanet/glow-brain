using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.Modules;
using GLOW.Scenes.Mission.Presentation.Component;
using UnityEngine;
using UnityEngine.EventSystems;

namespace GLOW.Scenes.Mission.Presentation.View.DailyMission
{
    public class DailyMissionBonusPointComponent : UIBehaviour
    {
        [SerializeField] MissionBonusPointAreaComponent _bonusPointArea;

        public MissionBonusPointAreaComponent BonusPointAreaComponent => _bonusPointArea;
        
        public void SetUpdateTime(RemainingTimeSpan nextUpdateTime)
        {
            _bonusPointArea.BonusPointText.SetText(TimeSpanFormatter.FormatRemaining(nextUpdateTime));
        }

        public async UniTask OpenRewardBoxAnimationAsync(BonusPoint bonusPoint, CancellationToken cancellationToken)
        {
            await _bonusPointArea.OpenRewardBoxAnimationAsync(bonusPoint, cancellationToken);
        }
    }
}
