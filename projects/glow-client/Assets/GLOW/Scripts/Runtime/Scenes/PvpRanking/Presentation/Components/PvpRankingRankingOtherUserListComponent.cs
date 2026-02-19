using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.PvpRanking.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.PvpRanking.Presentation.Components
{
    public class PvpRankingRankingOtherUserListComponent : UIObject, IUICollectionViewDataSource, IUICollectionViewDelegate
    {
        [SerializeField] UICollectionView _collectionView;

        IReadOnlyList<PvpRankingOtherUserViewModel> _otherUserViewModels = new List<PvpRankingOtherUserViewModel>();

        public void SetupAndReload(IReadOnlyList<PvpRankingOtherUserViewModel> otherUserViewModels)
        {
            _collectionView.DataSource = this;
            _collectionView.Delegate = this;

            _otherUserViewModels = otherUserViewModels;
            _collectionView.ReloadData();
        }

        public int NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _otherUserViewModels.Count;
        }

        public UICollectionViewCell CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<PvpRankingOtherUserCell>();
            var viewModel = _otherUserViewModels[indexPath.Row];

            cell.SetUpUnitImage(viewModel.UnitIconAssetPath);
            cell.SetUpEmblem(viewModel.EmblemIconAssetPath);
            cell.SetUpRank(viewModel.Rank);
            cell.SetUpUserName(viewModel.UserName);
            cell.SetUpScore(viewModel.Score);
            cell.SetUpMyselfMark(viewModel.IsMyself);
            cell.SetUpPvpRankIcon(viewModel.PvpUserRankStatus);

            return cell;
        }

        public void DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
        }

        public void AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier)
        {
        }
    }
}
