using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.Models.Gacha
{
    public record GachaPrizeModel(
        ResourceType ResourceType, 
        MasterDataId MasterDataId, 
        ObscuredInt ResourceAmount, 
        ObscuredFloat Probability, 
        bool Pickup)
    {
        public static GachaPrizeModel Empty { get; } = new(
            ResourceType.Unit, 
            MasterDataId.Empty, 
            0, 
            0, 
            false);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}
