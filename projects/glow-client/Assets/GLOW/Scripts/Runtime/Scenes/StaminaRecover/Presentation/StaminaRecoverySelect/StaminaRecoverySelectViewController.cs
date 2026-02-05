using System;
using System.Collections;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.StaminaRecover.Domain.ValueObject;
using GLOW.Scenes.StaminaRecover.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.StaminaRecover.Presentation.StaminaRecoverySelect
{
    public class StaminaRecoverySelectViewController :
        UIViewController<StaminaRecoverySelectView>,
        IUICollectionViewDelegate,
        IUICollectionViewDataSource,
        IEscapeResponder
    {
        public record Argument(StaminaShortageFlag IsStaminaShortage);
        [Inject] IStaminaRecoverySelectViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        IReadOnlyList<StaminaListCellViewModel> _cellViewModels;

        public Action OnDismissAction { get; set; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewWillAppear(bool animated)
        {
            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }

        public void InitializeView()
        {
            ActualView.Initialize(this, this);
        }

        public void SetUpView(StaminaRecoverySelectViewModel viewModel)
        {
            ActualView.SetUpDialogText(viewModel.IsStaminaShortage);
            _cellViewModels = viewModel.StaminaRecoveryItems;
            ActualView.ReloadData();
        }

        public IEnumerator AutoUpdateCell(
            StaminaListCell cell,
            StaminaRecoveryAvailableStatus availableStatus,
            RemainingTimeSpan remainingTime,
            StaminaRecoveryAvailability availability)
        {
            if (availability != StaminaRecoveryAvailability.WaitingForReset)
            {
                yield break;
            }

            if (remainingTime.IsEmpty())
            {
                yield break;
            }

            var currentRemainingTime = remainingTime;
            var currentAvailability = availability;
            var waitTime = remainingTime.Value.Milliseconds * 0.001f;

            while(!currentRemainingTime.IsMinus())
            {
                cell.SetUpButton(availableStatus, currentRemainingTime, currentAvailability);
                yield return new WaitForSeconds(waitTime);
                currentRemainingTime = new RemainingTimeSpan(currentRemainingTime.Value.Subtract(TimeSpan.FromSeconds(1)));
                waitTime = 1;
            }

            cell.SetUpButton(availableStatus,
                RemainingTimeSpan.Empty,
                StaminaRecoveryAvailability.Available);
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            // 不要
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier)
        {
            if(identifier == "useButtonTapped")
            {
                var viewModel = _cellViewModels[indexPath.Row];
                ViewDelegate.OnUseButtonTapped(viewModel.MstItemId, viewModel.AvailableStatus, viewModel.StaminaEffectValue);
            }
            else if (identifier == "adSkipButtonTapped")
            {
                var viewModel = _cellViewModels[indexPath.Row];
                ViewDelegate.OnUseButtonTapped(viewModel.MstItemId, viewModel.AvailableStatus, viewModel.StaminaEffectValue);
            }
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _cellViewModels?.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<StaminaListCell>();
            var viewModel = _cellViewModels[indexPath.Row];

            cell.Setup(viewModel);
            ViewDelegate.OnUpdateStaminaResetTime(
                cell,
                viewModel.AvailableStatus,
                viewModel.RemainingTime,
                viewModel.Availability);
            return cell;
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            ViewDelegate.OnClose();
            return true;
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            ViewDelegate.OnClose();
        }
    }
}
