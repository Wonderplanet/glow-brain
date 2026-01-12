using GLOW.Core.Domain.Constants;
using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    public class UnitRarityFrame : UIImage
    {
        [SerializeField] Sprite[] _unitRarityFrameSprites = {};

        public void Setup(Rarity rarity)
        {
            Sprite = GetFrame(rarity);
            Hidden = false;
        }

        Sprite GetFrame(Rarity rarity)
        {
            int index = (int)rarity;
            if (index >= _unitRarityFrameSprites.Length) return null;

            return _unitRarityFrameSprites[index];
        }
    }
}
