using GLOW.Core.Domain.Constants;
using UnityEngine;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components.FilterItem
{
    public class ColorFilterItem : FilterItem
    {
        [SerializeField] CharacterColor _color;

        public CharacterColor FilterType => _color;
    }
}
