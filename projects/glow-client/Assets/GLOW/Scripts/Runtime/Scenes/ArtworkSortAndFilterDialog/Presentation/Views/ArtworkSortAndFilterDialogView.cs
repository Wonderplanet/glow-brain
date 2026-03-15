using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Domain.Constants;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Presentation.Components;
using GLOW.Scenes.ArtworkSortAndFilterDialog.Presentation.ViewModels;
using GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components;
using GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components.FilterCell;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.ArtworkSortAndFilterDialog.Presentation.Views
{
    public class ArtworkSortAndFilterDialogView : UIView
    {
        [Header("ソート")]
        [SerializeField] ArtworkSortItem[] _sortItems;
        [SerializeField] UIToggleableComponentGroup _toggleableComponentGroup;

        [Header("フィルター")]
        [SerializeField] SeriesFilterCell _seriesFilterCell;
        [SerializeField] ArtworkEffectFilterCell _artworkEffectFilterCell;

        public IReadOnlyList<MasterDataId> SeriesOnToggleMasterDataIds => _seriesFilterCell.GetOnToggleMasterDataIds();
        public IReadOnlyList<ArtworkEffectType> ArtworkEffectOnToggleTypes => _artworkEffectFilterCell.GetOnToggleTypes();

        public void InitializeSort(ArtworkListSortType currentSortType, Action<ArtworkListSortType> onToggleChange)
        {
            foreach (var item in _sortItems)
            {
                item.SetUp(onToggleChange);
            }

            SetSortToggle(currentSortType);
        }

        public void InitializeFilter(ArtworkSortAndFilterDialogViewModel viewModel)
        {
            _seriesFilterCell.Initialize(viewModel.CategoryModel.FilterSeriesModel, viewModel.SeriesFilterTitleModels);
            _artworkEffectFilterCell.Initialize(viewModel.CategoryModel.FilterArtworkEffectModel);
        }

        public void SetSortToggle(ArtworkListSortType setSortType)
        {
            _toggleableComponentGroup.SetToggleOn(setSortType.ToString());
        }
    }
}
