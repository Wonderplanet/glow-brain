using WPFramework.Presentation.Components;
using WPFramework.Presentation.Views;

namespace WPFramework.Presentation.Extensions
{
    public static class CarouselViewExtension
    {
        public static ICollectionCellAnimation CreateDefaultAnimation(this InfiniteCarouselView collectionView)
        {
            return new InfiniteCarouselCellAnimation(collectionView);
        }
    }
}
