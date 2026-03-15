using System;
using System.Collections.Generic;
using System.Diagnostics.CodeAnalysis;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.GachaAnim.Presentation.Views.Parts
{
    public class GachaAnimRarityUpVariationComponent : UIObject
    {
        [Serializable]
        [SuppressMessage("ReSharper", "InconsistentNaming")]
        public class GachaRarityUpImages
        {
            public List<UIImage> RarityUpImages;
        }

        [SerializeField] List<GachaRarityUpImages> _components;
        public List<GachaRarityUpImages> RarityUpImageComponents => _components;

        public void DisplayRarityUpImagesByIndex(int index)
        {
            _components[index].RarityUpImages.ForEach(x => x.Hidden = false);
        }
    }
}
