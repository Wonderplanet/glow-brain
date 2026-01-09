using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.AdventBattleRanking.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.AdventBattleRanking.Presentation.Components
{
    public class AdventBattleRankingOtherUserListComponent : UIObject, IUICollectionViewDataSource, IUICollectionViewDelegate
    {
        [SerializeField] UICollectionView _collectionView;

        IReadOnlyList<AdventBattleRankingOtherUserViewModel> _otherUserViewModels = new List<AdventBattleRankingOtherUserViewModel>();

        public void SetupAndReload(IReadOnlyList<AdventBattleRankingOtherUserViewModel> otherUserViewModels)
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
            var cell = collectionView.DequeueReusableCell<AdventBattleRankingOtherUserCell>();
            var viewModel = _otherUserViewModels[indexPath.Row];

            cell.SetUpUnitImage(viewModel.UnitIconAssetPath);
            cell.SetUpEmblem(viewModel.EmblemIconAssetPath);
            cell.SetUpRank(viewModel.Rank);
            cell.SetUpUserName(viewModel.UserName);
            cell.SetUpMaxScore(viewModel.MaxScore);
            cell.SetUpMyselfMark(viewModel.IsMyself);
            cell.SetUpRankIcon(viewModel.RankType, viewModel.RankLevel);

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
