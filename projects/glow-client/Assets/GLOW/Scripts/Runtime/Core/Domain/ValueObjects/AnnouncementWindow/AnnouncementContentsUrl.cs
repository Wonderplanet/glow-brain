using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.AnnouncementWindow
{
    public record AnnouncementContentsUrl(ObscuredString Value)
    {
        public static AnnouncementContentsUrl Empty { get; } = new(string.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
