using GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.View
{
    public class ArtworkAcquisitionRouteView : UIView
    {
        [SerializeField] UICollectionView _fragmentCollectionView;  //原画のかけら用
        [SerializeField] GameObject _fragmentSourceObject;
        [SerializeField] ArtworkAcquisitionRouteCellView _artworkAcquisitionRouteView;
        [SerializeField] GameObject _artworkSourceScrollView;
        [SerializeField] GameObject _artworkSourceObject;

        public void InitializeCollectionView(
            IUICollectionViewDelegate collectionViewDelegate,
            IUICollectionViewDataSource collectionViewDataSource)
        {
            _fragmentCollectionView.Delegate = collectionViewDelegate;
            _fragmentCollectionView.DataSource = collectionViewDataSource;
        }

        public void Setup(ArtworkAcquisitionRouteViewModel viewModel)
        {
            // 入手先としてクエストが一つもない場合は、原画入手先を表示
            var isFragment = viewModel.FragmentListCellViewModels.Count > 0;
            _fragmentSourceObject.gameObject.SetActive(isFragment);
            _artworkSourceScrollView.SetActive(!isFragment);

            if (isFragment) return;

            _artworkSourceObject.SetActive(true);
            foreach(var cell in viewModel.AcquisitionRoutes)
            {
                var acquisitionRouteView = Instantiate(_artworkAcquisitionRouteView, _artworkSourceObject.transform);
                acquisitionRouteView.Setup(cell);
            }
        }

        public void ReloadData()
        {
            _fragmentCollectionView.ReloadData();
        }
    }
}
