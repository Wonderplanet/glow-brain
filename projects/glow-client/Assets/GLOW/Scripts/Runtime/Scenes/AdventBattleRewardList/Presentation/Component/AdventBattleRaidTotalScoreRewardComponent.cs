using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.AdventBattleRewardList.Presentation.Component
{
    public class AdventBattleRaidTotalScoreRewardComponent : UIObject
    {
        [SerializeField] UIText _lowerReauiredTotalScoreText;

        public void SetScore(AdventBattleScore score)
        {
            _lowerReauiredTotalScoreText.Hidden = false;
            _lowerReauiredTotalScoreText.SetText(score.ToDisplayString());
        }

    }
}