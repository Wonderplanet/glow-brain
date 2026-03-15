using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Constants;
using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    public class IconRarityFrame : UIImage
    {
        [SerializeField] Sprite[] _itemRarityFrameSprites = {};
        [SerializeField] Sprite[] _characterRarityFrameSprites = {};

        public void Setup(IconRarityFrameType type, Rarity rarity)
        {
            Sprite = GetFrame(type, rarity);
            Hidden = false;
        }

        Sprite GetFrame(IconRarityFrameType type, Rarity rarity)
        {
            var sprites = type switch
            {
                IconRarityFrameType.Item => _itemRarityFrameSprites,
                IconRarityFrameType.Unit => _characterRarityFrameSprites,
                _ => Array.Empty<Sprite>()
            };

            int index = (int)rarity;
            if (index >= sprites.Length) return null;

            return sprites[index];
        }
    }
}
