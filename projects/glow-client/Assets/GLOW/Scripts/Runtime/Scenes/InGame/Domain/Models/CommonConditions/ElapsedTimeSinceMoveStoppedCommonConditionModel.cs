using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Models.CommonConditions
{
    public record ElapsedTimeSinceMoveStoppedCommonConditionModel(TickCount ElapsedTickCount) : ICommonConditionModel
    {
        public InGameCommonConditionType ConditionType => InGameCommonConditionType.ElapsedTimeSinceMoveStopped;

        /// <summary>
        /// 移動停止から指定の時間が経過したら
        /// MoveStopStageTickCountは初期値は召喚時点での値が入り、移動が停止された時点で更新される
        /// </summary>
        public bool MeetsCondition(ICommonConditionContext context)
        {

            var currentElapsedTickCount = context.StageTime.CurrentTickCount - context.MyUnit.MoveStopStageTickCount;
            return currentElapsedTickCount >= ElapsedTickCount;
        }
    }
}
