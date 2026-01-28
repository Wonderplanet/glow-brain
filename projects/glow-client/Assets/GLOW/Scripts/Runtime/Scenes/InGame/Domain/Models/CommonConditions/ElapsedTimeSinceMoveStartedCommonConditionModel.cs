using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Models.CommonConditions
{
    public record ElapsedTimeSinceMoveStartedCommonConditionModel(TickCount ElapsedTickCount) : ICommonConditionModel
    {
        public InGameCommonConditionType ConditionType => InGameCommonConditionType.ElapsedTimeSinceMoveStarted;

        /// <summary>
        /// 移動開始から指定の時間が経過したら
        /// MoveStartStageTickCountは初期値は召喚時点での値が入り、移動が開始された時点で更新される
        /// </summary>
        public bool MeetsCondition(ICommonConditionContext context)
        {
            if (context.MyUnit.MoveStartStageTickCount.IsEmpty()) return false;

            var currentElapsedTickCount = context.StageTime.CurrentTickCount - context.MyUnit.MoveStartStageTickCount;
            return currentElapsedTickCount >= ElapsedTickCount;
        }
    }
}