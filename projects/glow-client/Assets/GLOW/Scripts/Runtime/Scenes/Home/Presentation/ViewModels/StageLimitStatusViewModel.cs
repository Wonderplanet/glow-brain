using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Scenes.Home.Presentation.ViewModels
{
    public record StageLimitStatusViewModel(StageLimitPartyStatus Status,
        IReadOnlyList<SeriesLogoImagePath> SeriesLogImageAssetPathList,
        IReadOnlyList<Rarity> Rarities,
        PartyUnitNum PartyUnitNum,
        IReadOnlyList<CharacterAttackRangeType> CharacterAttackRangeTypes,
        IReadOnlyList<CharacterUnitRoleType> CharacterUnitRoleTypes,
        SummonCost SummonCost);

}
