using UIKit;
using WPFramework.Presentation.Components;

namespace WPFramework.Presentation.Extensions
{
    public static class UICollectionViewExtension
    {
        public static ICollectionCellAnimation CreateAnimatorAnimation(this UICollectionView collectionView)
        {
            return new AnimatorCellAnimation(collectionView);
        }

        public static ICollectionCellAnimation CreateTweenAnimation(this UICollectionView collectionView)
        {
            return new TweenCellAnimation(collectionView);
        }
    }
}
