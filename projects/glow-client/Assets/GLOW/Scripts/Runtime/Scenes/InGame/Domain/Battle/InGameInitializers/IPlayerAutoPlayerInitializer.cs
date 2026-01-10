using System.Collections.Generic;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public interface IPlayerAutoPlayerInitializer
    {
        void Initialize(IReadOnlyList<DeckUnitModel> deckUnits);
    }
}