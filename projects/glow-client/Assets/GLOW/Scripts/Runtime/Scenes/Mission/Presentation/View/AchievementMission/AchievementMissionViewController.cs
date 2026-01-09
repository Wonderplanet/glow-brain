using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Scenes.Mission.Presentation.ViewModel.AchievementMission;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.Mission.Presentation.View.AchievementMission
{
    public class AchievementMissionViewController :
        UIViewController<AchievementMissionView>,
        IUICollectionViewDataSource,
        IUICollectionViewDelegate,
        IEscapeResponder
    {
        public record Argument(IAchievementMissionViewModel ViewModel, Action<IAchievementMissionViewModel> OnReceivedAction);

        [Inject] IAchievementMissionViewDelegate ViewDelegate { get; }

        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        IReadOnlyList<IAchievementMissionCellViewModel> _achievementMissionCellViewModels;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();

            EscapeResponderRegistry.Bind(this, ActualView);
            ActualView.CollectionView.DataSource = this;
            ActualView.CollectionView.Delegate = this;
        }

        public void SetViewModel(IAchievementMissionViewModel viewModel)
        {
            _achievementMissionCellViewModels = viewModel.AchievementMissionCellViewModels;
            ActualView.CollectionView.ReloadData();
        }
        
        public void SetOnApplicationFocusedAction(Action action)
        {
            ActualView.SetOnApplicationFocusedAction(action);
        }
        
        public void ClearOnApplicationFocusedAction()
        {
            ActualView.ClearOnApplicationFocusedAction();
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
            return _achievementMissionCellViewModels?.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(
            UICollectionView collectionView,
            UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<AchievementMissionListCell>();
            var viewModel = _achievementMissionCellViewModels[indexPath.Row];
            if (viewModel == null)
                return cell;

            cell.SetupAchievementMissionCell(viewModel);
            return cell;
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(
            UICollectionView collectionView,
            UIIndexPath indexPath)
        {
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(
            UICollectionView collectionView,
            UIIndexPath indexPath,
            object identifier)
        {
            var viewModel = _achievementMissionCellViewModels[indexPath.Row];
            string buttonKey = identifier.ToString();
            switch (buttonKey)
            {
                case "challenge":
                {
                    ViewDelegate.OnChallenge(
                        viewModel.AchievementMissionId,
                        viewModel.DestinationScene,
                        viewModel.CriterionValue);
                    Debug.Log("challenge");
                }break;
                case "receive":
                {
                    ViewDelegate.ReceiveReward(viewModel);
                    Debug.Log("receive");
                }break;
                case "resourceDetail":
                {
                    ViewDelegate.OnRewardIconSelected(viewModel.PlayerResourceIconViewModels.First());
                    Debug.Log("resourceDetail");
                }break;
                default:
                {
                    Debug.Log("Default");
                }break;
            }
        }
    }
}
