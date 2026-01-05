using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Scenes.PvpTop.Domain.ValueObject;

namespace GLOW.Scenes.PvpTop.Domain.ModelFactories
{
    public interface IPvpUserRankStatusFactory
    {
        PvpUserRankStatus Create(PvpPoint score);
    }
}
