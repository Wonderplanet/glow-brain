using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.BoxGachaResult.Presentation.Component
{
    public class BoxGachaConvertTextComponent : UIObject
    {
        [SerializeField] UIText _convertedUnitText;
        [SerializeField] UIText _convertedEmblemText;
        [SerializeField] UIText _convertedArtworkText;

        public void SetUpConvertText(IReadOnlyList<ResourceType> convertedResourceTypes)
        {
            _convertedUnitText.IsVisible = convertedResourceTypes.Contains(ResourceType.Unit);
            _convertedEmblemText.IsVisible = convertedResourceTypes.Contains(ResourceType.Emblem);
            _convertedArtworkText.IsVisible = convertedResourceTypes.Contains(ResourceType.ArtworkFragment);
        }
    }
}