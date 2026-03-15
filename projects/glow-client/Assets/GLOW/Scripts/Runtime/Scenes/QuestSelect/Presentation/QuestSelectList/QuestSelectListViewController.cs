using System;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.QuestSelect.Presentation;
using UIKit;
using Zenject;

namespace GLOW.Scenes.QuestSelectList.Presentation
{
    public class QuestSelectListViewController : UIViewController<QuestSelectListView>,
        IUICollectionViewDelegate,
        IUICollectionViewDataSource
    {
        public record Argument(Action StageSelected, MasterDataId InitialSelectedQuestId);

        [Inject] IQuestSelectListViewDelegate ViewDelegate { get; }

        QuestSelectViewModel _questDataList;
        MasterDataId _initialSelectedMstQuestId;
        QuestSelectContentViewModel _selectingContentViewModel;

        bool _isQuestDecided;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ActualView.InitializeView(this, this);
            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewDidAppear()
        {
            base.ViewDidAppear();
            ActualView.StartChildScalerAnimation();
        }

        public void SetUpView(QuestSelectViewModel viewModel)
        {
            _questDataList = viewModel;
            _selectingContentViewModel = viewModel.Items.ElementAtOrDefault(viewModel.CurrentIndex.Value)
                                         ?? QuestSelectContentViewModel.Empty;
            _initialSelectedMstQuestId = _selectingContentViewModel.GetCurrentDifficultyQuestId();

            ActualView.ReloadData();
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var model = _questDataList.Items[indexPath.Row];
            _selectingContentViewModel = model;
            ViewDelegate.ApplySelectedQuest(model.GetCurrentDifficultyQuestId());
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier)
        {
            // no use.
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _questDataList?.Items.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = ActualView.DequeueReusableCell();
            var model = _questDataList.Items[indexPath.Row];

            cell.Setup(model, _initialSelectedMstQuestId, indexPath.Row == 0, indexPath.Row == 1);
            return cell;
        }

        [UIAction]
        void OnClose()
        {
            ViewDelegate.OnClose();
        }
    }
}
