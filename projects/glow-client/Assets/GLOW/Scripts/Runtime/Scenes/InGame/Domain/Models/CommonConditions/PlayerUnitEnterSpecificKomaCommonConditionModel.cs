using System.Linq;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Models.CommonConditions
{
    public record PlayerUnitEnterSpecificKomaCommonConditionModel(KomaNo KomaNo) : ICommonConditionModel
    {
        public InGameCommonConditionType ConditionType => InGameCommonConditionType.PlayerUnitEnterSpecificKoma;

        public bool MeetsCondition(ICommonConditionContext context)
        {
            var koma = context.MstPage.GetKoma(KomaNo);
            if (koma.IsEmpty()) return false;

            return context.Units
                .Where(unit => unit.BattleSide == BattleSide.Player)
                .Any(m => m.LocatedKoma.Id == koma.KomaId);
        }
    }
}
