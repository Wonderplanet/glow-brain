using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Notice
{
    public record NoticeDestinationPathDetail(ObscuredString Value)
    {
        public static NoticeDestinationPathDetail Empty { get; } = new(string.Empty);

        public MasterDataId ToMasterDataId()
        {
            return new MasterDataId(Value);
        }
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}