using System;
using System.Collections.Generic;
using GLOW.Scenes.EncyclopediaReward.Presentation.ViewModels;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.EncyclopediaReward.Presentation.Views
{
    public class EncyclopediaRewardListCategoryComponent : MonoBehaviour
    {
        [SerializeField] EncyclopediaRewardListCell _cellPrefab;
        [SerializeField] RectTransform _listParent;

        public Rect Rect => ((RectTransform)this.transform).rect;
        public Vector2 CellSize => _cellPrefab.RectTransform.rect.size;

        List<EncyclopediaRewardListCell> _cellList = new ();

        public void Setup(IReadOnlyList<EncyclopediaRewardListCellViewModel> cellViewModels, Action<EncyclopediaRewardListCellViewModel> onSelect)
        {
            // セルが不足してる場合は生成
            if (cellViewModels.Count > _cellList.Count)
            {
                for (int i = _cellList.Count; i < cellViewModels.Count; ++i)
                {
                    var instance = Instantiate(_cellPrefab, _listParent);
                    _cellList.Add(instance);
                }
            }

            for (int i = 0; i < cellViewModels.Count; ++i)
            {
                _cellList[i].Hidden = false;
                _cellList[i].Setup(cellViewModels[i], onSelect);
            }

            // 余剰のセルがある場合は非表示にする
            for (int i = cellViewModels.Count; i < _cellList.Count; ++i)
            {
                _cellList[i].Hidden = true;
            }
            LayoutRebuilder.ForceRebuildLayoutImmediate((RectTransform)this.transform);
        }
    }
}
