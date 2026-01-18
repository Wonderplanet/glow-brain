using GLOW.Scenes.InGame.Domain.Constants;

namespace GLOW.Scenes.InGame.Domain.Models.CommonConditions
{
    public interface ICommonConditionModel
    {
        InGameCommonConditionType ConditionType { get; }

        bool MeetsCondition(ICommonConditionContext context);
    }
}
