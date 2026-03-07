using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels;
using GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.Views;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.View
{
    public class ArtworkAcquisitionRouteViewController :
        UIViewController<ArtworkAcquisitionRouteView>,
        IUICollectionViewDelegate,
        IUICollectionViewDataSource,
        IEscapeResponder
    {
        public record Argument(MasterDataId MstArtworkId);
        [Inject] IArtworkAcquisitionRouteDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        public Action OnTransitionAction { get; set; }

        ArtworkAcquisitionRouteViewModel _viewModel;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();

            EscapeResponderRegistry.Register(this);
        }

        public void SetUpView(ArtworkAcquisitionRouteViewModel viewModel)
        {
            _viewModel = viewModel;
            ActualView.Setup(viewModel);

            ActualView.ReloadData();
        }

        public void InitUICollectionView()
        {
            ActualView.InitializeCollectionView(this, this);

        }

        public void CloseView()
        {
            EscapeResponderRegistry.Unregister(this);
            Dismiss();
        }

        bool IEscapeResponder.OnEscape()
        {
            if (View.Hidden) return false;

            ViewDelegate.OnBackButtonTapped();
            return true;
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            // 必要なし
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier)
        {
            ViewDelegate.OnSelectFragmentDropQuest(_viewModel.FragmentListCellViewModels[indexPath.Row]);
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _viewModel?.FragmentListCellViewModels.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<EncyclopediaArtworkFragmentListCell>();
            var cellViewModel = _viewModel.FragmentListCellViewModels[indexPath.Row];
            cell.Setup(cellViewModel);
            return cell;
        }

        [UIAction]
        void BackButtonTapped()
        {
            ViewDelegate.OnBackButtonTapped();
        }
    }
}
