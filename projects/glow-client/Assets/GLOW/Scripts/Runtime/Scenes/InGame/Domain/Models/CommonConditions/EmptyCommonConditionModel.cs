using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Models.CommonConditions
{
    public record EmptyCommonConditionModel() : ICommonConditionModel
    {
        public static EmptyCommonConditionModel Instance { get; } = new();

        public InGameCommonConditionType ConditionType => InGameCommonConditionType.None;

        public bool MeetsCondition(ICommonConditionContext context)
        {
            return false;
        }
    }
}
