using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.DiamondPurchaseHistory.Domain;
using UIKit;
using Zenject;

namespace GLOW.Scenes.DiamondPurchaseHistory.Presentation
{
    public class DiamondPurchaseHistoryViewController : UIViewController<DiamondPurchaseHistoryView>
    {
        [Inject] IDiamondPurchaseHistoryViewDelegate ViewDelegate { get; }

        DiamondPurchaseHistoryViewModel _viewModel;
        PageNumber _currentPage;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ActualView.InitializeView();
            ViewDelegate.OnViewDidLoad();
        }

        public void SetUpView(DiamondPurchaseHistoryViewModel viewModel)
        {
            _viewModel = viewModel;
            _currentPage = viewModel.CurrentPage;
            ActualView.SetUpView(viewModel, viewModel.CurrentPage);
        }

        void UpdateView(PageNumber pageNumber)
        {
            _currentPage = pageNumber;
            ActualView.SetUpView(_viewModel, pageNumber);
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            Dismiss();
        }

        [UIAction]
        void OnFirstPageButtonTapped()
        {
            if (!_viewModel.CanGoToFirstPage(_currentPage))
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_013);
                return;
            }

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            _currentPage = new PageNumber(1);
            UpdateView(_currentPage);
        }
        [UIAction]
        void OnPreviousPageButtonTapped()
        {
            if (!_viewModel.CanGoToPreviousPage(_currentPage))
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_013);
                return;
            }

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            _currentPage = _currentPage - 1;
            UpdateView(_currentPage);
        }
        [UIAction]
        void OnNextPageButtonTapped()
        {
            if (!_viewModel.CanGoToNextPage(_currentPage))
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_013);
                return;
            }

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            _currentPage = _currentPage + 1;
            UpdateView(_currentPage);
        }
        [UIAction]
        void OnLastPageButtonTapped()
        {
            if (!_viewModel.CanGoToLastPage(_currentPage))
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_013);
                return;
            }

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            _currentPage = _viewModel.MaxPage;
            UpdateView(_currentPage);
        }
    }
}
