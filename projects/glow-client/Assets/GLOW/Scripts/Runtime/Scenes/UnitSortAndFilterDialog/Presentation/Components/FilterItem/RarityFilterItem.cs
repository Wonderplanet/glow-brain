using GLOW.Core.Domain.Constants;
using UnityEngine;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components.FilterItem
{
    public class RarityFilterItem : FilterItem
    {
        [SerializeField] Rarity _filterRarity;

        public Rarity FilterType => _filterRarity;
    }
}
