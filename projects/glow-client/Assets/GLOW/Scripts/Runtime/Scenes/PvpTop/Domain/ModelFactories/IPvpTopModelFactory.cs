using GLOW.Core.Domain.Models.Pvp;
using GLOW.Scenes.PvpTop.Domain.ValueObject;

namespace GLOW.Scenes.PvpTop.Domain.ModelFactories
{
    public interface IPvpTopModelFactory
    {
        PvpTopUseCaseModel Create(PvpTopResultModel pvpTopResultModel);
    }
}
