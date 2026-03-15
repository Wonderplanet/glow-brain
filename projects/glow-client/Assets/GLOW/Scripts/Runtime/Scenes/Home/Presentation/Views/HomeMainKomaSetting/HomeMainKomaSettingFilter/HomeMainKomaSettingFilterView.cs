using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components.FilterCell;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.HomeMainKomaSettingFilter.Presentation
{
    public class HomeMainKomaSettingFilterView : UIView
    {
        [SerializeField] SeriesFilterCell _seriesFilterCell;
        public IReadOnlyList<MasterDataId> SeriesOnToggleMasterDataIds => _seriesFilterCell.GetOnToggleMasterDataIds();

        public void InitializeView(HomeMainKomaSettingFilterViewModel viewModel)
        {
            _seriesFilterCell.Initialize(viewModel.FilterSeriesModel, viewModel.SeriesFilterTitleModels);
        }
    }
}
