using GLOW.Core.Domain.Extensions;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class DeckUnitSummonEvaluator : IDeckUnitSummonEvaluator
    {
        public bool CanSummon(DeckUnitModel deckUnit, BattlePointModel battlePointModel)
        {
            if (deckUnit.IsEmptyUnit()) return false;
            if (!deckUnit.RoleType.IsSummonableOnField()) return false;
            if (deckUnit.IsSummoned) return false;
            if (!deckUnit.RemainingSummonCoolTime.IsZero()) return false;

            return battlePointModel.CurrentBattlePoint >= deckUnit.SummonCost;
        }
    }
}
