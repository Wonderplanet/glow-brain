using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Presentation.Extensions;
using UnityEngine;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components.FilterItem
{
    public class ArtworkEffectFilterItem : FilterItem
    {
        [SerializeField] UIText _titleText;

        public ArtworkEffectType FilterType { get; private set; }

        public void Initialize(ArtworkEffectType filterType)
        {
            FilterType = filterType;

            var displayStr = ArtworkEffectTypeExtensions.ToDisplayString(filterType);
            _titleText.SetText(displayStr);
        }
    }
}
