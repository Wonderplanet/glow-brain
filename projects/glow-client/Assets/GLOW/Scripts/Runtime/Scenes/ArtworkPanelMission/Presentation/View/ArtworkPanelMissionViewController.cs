using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;
using GLOW.Scenes.ArtworkPanelMission.Presentation.ViewModel;
using UIKit;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.ArtworkPanelMission.Presentation.View
{
    public class ArtworkPanelMissionViewController : UIViewController<ArtworkPanelMissionView>
        , IEscapeResponder
        , IAsyncActivityControl
        , IUICollectionViewDataSource
        , IUICollectionViewDelegate
    {
        [Inject] IArtworkPanelMissionViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        
        IReadOnlyList<ArtworkPanelMissionCellViewModel> _cellViewModels;
        
        public record Argument(ArtworkPanelMissionViewModel ViewModel);
        
        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ActualView.InitializeMissionListView(this, this);
            
            ViewDelegate.OnViewDidLoad();
            EscapeResponderRegistry.Bind(this, ActualView);
        }

        public override void ViewWillDisappear(bool animated)
        {
            base.ViewWillDisappear(animated);
            
            // 原画詳細から戻ってきたときにアニメーションが有効になっているとちらつくため無効にする
            ActualView.SetArtworkTextureAnimatorEnable(false);
        }

        public void SetUpArtworkPanelComponent(ArtworkPanelMissionViewModel viewModel)
        {
            ActualView.SetRemainingTimeText(viewModel.RemainingTimeSpan);
            ActualView.SetUpArtworkPanelComponent(viewModel.ArtworkPanelViewModel);
            ActualView.SetUpArtworkBackgroundImage(viewModel.ArtworkPanelViewModel);
            ActualView.SetUpCompleteLabelVisible(
                viewModel.ArtworkPanelMissionFetchResultViewModel.GetAchievedCount(),
                viewModel.ArtworkPanelMissionFetchResultViewModel.GetTotalCount());
        }

        public void SetUpMissionList(ArtworkPanelMissionFetchResultViewModel viewModel)
        {
            _cellViewModels = viewModel.MissionListCellViewModels;
            ActualView.SetUpMissionListView(viewModel.MissionListCellViewModels);
            ActualView.SetUpMissionFractionText(
                viewModel.GetAchievedCount(), 
                viewModel.GetTotalCount());
            ActualView.BulkReceiveButtonInteractable(viewModel.IsExistReceivableMission());
        }
        
        public async UniTask PlayArtworkFragmentAnimation(
            IReadOnlyList<ArtworkFragmentPositionNum> positions, 
            CancellationToken cancellationToken)
        {
            await ActualView.PlayArtworkFragmentAnimation(positions, cancellationToken);
        }

        public void SkipArtworkFragmentAnimation(
            IReadOnlyList<ArtworkFragmentPositionNum> positions)
        {
            ActualView.SkipArtworkFragmentAnimation(positions);
        }

        public async UniTask PlayArtworkCompleteAnimation(HP addHp, CancellationToken cancellationToken)
        {
            await ActualView.PlayArtworkCompleteAnimation(addHp, cancellationToken);
            ActualView.PlayCompleteLabelInAnimation();
        }

        public void SkipArtworkCompleteAnimation()
        {
            ActualView.SkipArtworkCompleteAnimation();
            ActualView.PlayCompleteLabelInAnimation();
        }
        
        public IDisposable ViewTapGuard()
        {
            return this.Activate();
        }
        
        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _cellViewModels?.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(
            UICollectionView collectionView, 
            UIIndexPath indexPath)
        {
            var cell = ActualView.DequeueReusableCell();
            var viewModel = _cellViewModels[indexPath.Row];
            if (viewModel == null) return cell;
            
            cell.SetUpArtworkPanelMissionCell(viewModel);
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
            var viewModel = _cellViewModels[indexPath.Row];
            var buttonKey = identifier.ToString();
            switch (buttonKey)
            {
                case "challenge":
                {
                    ViewDelegate.OnChallengeButtonTapped(viewModel.DestinationScene);
                }break;
                case "receive":
                {
                    ViewDelegate.OnReceiveButtonTapped(viewModel.MstMissionLimitedTermId);
                }break;
                case "resourceDetailArtworkFragment":
                {
                    ViewDelegate.OnArtworkIconTapped(viewModel.ArtworkFragmentPlayerResourceIconViewModel);
                }break;
                case "resourceDetailOther":
                {
                    ViewDelegate.OnRewardIconTapped(viewModel.OtherRewardPlayerResourceIconViewModel);
                }break;
            }
        }
        
        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            Close();
            return true;
        }
        
        void IAsyncActivityControl.ActivityBegin()
        {
            ActualView.SetSkipButtonVisible(true);
        }

        void IAsyncActivityControl.ActivityEnd()
        {
            ActualView.SetSkipButtonVisible(false);
        }

        void Close()
        {
            ViewDelegate.OnCloseButtonTapped();
        }
        
        [UIAction]
        void OnCloseButtonTapped()
        {
            Close();
        }
        
        [UIAction]
        void OnSkipButtonTapped()
        {
            ViewDelegate.OnSkipButtonTapped();
        }
        
        [UIAction]
        void OnBulkReceiveButtonTapped()
        {
            ViewDelegate.OnBulkReceiveButtonTapped();
        }

        [UIAction]
        void OnArtworkPanelTapped()
        {
            var targetViewModel = _cellViewModels.FirstOrDefault(ArtworkPanelMissionCellViewModel.Empty);
            ViewDelegate.OnArtworkIconTapped(targetViewModel.ArtworkFragmentPlayerResourceIconViewModel);
        }
    }
}