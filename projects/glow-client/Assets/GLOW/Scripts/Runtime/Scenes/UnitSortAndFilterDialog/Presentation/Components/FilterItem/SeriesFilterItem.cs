using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components.FilterItem
{
    public class SeriesFilterItem : FilterItem
    {
        [SerializeField] UIImage _logoImage;

        public MasterDataId MasterDataId { get; private set; }

        public void Initialize(MasterDataId masterDataId, SeriesLogoImagePath seriesLogoImagePath)
        {
            MasterDataId = masterDataId;
            UISpriteUtil.LoadSpriteWithFade(_logoImage.Image, seriesLogoImagePath.Value);
        }
    }
}
