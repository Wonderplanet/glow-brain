using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.AdventBattle.Presentation.Component
{
    public class AdventRaidBattleScoreComponent : UIObject
    {
        [Header("協力スコア")]
        [SerializeField] UIText _currentRaidTotalScoreText;
        
        [Header("次の協力スコア報酬までに必要なスコア")]
        [SerializeField] UIText _requiredNextRaidTotalRewardScoreText;
        
        public void Setup(
            AdventBattleRaidTotalScore raidTotalScore,
            AdventBattleRaidTotalScore requiredNextRaidTotalRewardScore)
        {
            _currentRaidTotalScoreText.SetText(raidTotalScore.ToDisplayString());
            _requiredNextRaidTotalRewardScoreText.SetText(requiredNextRaidTotalRewardScore.ToDisplayString());
        }
    }
}