using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine;

namespace GLOW.Scenes.BattleResult.Presentation.Views
{
    public class ContinueEnemyProgressComponent : MonoBehaviour
    {
        [SerializeField] UIObject _bossCountRoot;
        [SerializeField] UIText _bossCountText;
        [SerializeField] UIObject _remainingTargetEnemyCountRoot;
        [SerializeField] UIText _remainingTargetEnemyCountText;

        public void SetUp(
            DefeatEnemyCount remainingTargetEnemyCount,
            DefeatBossEnemyCount defeatedBossCount,
            BossCount totalBossCount)
        {
            _remainingTargetEnemyCountRoot.Hidden = remainingTargetEnemyCount.IsEmpty();
            _remainingTargetEnemyCountText.SetText(remainingTargetEnemyCount.Value.ToString());

            _bossCountRoot.Hidden = !_remainingTargetEnemyCountRoot.Hidden || totalBossCount.IsZero();
            _bossCountText.SetText(
                "{0}/{1}",
                GetDefeatedBossCount(defeatedBossCount),
                GetTotalBossCountText(totalBossCount));
        }

        string GetDefeatedBossCount(DefeatBossEnemyCount defeatedBossCount)
        {
            if (defeatedBossCount > 999) return "999";

            return defeatedBossCount.Value.ToString();
        }

        string GetTotalBossCountText(BossCount totalBossCount)
        {
            if (totalBossCount.IsInfinity()) return "???";
            if (totalBossCount > 999) return "999";

            return totalBossCount.Value.ToString();
        }
    }
}
