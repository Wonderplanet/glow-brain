using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.Notice
{
    public record NoticeTitle(ObscuredString Value)
    {
        public static NoticeTitle Empty { get; } = new (string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}