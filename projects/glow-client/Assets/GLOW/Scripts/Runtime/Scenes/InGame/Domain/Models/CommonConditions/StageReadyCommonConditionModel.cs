using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Models.CommonConditions
{
    /// <summary>
    /// ステージ開始前になにかするための条件
    /// </summary>
    public record StageReadyCommonConditionModel : ICommonConditionModel
    {
        public static StageReadyCommonConditionModel Instance { get; } = new();

        public InGameCommonConditionType ConditionType => InGameCommonConditionType.StageReady;

        public bool MeetsCondition(ICommonConditionContext context)
        {
            // この条件の性質的に条件を満たすかのチェックはしないので常にfalse
            return false;
        }
    }
}
