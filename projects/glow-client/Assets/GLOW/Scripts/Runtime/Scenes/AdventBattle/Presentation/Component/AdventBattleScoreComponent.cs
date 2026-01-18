using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.AdventBattle.Presentation.Component
{
    public class AdventBattleScoreComponent : UIObject
    {
        [Header("累計ダメージスコア")]
        [SerializeField] UIText _totalDamageScoreText;
        [SerializeField] UIText _nextRankRequiredScoreText;

        [Header("ハイスコア")]
        [SerializeField] UIText _highScoreText;

        [Header("ランクアイコン")]
        [SerializeField] RankingRankIcon _rankingRankIcon;

        public void Setup(
            AdventBattleScore totalScore,
            AdventBattleScore requiredScore,
            AdventBattleScore highScore,
            RankType rankType,
            AdventBattleScoreRankLevel rankLevel)
        {
            _totalDamageScoreText.SetText(totalScore.ToDisplayString());
            _nextRankRequiredScoreText.SetText(requiredScore.ToDisplayString());
            _highScoreText.SetText(highScore.ToDisplayString());

            _rankingRankIcon.SetupRankType(rankType);
            _rankingRankIcon.PlayRankTierAnimation(rankLevel.ToScoreRankLevel());
        }
    }
}
