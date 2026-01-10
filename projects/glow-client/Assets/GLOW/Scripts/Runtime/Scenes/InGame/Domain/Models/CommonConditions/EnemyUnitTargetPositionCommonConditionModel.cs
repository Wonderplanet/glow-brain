using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.CommonConditions
{
    public record EnemyUnitTargetPositionCommonConditionModel(OutpostCoordV2 StopPosition) : ICommonConditionModel
    {
        public InGameCommonConditionType ConditionType => InGameCommonConditionType.EnemyUnitTargetPosition;

        /// <summary> 指定座標以上進行していたら </summary>
        public bool MeetsCondition(ICommonConditionContext context)
        {
            return context.MyUnit.BattleSide == BattleSide.Enemy &&
                   context.MyUnit.Pos.X >= StopPosition.X;
        }
    }
}
