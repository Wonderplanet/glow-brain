using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.Models;
using GLOW.Scenes.UnitSortAndFilterDialog.Domain.Models;
using GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components.FilterItem;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components.FilterCell
{
    /// <summary> 特性フィルタだけはアビリティ項目をもとに自動生成を行う </summary>
    public class PropertyFilterCell : UIComponent
    {
        [SerializeField] ToggleAllSelectCancelButtonCell _toggleAllSelectCancelButtonCell;
        [SerializeField] GameObject _itemPrefab;
        [SerializeField] GameObject _itemRoot;

        List<PropertyFilterItem> _filterItems = new();

        public void Initialize(FilterAbilityModel model, IReadOnlyList<UnitAbilityFilterTitleModel> titleModels)
        {
            // アビリティ分フィルタ項目を作成
            foreach (var titleModel in titleModels)
            {
                var item = Instantiate(_itemPrefab, _itemRoot.transform).GetComponent<PropertyFilterItem>();
                item.Initialize(titleModel.UnitAbilityType, titleModel.AbilityFilterTitle.Value);
                _filterItems.Add(item);
            }

            foreach (var item in _filterItems)
            {
                item.IsToggleOn = model.IsOn(item.FilterType);
            }

            // 生成した項目のトグル一覧を全解除・全選択処理のコンポーネントに登録
            var toggleableComponents = _filterItems.Select(item => item.UIToggleableComponent).ToList();
            _toggleAllSelectCancelButtonCell.SetToggleComponent(toggleableComponents);
        }

        public IReadOnlyList<UnitAbilityType> GetOnToggleTypes()
        {
            var filterTypes = _filterItems
                .Where(item => item.IsToggleOn)
                .Select(item => item.FilterType)
                .ToList();
            return filterTypes;
        }
    }
}
