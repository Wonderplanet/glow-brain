using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.ShopTab.Presentation.View
{
    public class ShopTabView : UIView
    {
        [SerializeField] Transform _contentRoot;
        [Header("タブ")]
        [SerializeField] UIToggleableComponentGroup _tab;
        [Header("タブ/バッジ")]
        [SerializeField] UIImage _shopTabNewBadges;
        [SerializeField] UIImage _packTabNewBadges;
        [SerializeField] UIImage _passTabNewBadges;

        public Transform ContentRoot => _contentRoot;
        public UIToggleableComponentGroup Tab => _tab;
        public UIImage ShopTabNewBadges => _shopTabNewBadges;
        public UIImage PackTabNewBadges => _packTabNewBadges;
        public UIImage PassTabNewBadges => _passTabNewBadges;


    }
}
