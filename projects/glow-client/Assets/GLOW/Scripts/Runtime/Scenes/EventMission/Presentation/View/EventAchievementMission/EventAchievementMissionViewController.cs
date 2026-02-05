using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EventMission.Presentation.Component;
using GLOW.Scenes.EventMission.Presentation.ViewModel.EventAchievementMission;
using GLOW.Scenes.EventMission.Presentation.ViewModel.EventMissionCell;
using UIKit;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.EventMission.Presentation.View.EventAchievementMission
{
    /// <summary>
    /// 61_ミッション
    /// 　61-1-2_アチーブメント（累計ミッション）
    /// </summary>
    public class EventAchievementMissionViewController : UIViewController<EventAchievementMissionView>,
        IUICollectionViewDataSource,
        IUICollectionViewDelegate,
        IEscapeResponder
    {
        public record Argument(
            EventAchievementMissionViewModel ViewModel,
            MasterDataId DisplayMissionMstEventId,
            bool IsEventMissionOpenInHome);

        [Inject] IEventAchievementMissionViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        public Action<EventAchievementMissionViewModel> OnReceivedAction { get; set; }

        IReadOnlyList<IEventMissionCellViewModel> _eventMissionCellViewModels;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();

            EscapeResponderRegistry.Bind(this, ActualView);
            ActualView.CollectionView.DataSource = this;
            ActualView.CollectionView.Delegate = this;
        }

        public void SetViewModel(EventAchievementMissionViewModel viewModel)
        {
            _eventMissionCellViewModels = viewModel.EventAchievementMissionCellViewModels;
            ActualView.CollectionView.ReloadData();
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden)
                return false;

            UISoundEffector.Main.PlaySeEscape();
            ViewDelegate.OnEscape();
            return true;
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _eventMissionCellViewModels?.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(
            UICollectionView collectionView,
            UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<EventMissionListCell>();
            var viewModel = _eventMissionCellViewModels[indexPath.Row];
            if (viewModel == null)
                return cell;

            cell.SetupEventMissionCell(viewModel);
            return cell;
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {

        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(
            UICollectionView collectionView,
            UIIndexPath indexPath,
            object identifier)
        {
            var viewModel = _eventMissionCellViewModels[indexPath.Row];
            string buttonKey = identifier.ToString();
            switch (buttonKey)
            {
                case "challenge":
                {
                    ViewDelegate.OnChallenge(viewModel, () => { });
                }break;
                case "receive":
                {
                    ViewDelegate.ReceiveMissionReward(viewModel);
                }break;
                case "resourceDetail":
                {
                    ViewDelegate.OnRewardIconSelected(viewModel.PlayerResourceIconViewModels.First());
                }break;
                default:
                {
                }break;
            }
        }
    }
}
