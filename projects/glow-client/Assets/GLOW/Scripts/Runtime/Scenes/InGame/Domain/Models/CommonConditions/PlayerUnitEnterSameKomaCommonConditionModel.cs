using System.Linq;
using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Models.CommonConditions
{
    public record PlayerUnitEnterSameKomaCommonConditionModel() : ICommonConditionModel
    {
        public static PlayerUnitEnterSameKomaCommonConditionModel Instance { get; } = new();

        public InGameCommonConditionType ConditionType => InGameCommonConditionType.PlayerUnitEnterSameKoma;

        public bool MeetsCondition(ICommonConditionContext context)
        {
            var myUnit = context.MyUnit;

            return context.Units.Any(unit =>
                unit.BattleSide == BattleSide.Player &&
                unit.Id != myUnit.Id &&
                unit.LocatedKoma.Id == myUnit.LocatedKoma.Id);
        }
    }
}
