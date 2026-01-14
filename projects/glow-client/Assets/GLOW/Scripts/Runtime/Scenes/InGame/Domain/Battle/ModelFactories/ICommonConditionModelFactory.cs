using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models.CommonConditions;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface ICommonConditionModelFactory
    {
        ICommonConditionModel Create(
            InGameCommonConditionType type,
            CommonConditionValue value);
    }
}
