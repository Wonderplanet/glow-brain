using System.Collections.Generic;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.ModelFactories
{
    public interface IArtworkEffectModelFactory
    {
        public ArtworkEffectModel Create();
        public ArtworkEffectModel CreatePvpOpponent(IReadOnlyList<ArtworkPartyStatusModel> pvpOpponentArtworkPartyStatuses);
    }
}
