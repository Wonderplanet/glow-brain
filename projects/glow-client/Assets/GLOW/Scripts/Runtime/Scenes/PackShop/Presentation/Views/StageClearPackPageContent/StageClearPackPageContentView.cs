using UIKit;
using UnityEngine;

namespace GLOW.Scenes.PackShop.Presentation.Views.StageClearPackPageContent
{
    public class StageClearPackPageContentView : UIView
    {
        [SerializeField] PackShopProductListCell _cell;

        public PackShopProductListCell Cell => _cell;
    }
}
