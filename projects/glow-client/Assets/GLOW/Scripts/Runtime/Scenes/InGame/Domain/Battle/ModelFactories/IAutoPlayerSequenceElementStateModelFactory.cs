using GLOW.Core.Domain.Models;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IAutoPlayerSequenceElementStateModelFactory
    {
        AutoPlayerSequenceElementStateModel Create(MstAutoPlayerSequenceElementModel elementModel, BattleSide battleSide);
    }
}
