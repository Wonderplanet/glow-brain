using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.AnnouncementWindow
{
    public record AnnouncementTitle(ObscuredString Value)
    {
        public static AnnouncementTitle Empty { get; } = new("");
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}