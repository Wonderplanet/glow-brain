using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Scenes.UnitList.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.Models;
using GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components.FilterItem;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components.FilterCell
{
    public class SpecialFilterCell : UIComponent
    {
        [SerializeField] ToggleAllSelectCancelButtonCell _toggleAllSelectCancelButtonCell;
        [SerializeField] GameObject _itemPrefab;
        [SerializeField] GameObject _itemRoot;

        List<SpecialFilterItem> _filterItems = new();

        public void Initialize(FilterSpecialAttackModel model)
        {
            // All以外の必殺技効果タイプ分フィルタ項目を作成
            var filterTypes = Enum.GetValues(typeof(FilterSpecialAttack));
            foreach (FilterSpecialAttack filterType in filterTypes)
            {
                if (filterType == FilterSpecialAttack.All) continue; // Allは除外

                var item = Instantiate(_itemPrefab, _itemRoot.transform).GetComponent<SpecialFilterItem>();
                item.Initialize(filterType);
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

        public IReadOnlyList<FilterSpecialAttack> GetOnToggleTypes()
        {
            List<FilterSpecialAttack> filterTypes = new();
            filterTypes = _filterItems
                .Where(item => item.IsToggleOn)
                .Select(item => item.FilterType)
                .ToList();
            return filterTypes;
        }
    }
}
