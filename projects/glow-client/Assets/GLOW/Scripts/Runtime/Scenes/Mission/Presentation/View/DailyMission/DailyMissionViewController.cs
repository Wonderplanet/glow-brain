using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.Mission.Presentation.ViewModel.BonusPointMission;
using GLOW.Scenes.Mission.Presentation.ViewModel.DailyMission;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.Mission.Presentation.View.DailyMission
{
    public class DailyMissionViewController : UIViewController<DailyMissionView>,
        IUICollectionViewDataSource,
        IUICollectionViewDelegate,
        IEscapeResponder
    {
        public record Argument(IDailyMissionViewModel ViewModel, Action<IDailyMissionViewModel> OnReceivedAction);
        [Inject] IDailyMissionViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        IDailyMissionViewModel _viewModel;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();

            EscapeResponderRegistry.Bind(this, ActualView);
            ActualView.CollectionView.DataSource = this;
            ActualView.CollectionView.Delegate = this;
        }

        public void SetViewModel(IDailyMissionViewModel viewModel)
        {
            _viewModel = viewModel;
            ActualView.CollectionView.ReloadData();
        }

        public void UpdateBonusPointComponent()
        {
            ActualView.BonusPointComponent.Setup(_viewModel.BonusPointMissionViewModel, ShowRewardListWindow);
            ActualView.SetUpdateTime(_viewModel.BonusPointMissionViewModel.NextUpdateDatetime);
        }
        
        public async UniTask OpenRewardBoxAnimation(
            CancellationToken cancellationToken, 
            BonusPoint bonusPoint)
        {
            await ActualView.OpenRewardBoxAnimationAsync(bonusPoint, cancellationToken);
        }
        
        public void SetupBonusPointGaugeRate(
            BonusPoint currentBonusPoint, 
            BonusPoint maxBonusPoint)
        {
            ActualView.BonusPointComponent.SetBonusPointNumber(currentBonusPoint);
            ActualView.BonusPointComponent.SetProgressGaugeRate(
                currentBonusPoint.ToGaugeRate(maxBonusPoint));
        }

        public async UniTask PlayBonusPointGaugeAnimation(CancellationToken cancellationToken,
            BonusPoint updatedBonusPoint,
            BonusPoint maxBonusPoint)
        {
            ActualView.BonusPointComponent.SetBonusPointNumber(updatedBonusPoint);
            await ActualView.BonusPointComponent.PlayProgressGaugeAnimation(
                cancellationToken,
                updatedBonusPoint,
                maxBonusPoint);
        }

        public void UpdateMissionNextUpdateTime(RemainingTimeSpan nextUpdateTime)
        {
            ActualView.SetUpdateTime(nextUpdateTime);
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
            return _viewModel.DailyMissionCellViewModels?.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(
            UICollectionView collectionView,
            UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<DailyMissionListCell>();
            var viewModel = _viewModel.DailyMissionCellViewModels[indexPath.Row];
            if (viewModel == null)
                return cell;

            cell.SetupDailyMissionCell(viewModel);
            return cell;
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            // no use.
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(
            UICollectionView collectionView,
            UIIndexPath indexPath,
            object identifier)
        {
            var viewModel = _viewModel.DailyMissionCellViewModels[indexPath.Row];
            string buttonKey = identifier.ToString();
            switch (buttonKey)
            {
                case "challenge":
                {
                    ViewDelegate.OnChallenge(viewModel.DestinationScene);
                    Debug.Log("challenge");
                }break;
                case "receive":
                {
                    ViewDelegate.ReceiveAchievedMissionRewards(viewModel.DailyMissionId);
                    Debug.Log("receive");
                }break;
                case "missionBonusPoint":
                {
                    ViewDelegate.OnMissionBonusPointSelected();
                    Debug.Log("receive");
                }break;
                default:
                {
                    Debug.Log("Default");
                }break;
            }
        }

        void ShowRewardListWindow(IReadOnlyList<PlayerResourceIconViewModel> viewModels, RectTransform windowPosition)
        {
            ViewDelegate.ShowRewardListWindow(viewModels, windowPosition);
        }
    }
}
