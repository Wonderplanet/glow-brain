using WPFramework.Presentation.Views;

namespace GLOW.Core.Presentation.CustomCarousel
{
    public interface IGlowCustomCarouselViewDelegate : IInfiniteCarouselViewDelegate
    {
        void AccessoryButtonTappedForRowWith(GlowCustomInfiniteCarouselView carouselView, int indexPath, object identifier);
    }
}
