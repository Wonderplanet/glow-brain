using GLOW.Core.Domain.AnnouncementWindow;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.AnnouncementWindow
{
    public record AnnouncementBannerUrl(ObscuredString Value)
    {
        public static AnnouncementBannerUrl Empty { get; } = new(string.Empty);
        
        public bool IsEmpty()
        {
            return string.IsNullOrEmpty(Value);
        }

        public AnnouncementBannerSizeType GetBannerSizeType()
        {
            return Value.ToString().Contains("_L.png") ? AnnouncementBannerSizeType.SizeL : AnnouncementBannerSizeType.SizeM;
        }
    }
}