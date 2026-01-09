using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Presentation.Component;
using GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.UnitEnhanceRankUpDetailDialog.Presentation.Views
{
    public class UnitEnhanceRankUpDetailDialogView : UIView
    {
        [SerializeField] UnitEnhanceRankUpDetailCell _cell;
        [SerializeField] RectTransform _cellRoot;
        [SerializeField] RectTransform _scrollViewContent;
        [SerializeField] LayoutElement _scrollViewLayout;

        public void Setup(
            UnitEnhanceRankUpDetailDialogViewModel viewModel,
            Action<ResourceType, MasterDataId, PlayerResourceAmount> onItemTapped)
        {
            foreach (var cellModel in viewModel.CellViewModelList)
            {
                var cell = Instantiate(_cell, _cellRoot);
                cell.Setup(cellModel, onItemTapped);
            }

            // デフォルト(最大)サイズよりリスト領域が小さければウィンドウサイズを小さい方にあわせる
            LayoutRebuilder.ForceRebuildLayoutImmediate(_scrollViewContent);
            if(_scrollViewLayout.preferredHeight > _scrollViewContent.rect.size.y)
            {
                _scrollViewLayout.preferredHeight = _scrollViewContent.rect.size.y;
            }
        }
    }
}
