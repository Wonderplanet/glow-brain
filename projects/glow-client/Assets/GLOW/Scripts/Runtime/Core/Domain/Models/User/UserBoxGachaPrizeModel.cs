using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Domain.Models
{
    public record UserBoxGachaPrizeModel(
        MasterDataId MstBoxGachaPrizeId,
        GachaDrawCount DrawCount)
    {
        public static UserBoxGachaPrizeModel Empty { get; } = new(MasterDataId.Empty, GachaDrawCount.Empty);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}