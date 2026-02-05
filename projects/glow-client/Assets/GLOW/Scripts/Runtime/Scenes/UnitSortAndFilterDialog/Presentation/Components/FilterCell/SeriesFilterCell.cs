using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitList.Domain.Models;
using GLOW.Scenes.UnitSortAndFilterDialog.Domain.Models;
using GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components.FilterItem;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components.FilterCell
{
    public class SeriesFilterCell : UIComponent
    {
        [SerializeField] ToggleAllSelectCancelButtonCell _toggleAllSelectCancelButtonCell;
        [SerializeField] GameObject _itemPrefab;
        [SerializeField] GameObject _itemRoot;

        List<SeriesFilterItem> _filterItems = new();

        public void Initialize(FilterSeriesModel model, IReadOnlyList<SeriesFilterTitleModel> seriesFilterTitleModels)
        {
            // 作品分フィルタ項目を作成
            foreach (var titleModel in seriesFilterTitleModels)
            {
                var item = Instantiate(_itemPrefab, _itemRoot.transform).GetComponent<SeriesFilterItem>();
                item.Initialize(titleModel.Id, titleModel.SeriesLogoImagePath);
                _filterItems.Add(item);
            }

            foreach (var item in _filterItems)
            {
                item.IsToggleOn = model.IsOn(item.MasterDataId);
            }

            // 生成した項目のトグル一覧を全解除・全選択処理のコンポーネントに登録
            var toggleableComponents = _filterItems.Select(item => item.UIToggleableComponent).ToList();
            _toggleAllSelectCancelButtonCell.SetToggleComponent(toggleableComponents);
        }

        public IReadOnlyList<MasterDataId> GetOnToggleMasterDataIds()
        {
            var masterDataIds = _filterItems
                .Where(item => item.IsToggleOn)
                .Select(item => item.MasterDataId)
                .ToList();
            return masterDataIds;
        }
    }
}
