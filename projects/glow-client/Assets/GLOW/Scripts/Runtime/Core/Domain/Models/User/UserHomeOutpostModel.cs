using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record UserHomeOutpostModel(MasterDataId MstOutpostId, MasterDataId MstArtworkId, UserOutpostUsingFlag IsUsed)
    {
        public static UserHomeOutpostModel Empty { get; } = new (
            MasterDataId.Empty, 
            MasterDataId.Empty, 
            UserOutpostUsingFlag.False);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
