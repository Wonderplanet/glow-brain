using GLOW.Core.Presentation.Components;
using UIKit;

namespace GLOW.Core.Presentation.Extensions
{
    public static class UICollectionViewExtension
    {
        public static IPlayerResourceIconAnimation CreateCommonReceiveAnimation(this UICollectionView collectionView)
        {
            return new PlayerResourceIconCellAnimation(collectionView);
        }
    }
}
