using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.BeginnerMission.Presentation.Component;
using GLOW.Scenes.BeginnerMission.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.BeginnerMission.Presentation.View
{
    /// <summary>
    /// 61_ミッション
    /// 　61-1-5_初心者ミッション
    /// </summary>
    public class BeginnerMissionContentViewController :
        UIViewController<BeginnerMissionContentView>,
        IUICollectionViewDataSource,
        IUICollectionViewDelegate,
        IEscapeResponder
    {
        public record Argument(IBeginnerMissionContentViewModel ViewModel, BeginnerMissionDayNumber CurrentDayNumber);
        
        [Inject] IBeginnerMissionContentViewDelegate ViewDelegate { get; }
        
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        
        public Action<IBeginnerMissionMainViewModel> OnReceivedAction { get; set; }
        
        IReadOnlyList<IBeginnerMissionCellViewModel> _beginnerMissionCellViewModels;
        
        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
            
            EscapeResponderRegistry.Bind(this, ActualView);
            ActualView.CollectionView.DataSource = this;
            ActualView.CollectionView.Delegate = this;
        }

        public void SetViewModel(IBeginnerMissionContentViewModel viewModel)
        {
            _beginnerMissionCellViewModels = viewModel.BeginnerMissionCellViewModels;
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
            return _beginnerMissionCellViewModels?.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(
            UICollectionView collectionView,
            UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<BeginnerMissionListCell>();
            var viewModel = _beginnerMissionCellViewModels[indexPath.Row];
            if (viewModel == null)
                return cell;
            
            cell.SetupBeginnerMissionCell(viewModel);
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
            var viewModel = _beginnerMissionCellViewModels[indexPath.Row];
            string buttonKey = identifier.ToString();
            switch (buttonKey)
            {
                case "challenge":
                {
                    if (viewModel.IsLock)
                    {
                        ViewDelegate.OnUnlockMissionButtonSelected();
                        return;
                    }
                    
                    ViewDelegate.OnChallenge(
                        viewModel.BeginnerMissionId,
                        viewModel.DestinationScene, 
                        viewModel.CriterionValue,
                        () => { });
                }break;
                case "receive":
                {
                    if (viewModel.IsLock)
                    {
                        ViewDelegate.OnUnlockMissionButtonSelected();
                        return;
                    }
                    
                    ViewDelegate.ReceiveMissionReward(viewModel);
                }break;
                case "resourceDetailLeft":
                {
                    ViewDelegate.OnRewardIconSelected(viewModel.PlayerResourceIconViewModels[0]);
                }break;
                case "resourceDetailRight":
                {
                    ViewDelegate.OnRewardIconSelected(viewModel.PlayerResourceIconViewModels[1]);
                }break;
                case "missionBonusPoint":
                {
                    ViewDelegate.OnMissionBonusPointSelected();
                }break;
                default:
                {
                }break;
            }
        }
    }
}