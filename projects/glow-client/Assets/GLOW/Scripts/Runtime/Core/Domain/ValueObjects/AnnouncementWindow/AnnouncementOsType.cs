using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects.AnnouncementWindow
{
    public record AnnouncementOsType(ObscuredString Value)
    {
        public static AnnouncementOsType Empty { get; } = new AnnouncementOsType(string.Empty);
        
        static AnnouncementOsType All = new AnnouncementOsType("All");
        static AnnouncementOsType Ios = new AnnouncementOsType("iOS");
        static AnnouncementOsType Android = new AnnouncementOsType("Android");
        
        public bool IsAll()
        {
            return Value.ToString() == All.Value.ToString();
        }
        
        public bool IsIos()
        {
            return Value.ToString() == Ios.Value.ToString();
        }
        
        public bool IsAndroid()
        {
            return Value.ToString() == Android.Value.ToString();
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public override string ToString()
        {
            return Value;
        }
    }
}