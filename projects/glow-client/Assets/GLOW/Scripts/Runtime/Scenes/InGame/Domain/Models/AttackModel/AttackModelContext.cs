using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Battle;

namespace GLOW.Scenes.InGame.Domain.Models.AttackModel
{
    public record AttackModelContext(
        IReadOnlyList<IAttackTargetModel> AttackTargetCandidates,
        IRandomProvider RandomProvider,
        ICoordinateConverter CoordinateConverter,
        IAttackResultModelFactory AttackResultModelFactory,
        TickCount TickCount,
        IReadOnlyList<DeckUnitModel> PlayerDeckUnits,
        IReadOnlyList<DeckUnitModel> PvpOpponentDeckUnits, 
        IReadOnlyDictionary<KomaId, KomaModel> KomaDictionary,
        MstPageModel MstPage,
        IReadOnlyList<PlacedItemModel> PlacedItems,
        HashSet<KomaId> AlreadyPlacedItemKomaIdSet);
}
