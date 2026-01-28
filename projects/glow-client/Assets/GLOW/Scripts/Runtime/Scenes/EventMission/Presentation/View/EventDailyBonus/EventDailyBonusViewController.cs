using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.EventMission.Presentation.View.EventMissionMain;
using GLOW.Scenes.EventMission.Presentation.ViewModel.EventDailyBonus;
using UIKit;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.EventMission.Presentation.View.EventDailyBonus
{
    /// <summary>
    /// 61_ミッション
    /// 　61-1-3_ログインボーナス
    /// </summary>
    public class EventDailyBonusViewController :
        UIViewController<EventDailyBonusView>,
        IUICollectionViewDataSource,
        IUICollectionViewDelegate,
        IEscapeResponder,
        IAsyncActivityControl
    {
        public record Argument(EventDailyBonusViewModel ViewModel);
        [Inject] IEventDailyBonusViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] IEventMissionMainControl EventMissionMainViewControl { get; }

        IReadOnlyList<DailyBonusCollectionCellViewModel> _eventDailyBonusCellViewModels;
        public Action<EventDailyBonusViewModel> OnReceivedAction { get; set; }

        // 13日目以降は自動スクロールさせる
        const int NeedScrollCellIndexThreshold = 12;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
            
            ActualView.CollectionView.DataSource = this;
            ActualView.CollectionView.Delegate = this;
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            
            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            EscapeResponderRegistry.Unregister(this);
        }

        public void SetViewModel(EventDailyBonusViewModel viewModel)
        {
            _eventDailyBonusCellViewModels = viewModel.EventMissionDailyBonusCellModels;
            ActualView.CollectionView.ReloadData();
        }

        public async UniTask PlayAnimation(LoginDayCount loginDayCount, CancellationToken cancellationToken)
        {
            var cell = ActualView.CollectionView.CellForRow(new UIIndexPath(0, loginDayCount.Value-1));
            if(cell == null)
                return;

            var eventDailyBonusCell = cell as DailyBonusCollectionCellComponent;
            if(eventDailyBonusCell == null)
                return;

            if (loginDayCount.Value > NeedScrollCellIndexThreshold)
            {
                // 13日目以降は自動スクロールさせる
                await ActualView.MoveScrollToCell(eventDailyBonusCell, cancellationToken);
            }

            await eventDailyBonusCell.PlayReceiveAnimation(cancellationToken);
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden)
            {
                return false;
            }

            UISoundEffector.Main.PlaySeEscape();
            ViewDelegate.OnEscape();
            return true;
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _eventDailyBonusCellViewModels?.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(
            UICollectionView collectionView,
            UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<DailyBonusCollectionCellComponent>();
            var viewModel = _eventDailyBonusCellViewModels[indexPath.Row];
            if (viewModel == null)
                return cell;

            cell.SetUpDailyBonusCell(viewModel);
            return cell;
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {

        }

        public void AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier)
        {
            var viewModel = _eventDailyBonusCellViewModels[indexPath.Row];
            var buttonKey = identifier.ToString();
            switch (buttonKey)
            {
                case "resourceDetail":
                    ViewDelegate.OnRewardIconSelected(viewModel.PlayerResourceIconViewModel); 
                    break;
                default: 
                    break;
            }
        }

        void IAsyncActivityControl.ActivityBegin()
        {
            EventMissionMainViewControl.SetInteractable(false);
        }

        void IAsyncActivityControl.ActivityEnd()
        {
            EventMissionMainViewControl.SetInteractable(true);
        }
    }
}
