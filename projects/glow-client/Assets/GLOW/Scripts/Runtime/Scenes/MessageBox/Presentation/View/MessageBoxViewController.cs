using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.MessageBox;
using GLOW.Core.Extensions;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.MessageBox.Presentation.Component;
using GLOW.Scenes.MessageBox.Presentation.ViewModel;
using UIKit;
using UnityEngine;
using Zenject;

namespace GLOW.Scenes.MessageBox.Presentation.View
{
    public class MessageBoxViewController :
        HomeBaseViewController<MessageBoxView>,
        IUICollectionViewDataSource,
        IUICollectionViewDelegate
    {
        [Inject] IMessageBoxViewDelegate ViewDelegate { get; }

        public Action OnCloseAction { get; set; }

        IReadOnlyList<IMessageBoxCellViewModel> _messageBoxCellViewModels;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ActualView.CollectionView.DataSource = this;
            ActualView.CollectionView.Delegate = this;

            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            ViewDelegate.OnViewDidUnload();
        }

        public void SetViewModel(MessageBoxViewModel viewModel, bool canBulkReceive, bool canBulkOpen)
        {
            _messageBoxCellViewModels = viewModel.MessageBoxCellViewModels;
            ActualView.Indicator.Hidden = true;
            ActualView.NoMessageObject.Hidden = !_messageBoxCellViewModels.IsEmpty();

            SetBulkButtonInteractable(canBulkReceive, canBulkOpen);
            ActualView.CollectionView.ReloadData();
        }
        
        public void UpdateViewModel(
            MessageBoxViewModel viewModel,
            UIIndexPath indexPath,
            bool canBulkReceive, 
            bool canBulkOpen)
        {
            _messageBoxCellViewModels = viewModel.MessageBoxCellViewModels;
            ActualView.NoMessageObject.Hidden = !_messageBoxCellViewModels.IsEmpty();
            
            var selectedCell = ActualView.CollectionView.CellForRow(indexPath) as MessageBoxListCell;
            if (selectedCell != null)
            {
                // 選択されたセルについてはバッジを非表示にする
                selectedCell.SetNoticeBadgeImage(MessageStatus.Opened);
                selectedCell.SetPlateImage(MessageStatus.Opened);
                selectedCell.SetIconImage(MessageStatus.Opened);
            }
        }

        public void SetBulkButtonInteractable(bool canBulkReceive, bool canBulkOpen)
        {
            ActualView.SetBulkButton(canBulkReceive, canBulkOpen);
        }

        public void PlayCellAppearanceAnimation()
        {
            ActualView.PlayCellAppearanceAnimation();
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _messageBoxCellViewModels?.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(
            UICollectionView collectionView,
            UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<MessageBoxListCell>();
            var viewModel = _messageBoxCellViewModels[indexPath.Row];
            if (viewModel == null) return cell;

            cell.Setup(viewModel);
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
            var viewModel = _messageBoxCellViewModels[indexPath.Row];
            string buttonKey = identifier.ToString();
            switch (buttonKey)
            {
                case "message":
                {
                    ViewDelegate.OnMessageSelected(viewModel, indexPath);
                }
                    break;
                default:
                {
                    Debug.Log("Default");
                }
                    break;
            }
        }

        [UIAction]
        void OnClose()
        {
            ViewDelegate.OnClose();
        }

        [UIAction]
        void OnBulkReceive()
        {
            var viewModels = _messageBoxCellViewModels
                .Where(viewModel => viewModel.MessageFormatType == MessageFormatType.HasReward)
                .Where(viewModel => viewModel.MessageStatus != MessageStatus.Received)
                .ToList();
            ViewDelegate.OnBulkReceive(viewModels);
        }

        [UIAction]
        void OnBulkOpened()
        {
            var newStatusViewModels =
                _messageBoxCellViewModels
                    .Where(viewModel => viewModel.MessageStatus == MessageStatus.New)
                    .ToList();
            ViewDelegate.OnBulkOpen(newStatusViewModels);
        }
    }
}
