using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record HomeBannerDestinationPath(ObscuredString Value)
    {
        public static HomeBannerDestinationPath Empty { get; } = new(string.Empty);

        // urlとかHomeBannerDestinationTypeによって入る予定
        public MasterDataId ToMstGachaId() => new MasterDataId(Value);
        public MasterDataId ToMstEventId() => new MasterDataId(Value);
        public string ToWebUrl() => Value;

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    };
}
