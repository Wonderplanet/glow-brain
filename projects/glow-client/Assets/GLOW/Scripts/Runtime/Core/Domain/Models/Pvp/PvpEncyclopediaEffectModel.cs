using GLOW.Core.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.Models.Pvp
{
    public record PvpEncyclopediaEffectModel(
        MasterDataId MstEncyclopediaEffectId
    )
    {
        public static PvpEncyclopediaEffectModel Empty { get; } = new PvpEncyclopediaEffectModel(MasterDataId.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
