using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.Models;
using GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components.FilterItem;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.UnitSortAndFilterDialog.Presentation.Components.FilterCell
{
    public class ArtworkEffectFilterCell : UIComponent
    {
        [SerializeField] ToggleAllSelectCancelButtonCell _toggleAllSelectCancelButtonCell;
        [SerializeField] GameObject _itemPrefab;
        [SerializeField] GameObject _itemRoot;

        List<ArtworkEffectFilterItem> _filterItems = new();

        public void Initialize(FilterArtworkEffectModel model)
        {
            // アートワーク効果タイプ分フィルタ項目を作成
            var filterTypes = Enum.GetValues(typeof(ArtworkEffectType));
            foreach (ArtworkEffectType filterType in filterTypes)
            {
                var item = Instantiate(_itemPrefab, _itemRoot.transform).GetComponent<ArtworkEffectFilterItem>();
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

        public IReadOnlyList<ArtworkEffectType> GetOnToggleTypes()
        {
            var filterTypes = _filterItems
                .Where(item => item.IsToggleOn)
                .Select(item => item.FilterType)
                .ToList();
            return filterTypes;
        }
    }
}
