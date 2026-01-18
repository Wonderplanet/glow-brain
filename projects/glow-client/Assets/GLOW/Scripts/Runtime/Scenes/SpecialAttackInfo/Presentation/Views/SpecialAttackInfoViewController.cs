using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.SpecialAttackInfo.Presentation.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Scenes.SpecialAttackInfo.Presentation.Views
{
    public class SpecialAttackInfoViewController : UIViewController<SpecialAttackInfoView>,
        IUICollectionViewDataSource
    {
        public record Argument(MasterDataId UnitId, UnitGrade UnitGrade, UnitLevel UnitLevel);

        [Inject] ISpecialAttackInfoViewDelegate ViewDelegate { get; }

        IReadOnlyList<SpecialAttackInfoGradeViewModel> _infoRankViewModelList;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();

            ActualView.RankCollectionView.DataSource = this;
        }

        public void SetInfo(SpecialAttackInfoViewModel viewModel)
        {
            ActualView.Setup(viewModel);
        }

        public void SetInfoRankModelList(SpecialAttackInfoViewModel viewModel)
        {
            _infoRankViewModelList = viewModel.RankViewModelList;
            ActualView.RankCollectionView.ReloadData();
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _infoRankViewModelList?.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<SpecialAttackInfoGradeCell>();
            var rankViewModel = _infoRankViewModelList[indexPath.Row];
            if (rankViewModel == null)
            {
                return cell;
            }

            cell.Setup(rankViewModel);
            return cell;
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnClose();
        }
    }
}
