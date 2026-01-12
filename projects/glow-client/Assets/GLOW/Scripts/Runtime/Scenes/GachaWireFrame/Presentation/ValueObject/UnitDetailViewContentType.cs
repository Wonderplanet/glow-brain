using GLOW.Scenes.Home.Domain.Constants;

namespace GLOW.Scenes.GachaWireFrame.Presentation.ValueObject
{
    public record UnitDetailViewContentType(HomeContentTypes ContentType)
    {
        public static UnitDetailViewContentType Empty { get; } =
            new UnitDetailViewContentType(HomeContentTypes.Main);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
        
        public bool IsSameContentType(HomeContentTypes contentType)
        {
            if (IsEmpty()) return false;
            
            return ContentType == contentType;
        }
    }
}