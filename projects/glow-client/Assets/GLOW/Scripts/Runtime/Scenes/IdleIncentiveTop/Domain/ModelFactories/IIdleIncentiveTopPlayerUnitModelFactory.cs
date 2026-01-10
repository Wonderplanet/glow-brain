using GLOW.Core.Domain.Models;
using GLOW.Scenes.IdleIncentiveTop.Domain.Models;

namespace GLOW.Scenes.IdleIncentiveTop.Domain.ModelFactories
{
    public interface IIdleIncentiveTopPlayerUnitModelFactory
    {
        IdleIncentiveTopPlayerUnitModel Create(MstCharacterModel mstCharacterModel);
    }
}