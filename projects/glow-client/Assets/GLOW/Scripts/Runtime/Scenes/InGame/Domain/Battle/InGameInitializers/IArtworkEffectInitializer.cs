using GLOW.Core.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public interface IArtworkEffectInitializer
    {
        ArtworkEffectInitializerResult Initialize(InGameType inGameType);
    }
}
