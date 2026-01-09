using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EventBonusUnitList.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.EventBonusUnitList.Presentation.Views
{
    /// <summary>
    /// 45_強化クエスト
    /// 　42-5_1日N回強化クエスト
    /// 　　45-1-7-1_ ボーナスキャラ簡易表示
    /// 　　45-1-7-2_ ボーナスキャラ一覧ダイアログ
    /// </summary>
    public class EventBonusUnitListViewController : UIViewController<EventBonusUnitListView>,
        IUICollectionViewDataSource
    {
        public record Argument(
            EventBonusGroupId EventBonusGroupId,
            MasterDataId MstQuestId,
            QuestType QuestType = QuestType.Normal);
        [Inject] IEventBonusUnitListViewDelegate ViewDelegate { get; }
        [Inject] Argument Args { get; }
        EventBonusUnitListViewModel _unitListViewModel;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ActualView.CollectionView.DataSource = this;
            ActualView.SetBonusText(Args.QuestType);
            ViewDelegate.OnViewDidLoad();
        }

        public void Setup(EventBonusUnitListViewModel unitListViewModel)
        {
            _unitListViewModel = unitListViewModel;
            ActualView.CollectionView.ReloadData();
            ActualView.ReformCollectionViewSize();

        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _unitListViewModel?.BonusUnits.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<EventBonusUnitListCell>();
            var viewModel = _unitListViewModel.BonusUnits[indexPath.Row];
            cell.Setup(viewModel.Icon, viewModel.Bonus);
            return cell;
        }

        [UIAction]
        void OnBackButtonTapped()
        {
            ViewDelegate.OnBackButtonTapped();
        }
    }
}
