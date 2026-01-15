using System;
using System.Collections.Generic;
using GLOW.Scenes.Home.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Views
{
    public sealed class HomeStageSelectViewController : UIViewController<HomeStageSelectView>, IUICollectionViewDataSource, IUICollectionViewDelegate
    {
        [Inject] IHomeStageSelectViewDelegate ViewDelegate { get; }

        IReadOnlyList<HomeMainStageViewModel> _stageDataList;

        Action<string> _onStageSelected;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ViewDelegate.OnViewDidLoad();

            ActualView.StageCollectionView.DataSource = this;
            ActualView.StageCollectionView.Delegate = this;
        }

        public void SetStageDataList(HomeMainQuestViewModel questViewModel)
        {
            // NOTE: データをセットした後にリロードを行い、セルの再描画を行う
            _stageDataList = questViewModel.Stages;
            ActualView.StageCollectionView.ReloadData();
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _stageDataList?.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<HomeStageSelectCell>();
            var stageData = _stageDataList[indexPath.Row];
            if (stageData == null)
            {
                return cell;
            }

            cell.NameText = stageData.StageName.Value;
            cell.DescriptionText = stageData.ReleaseRequireSentence.Value;
            cell.ScoreText = "-1";
            // cell.HomeMainStageSymbolImage.AssetPath = stageData.;
            return cell;
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var stageData = _stageDataList[indexPath.Row];
            ViewDelegate?.OnStageSelected(stageData);

            if (stageData != null)
            {
                _onStageSelected?.Invoke(stageData.MstStageId.Value);
            }
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier)
        {
            //no use.
        }

        public void SetSelectedAction(Action<string> onStageSelected)
        {
            _onStageSelected = onStageSelected;
        }
    }
}
