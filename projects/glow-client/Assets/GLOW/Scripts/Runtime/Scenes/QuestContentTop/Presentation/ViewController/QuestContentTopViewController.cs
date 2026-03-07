using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Modules.Time;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.QuestContentTop.Domain.enums;
using GLOW.Scenes.QuestContentTop.Presentation.ViewModel;
using UIKit;
using Zenject;

namespace GLOW.Scenes.QuestContentTop.Presentation
{
    public class QuestContentTopViewController : HomeBaseViewController<QuestContentTopView>
        , IUICollectionViewDelegate
        , IUICollectionViewDataSource
        , IQuestContentTopViewControllerListener
    {
        [Inject] IQuestContentTopViewDelegate ViewDelegate { get; }

        IReadOnlyList<QuestContentCellViewModel> _cellAllItemViewModels;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ActualView.Initialize(this, this);
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            ViewDelegate.OnViewWillAppear();
        }
        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            ViewDelegate.OnViewDidUnload();
        }

        public void Refresh()
        {
            ViewDelegate.Refresh();
        }

        public void SetViewModel(QuestContentTopViewModel viewModel)
        {
            _cellAllItemViewModels = viewModel.CreateAllItems();
            ActualView.RefreshCollectionView();
        }

        public void SetEventMissionBadge(
            IReadOnlyDictionary<MasterDataId, NotificationBadge> eventMissionBadgeDictionary)
        {
            var eventSectionViewModel = _cellAllItemViewModels
                .FirstOrDefault(
                    model => model.ElementType == QuestContentTopElementType.Event,
                    QuestContentCellViewModel.Empty);

            // 対象の要素が存在しない場合は早期リターン
            if (eventSectionViewModel.IsEmpty()) return;

            var itemCount = _cellAllItemViewModels
                .Count(model => model.ElementType == QuestContentTopElementType.Event);
            var sectionIndex = _cellAllItemViewModels.IndexOf(eventSectionViewModel);

            UpdateCellToEventMissionBadge(sectionIndex, itemCount, eventMissionBadgeDictionary);
        }

        void UpdateCellToEventMissionBadge(
            int sectionIndex,
            int itemCount,
            IReadOnlyDictionary<MasterDataId, NotificationBadge> eventMissionBadgeDictionary)
        {
            for (var i = 0; i < itemCount; i++)
            {
                var item = ActualView.GetCollectionViewCell(sectionIndex, i) as QuestContentCell;
                if (item == null) continue;

                var model = _cellAllItemViewModels[i];
                item.SetNotificationBadge(
                    eventMissionBadgeDictionary.GetValueOrDefault(model.MstEventId, NotificationBadge.False));
            }
        }

        public void ScrollToContentCell(QuestContentTopElementType type)
        {
            var sectionViewModel = _cellAllItemViewModels
                .FirstOrDefault(
                    model => model.ElementType == type,
                    QuestContentCellViewModel.Empty);

            // 対象の要素が存在しない場合は早期リターン
            if (sectionViewModel.IsEmpty()) return;

            // NOTE: viewModelのindex(要素の並び順)とcollectionCellのindex(画面上の並び順)が同じである前提で実装していますが、
            // 初回遷移時以外ではCollectionViewのCell再利用処理によって、viewModelのindex(要素の並び順)とcollectionCellのindex(画面上の並び順)が崩れる可能性があります
            var index = _cellAllItemViewModels.FindIndex(item => item.ElementType == type);

            ActualView.ScrollToRowInSection(0, index);
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var model = _cellAllItemViewModels[indexPath.Row];
            var type = model.ElementType;

            if (!model.CanExecuteTappedAction())
            {
                return;
            }

            switch (type)
            {
                case QuestContentTopElementType.Enhance:
                    ViewDelegate.OnEnhanceButtonTapped();
                    break;
                case QuestContentTopElementType.Event:
                    ViewDelegate.OnEventButtonTapped(model.MstEventId);
                    break;
                case QuestContentTopElementType.AdventBattle:
                    OnAdventBattleButtonTapped(model);
                    break;
                case QuestContentTopElementType.Pvp:
                    OnPvpButtonTapped(model);
                    break;
                case QuestContentTopElementType.Limited:
                    ViewDelegate.OnLimitedButtonTapped();
                    break;
            }
        }

        void OnAdventBattleButtonTapped(QuestContentCellViewModel model)
        {
            if (model.OpeningStatusModel.OpeningStatusAtUserStatus == QuestContentOpeningStatusAtUserStatus.RankLocked)
            {
                CommonToastWireFrame.ShowScreenCenterToast(
                    model.OpeningStatusModel.QuestContentReleaseRequiredSentence.Value);
                return;
            }
            if (model.OpeningStatusModel.OpeningStatusAtTimeType == QuestContentOpeningStatusAtTimeType.BeforeOpen)
            {
                var timeSpanString = TimeSpanFormatter.FormatUntilOpen(model.LimitTime.Value);
                CommonToastWireFrame.ShowScreenCenterToast(timeSpanString);
                return;
            }
            if (!model.IsAdventBattleTransitionable)
            {
                return;
            }
            ViewDelegate.OnRaidButtonTapped();
        }

        void OnPvpButtonTapped(QuestContentCellViewModel model)
        {
            if (model.OpeningStatusModel.OpeningStatusAtUserStatus == QuestContentOpeningStatusAtUserStatus.StageLocked)
            {
                CommonToastWireFrame.ShowScreenCenterToast(
                    model.OpeningStatusModel.QuestContentReleaseRequiredSentence.Value);
                return;
            }

            if (model.OpeningStatusModel.OpeningStatusAtTimeType == QuestContentOpeningStatusAtTimeType.Totalizing)
            {
                CommonToastWireFrame.ShowScreenCenterToast(
                    "現在ランキング結果の集計中になります\n集計終了までお待ちください");
                return;
            }
            ViewDelegate.OnPvpButtonTapped();
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(
            UICollectionView collectionView,
            UIIndexPath indexPath,
            object identifier)
        {
            if ((string)identifier == "ranking")
            {
                ViewDelegate.OnRankingButtonTapped();
            }
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _cellAllItemViewModels?.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(
            UICollectionView collectionView,
            UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<QuestContentCell>();
            var model = _cellAllItemViewModels[indexPath.Row];
            cell.SetContentCell(model);
            return cell;
        }

        [UIAction]
        void OnEnhanceButtonTapped()
        {
            ViewDelegate.OnEnhanceButtonTapped();
        }
        [UIAction]
        void OnRaidButtonTapped()
        {
            ViewDelegate.OnRaidButtonTapped();
        }
        [UIAction]
        void OnLimitedButtonTapped()
        {
            ViewDelegate.OnLimitedButtonTapped();
        }
        [UIAction]
        void OnBattleButtonTapped()
        {
            ViewDelegate.OnPvpButtonTapped();
        }

    }
}
