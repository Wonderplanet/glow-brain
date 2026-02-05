using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ComeBackDailyBonus.Presentation.ViewModel;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.ComebackDailyBonus.Presentation.View
{
    public class ComebackDailyBonusViewController : 
        UIViewController<ComebackDailyBonusView>,
        IEscapeResponder,
        IUICollectionViewDataSource,
        IUICollectionViewDelegate
    {
        public record Argument(MasterDataId MstComebackDailyBonusScheduleId);
        
        public Action OnCloseCompletion { get; set; }
        
        [Inject] IComebackDailyBonusViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] ISystemSoundEffectProvider SystemSoundEffectProvider { get; }

        IReadOnlyList<DailyBonusCollectionCellViewModel> _viewModels = new List<DailyBonusCollectionCellViewModel>();
        
        // 16日目以降は自動スクロールさせる
        const int NeedScrollCellIndexThreshold = 16;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            
            ActualView.InitializeCollectionView(this, this);
            
            ViewDelegate.OnViewDidLoad();
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
        
        public void SetUpComebackDailyBonusView(ComebackDailyBonusViewModel viewModel)
        {
            ActualView.SetRemainingTime(viewModel.RemainingTime);
            _viewModels = viewModel.ComebackDailyBonusCellModels;
            ActualView.ReloadCollectionView();
        }
        
        public async UniTask PlayAnimation(LoginDayCount loginDayCount, CancellationToken cancellationToken)
        {
            if (loginDayCount.Value > NeedScrollCellIndexThreshold)
            {
                // 一定の日数以上の場合には一番下までスクロールする
                await ActualView.MoveScrollToBottom(cancellationToken);
            }
            
            var cell = ActualView.GetCollectionViewCellFromLoginDayCount(loginDayCount);
            if(cell == null)
                return;

            var eventDailyBonusCell = cell as DailyBonusCollectionCellComponent;
            if(eventDailyBonusCell == null)
                return;

            await eventDailyBonusCell.PlayReceiveAnimation(cancellationToken);
        }
        
        public void SetCloseButtonInteractable(bool interactable)
        {
            ActualView.SetCloseButtonInteractable(interactable);
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;
            
            SystemSoundEffectProvider.PlaySeEscape();
            ViewDelegate.OnCloseButtonSelected();
            return true;
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _viewModels?.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<DailyBonusCollectionCellComponent>();
            var viewModel = _viewModels[indexPath.Row];
            
            if (viewModel == null) return cell;
            
            cell.SetUpDailyBonusCell(viewModel);
            return cell;
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath) { }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(UICollectionView collectionView,
            UIIndexPath indexPath, object identifier)
        {
            var viewModel = _viewModels[indexPath.Row];
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
        
        [UIAction]
        void OnCloseButtonSelected()
        {
            ViewDelegate.OnCloseButtonSelected();
        }
    }
}