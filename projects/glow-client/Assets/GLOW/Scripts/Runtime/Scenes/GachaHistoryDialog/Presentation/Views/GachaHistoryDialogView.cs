using System;
using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.GachaHistoryDetailDialog.Presentation.ViewModels;
using GLOW.Scenes.GachaHistoryDialog.Presentation.ViewModels;
using ModestTree;
using UIKit;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Scenes.GachaHistoryDialog.Presentation.Views
{
    public class GachaHistoryDialogView : UIView
    {
        [SerializeField] List<GachaHistoryCell> _cells;
        [SerializeField] GameObject _emptyTextObject;
        [SerializeField] UIText _currentPageText;
        [SerializeField] Button _firstPageButton;
        [SerializeField] Button _previousPageButton;
        [SerializeField] Button _nextPageButton;
        [SerializeField] Button _lastPageButton;
        [SerializeField] UIObject _firstPageButtonGrayOutObject;
        [SerializeField] UIObject _previousPageButtonGrayOutObject;
        [SerializeField] UIObject _nextPageButtonGrayOutObject;
        [SerializeField] UIObject _lastPageButtonGrayOutObject;
        [SerializeField] ScrollRect _scrollRect;
        
        public float ScrollPos => _scrollRect.verticalNormalizedPosition;

        public void SetupGachaHistoryCells(
            IReadOnlyList<GachaHistoryCellViewModel> cellViewModels,
            IReadOnlyList<GachaHistoryDetailDialogViewModel> detailViewModels,
            Action<GachaHistoryCellViewModel, GachaHistoryDetailDialogViewModel> onTapped)
        {
            _emptyTextObject.SetActive(cellViewModels.IsEmpty());
            
            for (var i = 0; i < _cells.Count; i++)
            {
                var cell = _cells[i];
                if(cellViewModels.Count <= i)
                {
                    cell.Hidden = true;
                    continue;
                }
                
                cell.Hidden = false;
                var cellViewModel = cellViewModels[i];
                var detailViewModel = detailViewModels[i];
                cell.Setup(cellViewModel, () => onTapped(cellViewModel, detailViewModel));
            }
        }
        
        public void MoveScrollToTop()
        {
            LayoutRebuilder.ForceRebuildLayoutImmediate(_scrollRect.content);
            _scrollRect.verticalNormalizedPosition = 1.0f;
        }
        
        public void MoveScrollToTargetPos(float targetPos)
        {
            LayoutRebuilder.ForceRebuildLayoutImmediate(_scrollRect.content);
            _scrollRect.verticalNormalizedPosition = targetPos;
        }
        
        public void SetupButton(GachaHistoryDialogViewModel viewModel, int currentPage)
        {
            _currentPageText.SetText("{0}/{1}", currentPage, viewModel.GetLastPageNum());
            
            _firstPageButtonGrayOutObject.Hidden = viewModel.CanGoToFirstPage(currentPage);
            _previousPageButtonGrayOutObject.Hidden = viewModel.CanGoToPreviousPage(currentPage);
            _nextPageButtonGrayOutObject.Hidden = viewModel.CanGoToNextPage(currentPage);
            _lastPageButtonGrayOutObject.Hidden = viewModel.CanGoToLastPage(currentPage);
        }
    }
}