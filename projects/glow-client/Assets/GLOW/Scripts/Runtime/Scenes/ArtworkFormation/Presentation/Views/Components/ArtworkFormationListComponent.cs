using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.ArtworkFormation.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.ArtworkFormation.Presentation.Views.Components
{
    public interface IArtworkFormationListComponentDelegate
    {
        void OnListCellTapped(MasterDataId mstArtworkId, ArtworkCompleteFlag flag, ArtworkFormationListCell cell);
        void OnListCellLongTapped(MasterDataId mstArtworkId, List<ArtworkFormationListCellViewModel> mstArtworkIds);
    }
    
    public class ArtworkFormationListComponent :
        UIObject,
        IUICollectionViewDelegate,
        IUICollectionViewDataSource
    {
        [SerializeField] UICollectionView _artworkListView;
        [SerializeField] ArtworkFormationListCell _cellPrefab;
        [SerializeField] ChildScaler _childScaler;
        
        public IArtworkFormationListComponentDelegate Delegate { private get; set; }
        
        ArtworkFormationListViewModel _viewModel;
        bool _hasPlayedInitialAnimation;
        
        protected override void Awake()
        {
            _artworkListView.Delegate = this;
            _artworkListView.DataSource = this;
        }
        
        public void SetUp(ArtworkFormationListViewModel viewModel)
        {
            _viewModel = viewModel;
            _artworkListView.ReloadData();
            
            // 初回表示時のみアニメーションを再生
            if (_childScaler != null && !_hasPlayedInitialAnimation)
            {
                _hasPlayedInitialAnimation = true;
                StartCoroutine(PlayAnimationAfterReload());
            }
        }
        
        System.Collections.IEnumerator PlayAnimationAfterReload()
        {
            // 1フレーム待ってセルが生成されるのを待つ
            yield return null;
            _childScaler.Play();
        }

        public void UpdateCellAssignment(
            MasterDataId mstArtworkId,
            ArtworkFormationListCellViewModel cellViewModel,
            ArtworkFormationListCell targetCell = null)
        {
            // 該当するセルを探してindexPathを特定
            var index = _viewModel.CellViewModels.FindIndex(vm => vm.MstArtworkId == mstArtworkId);
            if (index < 0) return;

            // ViewModelを更新
            _viewModel.CellViewModels[index] = cellViewModel;

            // targetCellが指定されている場合はそれを使用
            if (targetCell != null)
            {
                targetCell.SetUp(cellViewModel);
                return;
            }

            // UICollectionViewから該当セルを取得(画面に表示されているセルのみ取得可能)
            var indexPath = new UIIndexPath(0,index);
            var cell = _artworkListView.CellForRow(indexPath);
            
            if (cell == null)
            {
                // セルが画面外にある場合はViewModelの更新のみで終了
                // 次にセルが表示される際にCellForItemAtIndexPathで正しいViewModelが使用される
                return;
            }

            // セルが画面に表示されている場合は即座に更新
            var listCell = cell as ArtworkFormationListCell;
            if (listCell == null)
            {
                return;
            }

            listCell.SetUp(cellViewModel);
        }
        
        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _viewModel?.CellViewModels.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(
            UICollectionView collectionView,
            UIIndexPath indexPath)
        {
            var viewModel = _viewModel.CellViewModels[indexPath.Row];
            
            var cell = collectionView.DequeueReusableCell<ArtworkFormationListCell>(item => item.MstArtworkId == viewModel.MstArtworkId);
            cell.SetUp(viewModel);
            cell.LongPress.PointerDown.RemoveAllListeners();
            cell.LongPress.PointerDown.AddListener(() =>
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
                Delegate?.OnListCellLongTapped(viewModel.MstArtworkId, _viewModel.CellViewModels);
            });
            // タップ時の処理はDidSelectRowAtIndexPathで行う
            return cell;
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            // セルタップ時の処理
            var viewModel = _viewModel.CellViewModels[indexPath.Row];
            
            // タップされたセルの参照を取得
            var cell = collectionView.CellForRow(indexPath) as ArtworkFormationListCell;
            
            Delegate?.OnListCellTapped(viewModel.MstArtworkId, viewModel.IsCompleted, cell);
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(
            UICollectionView collectionView,
            UIIndexPath indexPath,
            object identifier)
        {
        }
    }
}