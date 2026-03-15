using System.Collections.Generic;
using GLOW.Scenes.EnhanceQuestScoreDetail.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.EnhanceQuestScoreDetail.Presentation.Views
{
    /// <summary>
    /// 45_強化クエスト
    /// 　42-5_1日N回強化クエスト
    /// 　　42-5-3-1_スコア獲得条件ダイアログ
    /// </summary>
    public class EnhanceQuestScoreDetailView : UIView
        , IUICollectionViewDataSource
        , IUICollectionViewDelegate
    {
        [SerializeField] UICollectionView _collectionView;

        IReadOnlyList<EnhanceQuestScoreDetailCellViewModel> _cellViewModels;

        protected override void Awake()
        {
            base.Awake();

            _collectionView.DataSource = this;
            _collectionView.Delegate = this;
        }

        public void SetUpListView(IReadOnlyList<EnhanceQuestScoreDetailCellViewModel> cellViewModels)
        {
            _cellViewModels = cellViewModels;
            _collectionView.ScrollRect.verticalNormalizedPosition = 1.0f;
            _collectionView.ReloadData();
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _cellViewModels.Count;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<EnhanceQuestScoreDetailListCell>();
            var viewModel = _cellViewModels[indexPath.Row];
            cell.SetUpScore(viewModel.EnhanceQuestMinThresholdScore);
            cell.SetUpRewardMultiplier(viewModel.CoinRewardAmount);
            cell.SetUpRewardIcon(viewModel.CoinRewardSizeType);
            return cell;
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier)
        {
        }
    }
}
