using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.OutpostEnhance.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.OutpostEnhance.Presentation.Views.Component
{
    public interface IOutpostEnhanceArtworkListComponentDelegate
    {
        void ChangeArtworkSelection(MasterDataId mstArtworkId);
        void ShowArtworkDetail(MasterDataId mstArtworkId, OutpostEnhanceArtworkListViewModel viewModel);
    }

    public class OutpostEnhanceArtworkListComponent : UIObject,
        IUICollectionViewDelegate,
        IUICollectionViewDataSource
    {
        [SerializeField] UICollectionView _collectionView;
        [SerializeField] ChildScaler _childScaler;
        OutpostEnhanceArtworkListViewModel _viewModel;
        public IOutpostEnhanceArtworkListComponentDelegate Delegate { private get; set; }

        protected override void Awake()
        {
            _collectionView.Delegate = this;
            _collectionView.DataSource = this;
        }

        public void Setup(OutpostEnhanceArtworkListViewModel viewModel)
        {
            _viewModel = viewModel;
            _collectionView.ReloadData();
        }

        public void PlayCellAppearanceAnimation()
        {
            _childScaler.Play();
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _viewModel?.Cells.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(
            UICollectionView collectionView,
            UIIndexPath indexPath)
        {
            var viewModel = _viewModel.Cells[indexPath.Row];
            var cell = collectionView.DequeueReusableCell<OutpostEnhanceArtworkListCellComponent>(item => item.MstArtworkId == viewModel.MstArtworkId);
            cell.Setup(viewModel);
            cell.LongPress.PointerDown.RemoveAllListeners();
            cell.LongPress.PointerDown.AddListener(() =>
                {
                    SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
                    Delegate?.ShowArtworkDetail(viewModel.MstArtworkId, _viewModel);
                });
            return cell;
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var viewModel = _viewModel.Cells[indexPath.Row];
            if (viewModel.IsSelect) return;

            if (viewModel.IsLock)
            {
                Delegate?.ShowArtworkDetail(viewModel.MstArtworkId, _viewModel);
            }
            else
            {
                Delegate?.ChangeArtworkSelection(viewModel.MstArtworkId);
            }
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(
            UICollectionView collectionView,
            UIIndexPath indexPath,
            object identifier)
        {
        }
    }
}
