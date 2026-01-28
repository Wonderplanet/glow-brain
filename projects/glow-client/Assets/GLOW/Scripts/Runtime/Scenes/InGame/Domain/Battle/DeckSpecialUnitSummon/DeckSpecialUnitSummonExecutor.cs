using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class DeckSpecialUnitSummonExecutor : IDeckSpecialUnitSummonExecutor
    {
        public DeckSpecialUnitSummonResult Summon(DeckUnitModel deckUnit, BattlePointModel battlePointModel)
        {
            // 召喚コストを消費
            var newBattlePoint = battlePointModel.CurrentBattlePoint - deckUnit.SummonCost;
            var updatedBattlePointModel = battlePointModel with { CurrentBattlePoint = newBattlePoint };

            // DeckUnitModel更新
            var updatedDeckUnit = deckUnit with
            {
                IsSummoned = true,
                CurrentSpecialAttackCoolTime = deckUnit.SpecialAttackCoolTime,
                IsSpecialAttackReady = false
            };

            return new DeckSpecialUnitSummonResult(updatedDeckUnit, updatedBattlePointModel);
        } 
    }
}