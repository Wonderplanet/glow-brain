using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Pvp
{
    public record OpponentPvpStatusModel(
        IReadOnlyList<PvpUnitModel> PvpUnits,
        IReadOnlyList<UserOutpostEnhanceModel> UsrOutpostEnhancements,
        IReadOnlyList<PvpEncyclopediaEffectModel> UsrEncyclopediaEffects,
        IReadOnlyList<MasterDataId> MstArtworkIds)
    {
        public static OpponentPvpStatusModel Empty { get; } = new(
            new List<PvpUnitModel>(),
            new List<UserOutpostEnhanceModel>(),
            new List<PvpEncyclopediaEffectModel>(),
            new List<MasterDataId>()
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
