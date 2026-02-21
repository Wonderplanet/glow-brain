using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Constants;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Core.Presentation.Components
{
    public class ItemIconComponent : UIObject
    {
        [SerializeField] UIImage _itemImage;
        [SerializeField] IconRarityFrame _rarityFrame;
        [SerializeField] UIText _amountText;
        [SerializeField] int _crossMarkFontSize;
        const string AmountTextFormat = "<size={0}>{1}</size>{2}";

        public void Setup(ItemIconAssetPath iconAssetPath, Rarity rarity, ItemAmount amount)
        {
            UISpriteUtil.LoadSpriteWithFade(_itemImage.Image, iconAssetPath.Value);

            _rarityFrame.Setup(IconRarityFrameType.Item, rarity);
            _amountText.SetText(AmountTextFormat, _crossMarkFontSize,"×", amount.ToStringSeparated());
        }
    }
}
