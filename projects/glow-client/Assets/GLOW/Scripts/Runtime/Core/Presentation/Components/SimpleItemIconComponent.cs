using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Constants;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Core.Presentation.Components
{
    public class SimpleItemIconComponent : UIObject
    {
        [SerializeField] UIImage _itemImage;
        [SerializeField] IconRarityFrame _rarityFrame;

        public void Setup(ItemIconAssetPath iconAssetPath, Rarity rarity)
        {
            UISpriteUtil.LoadSpriteWithFade(_itemImage.Image, iconAssetPath.Value);

            _rarityFrame.Setup(IconRarityFrameType.Item, rarity);
        }
    }
}
