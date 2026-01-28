using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.AdventBattleMission.Presentation.Component;
using GLOW.Scenes.AdventBattleMission.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.AdventBattleMission.Presentation.View
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-1_降臨バトル基礎実装
    /// 　　44-1-7_ミッションアイコン（専用画面表示も実装に含む）
    /// </summary>
    public class AdventBattleMissionViewController :
        UIViewController<AdventBattleMissionView>,
        IEscapeResponder,
        IUICollectionViewDataSource,
        IUICollectionViewDelegate
    {
        [Inject] IAdventBattleMissionViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        IReadOnlyList<AdventBattleMissionCellViewModel> _adventBattleMissionCellViewModels = new List<AdventBattleMissionCellViewModel>();

        public Action<NotificationBadge> AdventBattleMissionBadgeAction { get; set; }

        public bool Interactable => ActualView.Interactable;

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            
            ActualView.CollectionView.DataSource = this;
            ActualView.CollectionView.Delegate = this;

            ViewDelegate.OnViewWillAppear();
            
            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }
        
        public override void ViewDidUnload()
        {
            base.ViewDidUnload();

            EscapeResponderRegistry.Unregister(this);
        }

        public void SetMissionList(IReadOnlyList<AdventBattleMissionCellViewModel> adventBattleMissionCellViewModels)
        {
            _adventBattleMissionCellViewModels = adventBattleMissionCellViewModels;
            ActualView.CollectionView.ReloadData();
        }

        public void SetInteractable(bool interactable)
        {
            ActualView.Interactable = interactable;
        }

        public void SetIndicatorHidden(bool hidden)
        {
            ActualView.Indicator.Hidden = hidden;
        }

        public void SetBulkReceivable(bool isReceivable)
        {
            ActualView.BulkReceiveButton.interactable = isReceivable;
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _adventBattleMissionCellViewModels.Count;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(
            UICollectionView collectionView,
            UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<AdventBattleMissionListCell>();
            var viewModel = _adventBattleMissionCellViewModels[indexPath.Row];

            cell.SetupAdventBattleMissionCell(viewModel);
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
            var viewModel = _adventBattleMissionCellViewModels[indexPath.Row];
            var buttonKey = identifier.ToString();
            switch (buttonKey)
            {
                case "challenge":
                    ViewDelegate.OnChallengeButtonTapped(viewModel.DestinationScene, () => { });
                    break;
                case "receive":
                    ViewDelegate.OnReceiveButtonTapped(viewModel);
                    break;
                case "resourceDetail":
                    ViewDelegate.OnRewardIconSelected(viewModel.PlayerResourceIconViewModels[0]);
                    break;
                default:
                    Debug.Log("Default");
                    break;
            }
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            ViewDelegate.OnEscape();
            return true;
        }

        [UIAction]
        void OnBulkReceiveButtonTapped()
        {
            ViewDelegate.OnBulkReceiveButtonTapped();
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnCloseButtonTapped();
        }
    }
}
