using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Domain.Models.OprData
{
    public record OprGachaDisplayUnitI18nModel(
        MasterDataId OprGachaId,
        MasterDataId PickupMstUnitId,
        GachaDisplayUnitDescription GachaDisplayUnitDescription)
    {
        public static OprGachaDisplayUnitI18nModel Empty { get; } =
            new(
                MasterDataId.Empty,
                MasterDataId.Empty,
                GachaDisplayUnitDescription.Empty);
    };
}
