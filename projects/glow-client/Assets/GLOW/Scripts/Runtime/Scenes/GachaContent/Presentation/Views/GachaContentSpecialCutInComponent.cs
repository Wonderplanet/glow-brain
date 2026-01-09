using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.GachaContent.Presentation.Views
{
    public class GachaContentSpecialCutInComponent : UIObject
    {
        [Serializable]
        class SpecialCutIn
        {
            public Rarity rarity;
            public Sprite sprite;
        }

        [SerializeField] UIImage _specialBand;
        [SerializeField] List<SpecialCutIn> _specialCutInList;

        public Rarity Rarity
        {
            set => _specialBand.Sprite = GetSprite(value);
        }

        Sprite GetSprite(Rarity rarity)
        {
            return _specialCutInList.First(sprite => sprite.rarity == rarity).sprite;
        }
    }
}
