using System;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.GachaHistoryDetailDialog.Presentation.ViewModels;
using GLOW.Scenes.GachaHistoryDialog.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.GachaHistoryDialog.Presentation.Views
{
    public class GachaHistoryDialogViewController : UIViewController<GachaHistoryDialogView>
    {
        public record Argument(GachaHistoryDialogViewModel ViewModel, int CurrentPage = 1);
        
        [Inject] IGachaHistoryDialogViewDelegate ViewDelegate { get; }
        
        public Action OnClose { get; set; } // NOTE: 詳細を開くために閉じる場合はInvokeさせない 
        
        GachaHistoryDialogViewModel _viewModel;
        int _currentPage = 1;
        
        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public void Setup(GachaHistoryDialogViewModel viewModel, int currentPage = 1)
        {
            _currentPage = currentPage;
            _viewModel = viewModel;
            var currentPageVeiwModels = viewModel.GetGachaHistoryCellViewModelsForCurrentPage(currentPage);
            var currentPageDetailViewModels = viewModel.GetGachaHistoryDetailDialogViewModelsForCurrentPage(currentPage);
            ActualView.SetupGachaHistoryCells(currentPageVeiwModels, currentPageDetailViewModels, OnTapCell);
            ActualView.SetupButton(viewModel, currentPage);
        }
        
        public void UpdateView(int currentPage)
        {
            _currentPage = currentPage;
            var currentPageVeiwModels = _viewModel.GetGachaHistoryCellViewModelsForCurrentPage(currentPage);
            var currentPageDetailViewModels = _viewModel.GetGachaHistoryDetailDialogViewModelsForCurrentPage(currentPage);
            ActualView.SetupGachaHistoryCells(currentPageVeiwModels, currentPageDetailViewModels, OnTapCell);
            ActualView.SetupButton(_viewModel, currentPage);
            ActualView.MoveScrollToTop();
        }

        public void OnTapCell(
            GachaHistoryCellViewModel cellViewModel,
            GachaHistoryDetailDialogViewModel detailViewModel)
        {
            ViewDelegate.OnCellTapped(cellViewModel, detailViewModel, _currentPage, ActualView.ScrollPos);
        }
        
        public void ScrollToPage(float targetPos)
        {
            ActualView.MoveScrollToTargetPos(targetPos);
        }
        
        
        [UIAction]
        public void OnCloseButtonTapped()
        {
            OnClose?.Invoke();
            Dismiss();
        }
        
        [UIAction]
        public void OnFirstPageButtonTapped()
        {
            if (!_viewModel.CanGoToFirstPage(_currentPage))
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_013);
                return;
            }

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            _currentPage = 1;
            UpdateView(_currentPage);
        }
        
        [UIAction]
        public void OnPreviousPageButtonTapped()
        {
            if (!_viewModel.CanGoToPreviousPage(_currentPage))
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_013);
                return;
            }

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            _currentPage--;
            UpdateView(_currentPage);
        }
        
        [UIAction]
        public void OnNextPageButtonTapped()
        {
            if (!_viewModel.CanGoToNextPage(_currentPage))
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_013);
                return;
            }

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            _currentPage++;
            UpdateView(_currentPage);
        }

        [UIAction]
        public void OnLastPageButtonTapped()
        {
            if (!_viewModel.CanGoToLastPage(_currentPage))
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_013);
                return;
            }

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            _currentPage = _viewModel.GetLastPageNum();
            UpdateView(_currentPage);
        }
    }
}