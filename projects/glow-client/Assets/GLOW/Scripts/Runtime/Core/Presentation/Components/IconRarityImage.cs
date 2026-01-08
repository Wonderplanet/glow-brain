using GLOW.Core.Domain.Constants;
using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    public class IconRarityImage : UIImage
    {
        [SerializeField] Sprite[] _rarityIconSprites = {};

        public void Setup(Rarity rarity)
        {
            int index = (int)rarity;
            if (index >= _rarityIconSprites.Length) return;

            Sprite = _rarityIconSprites[index];
            Hidden = false;
        }
    }
}
