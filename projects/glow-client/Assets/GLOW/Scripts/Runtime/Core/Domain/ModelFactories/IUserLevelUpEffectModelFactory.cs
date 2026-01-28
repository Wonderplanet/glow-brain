using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.ModelFactories
{
    public interface IUserLevelUpEffectModelFactory
    {
        UserLevelUpEffectModel Create(
            UserLevelUpResultModel model,
            UserLevel currentLevel,
            UserLevel afterLevel);
    }
}
