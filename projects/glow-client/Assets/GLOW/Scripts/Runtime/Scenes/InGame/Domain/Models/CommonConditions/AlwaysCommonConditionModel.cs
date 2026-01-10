using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Models.CommonConditions
{
    public record AlwaysCommonConditionModel() : ICommonConditionModel
    {
        public static AlwaysCommonConditionModel Instance { get; } = new();

        public InGameCommonConditionType ConditionType => InGameCommonConditionType.Always;

        public bool MeetsCondition(ICommonConditionContext context)
        {
            return true;
        }
    }
}
