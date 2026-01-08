using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.ModelFactories
{
    public interface IAutoPlayerSequenceModelFactory
    {
        AutoPlayerSequenceModel Create(AutoPlayerSequenceSetId mstAutoPlayerSequenceSetId);
    }
}