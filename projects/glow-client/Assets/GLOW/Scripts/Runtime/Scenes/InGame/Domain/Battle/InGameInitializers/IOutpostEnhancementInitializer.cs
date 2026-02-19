using GLOW.Core.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public interface IOutpostEnhancementInitializer
    {
        OutpostEnhancementInitializerResult Initialize(InGameType inGameType);
    }
}

