using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class DeckUnitSummonExecutor : IDeckUnitSummonExecutor
    {
        public DeckUnitSummonResult Summon(DeckUnitModel deckUnit, BattlePointModel battlePointModel)
        {
            // 召喚コストを消費
            var newBattlePoint = battlePointModel.CurrentBattlePoint - deckUnit.SummonCost;
            var updatedBattlePointModel = battlePointModel with { CurrentBattlePoint = newBattlePoint };

            // DeckUnitModel更新
            var updatedDeckUnit = deckUnit with
            {
                RemainingSummonCoolTime = deckUnit.SummonCoolTime,
                IsSummoned = true,
                CurrentSpecialAttackCoolTime = deckUnit.SpecialAttackInitialCoolTime,
                RemainingSpecialAttackCoolTime = deckUnit.SpecialAttackInitialCoolTime,
                IsSpecialAttackReady = false
            };

            return new DeckUnitSummonResult(updatedDeckUnit, updatedBattlePointModel);
        }
    }
}
