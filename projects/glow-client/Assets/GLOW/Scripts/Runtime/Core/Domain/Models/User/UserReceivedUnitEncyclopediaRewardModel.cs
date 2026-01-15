using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record UserReceivedUnitEncyclopediaRewardModel(MasterDataId MstUnitEncyclopediaRewardId)
    {
        public static UserReceivedUnitEncyclopediaRewardModel Empty { get; } = new (MasterDataId.Empty);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
