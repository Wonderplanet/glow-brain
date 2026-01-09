using System.Collections.Generic;
using GLOW.Core.Domain.Constants;

namespace GLOW.Core.Domain.ValueObjects.Stage
{
    public record StageLimitStatus(StageLimitPartyStatus Status,
        PartyUnitNum PartyUnitNum,
        IReadOnlyList<Rarity> Rarities,
        IReadOnlyList<SeriesLogoImagePath> SeriesLogImageAssetPathList,
        IReadOnlyList<SeriesAssetKey> SeriesAssetKeys,
        IReadOnlyList<CharacterAttackRangeType> UnitAttackRangeTypes,
        IReadOnlyList<CharacterUnitRoleType> UnitRoleTypes,
        IReadOnlyList<CharacterColor> CharacterColors,
        SummonCost SummonCost)
    {
        public static StageLimitStatus Empty { get; } = new StageLimitStatus(
            StageLimitPartyStatus.PartyUnitNum,
            PartyUnitNum.Empty,
            new List<Rarity>(),
            new List<SeriesLogoImagePath>(),
            new List<SeriesAssetKey>(),
            new List<CharacterAttackRangeType>(),
            new List<CharacterUnitRoleType>(),
            new List<CharacterColor>(),
            SummonCost.Empty
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}
