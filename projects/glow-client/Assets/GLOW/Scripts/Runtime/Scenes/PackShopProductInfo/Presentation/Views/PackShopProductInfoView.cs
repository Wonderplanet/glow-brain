using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.PackShopProductInfo.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.PackShopProductInfo.Presentation.Views
{
    public class PackShopProductInfoView : UIView
    {
        [SerializeField] PackShopProductInfoContentCell _contentCellPrefab;
        [SerializeField] GameObject _contentCellContainer;
        [SerializeField] GameObject _bonusCellContainer;
        [SerializeField] GameObject _bonusHeader;
        [SerializeField] RectTransform _scrollViewContent;
        [SerializeField] LayoutElement _scrollViewLayout;

        public void Setup(PackShopProductInfoViewModel viewModel, Action<MasterDataId> ticketDetailAction)
        {
            foreach (var content in viewModel.Contents)
            {
                var cell = Instantiate(_contentCellPrefab, _contentCellContainer.transform);
                cell.Setup(content, ticketDetailAction);
            }

            _bonusHeader.SetActive(!viewModel.Bonuses.IsEmpty());
            foreach (var bonus in viewModel.Bonuses)
            {
                var cell = Instantiate(_contentCellPrefab, _bonusCellContainer.transform);
                cell.Setup(bonus, ticketDetailAction);
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
