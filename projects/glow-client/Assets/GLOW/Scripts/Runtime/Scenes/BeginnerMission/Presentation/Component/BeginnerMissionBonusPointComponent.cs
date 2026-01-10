using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.Mission.Presentation.Component;
using UnityEngine;
using UnityEngine.EventSystems;

namespace GLOW.Scenes.BeginnerMission.Presentation.Component
{
    public class BeginnerMissionBonusPointComponent : UIBehaviour
    {
        [SerializeField] MissionBonusPointAreaComponent _bonusPointArea;
        [SerializeField] UIText _receivableTotalDiamondAmountText;
        
        public MissionBonusPointAreaComponent BonusPointAreaComponent => _bonusPointArea;
        public void SetReceivableTotalDiamondAmount(BeginnerMissionPromptPhrase promptPhrase)
        {
            _receivableTotalDiamondAmountText.SetText(promptPhrase.Value);
        }
        
        public async UniTask OpenRewardBoxAnimationAsync(BonusPoint bonusPoint, CancellationToken cancellationToken)
        {
            await _bonusPointArea.OpenRewardBoxAnimationAsync(bonusPoint, cancellationToken);
        }
    }
}