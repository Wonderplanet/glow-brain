using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;

namespace GLOW.Core.Domain.Models
{
    public record UserOutpostEnhanceModel(
        MasterDataId MstOutpostId,
        MasterDataId MstOutpostEnhanceId, 
        OutpostEnhanceLevel Level)
    {
        public static UserOutpostEnhanceModel Empty { get; } = new (
            MasterDataId.Empty,
            MasterDataId.Empty,
            OutpostEnhanceLevel.Empty);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public bool IsEnhanced()
        {
            return Level > OutpostEnhanceLevel.One;
        }
    }
}
