using System;
using System.Collections.Generic;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.PartyFormation.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.PartyFormation.Presentation.Views
{
    public class PartyFormationUnitListComponent : MonoBehaviour,
        IUICollectionViewDelegate,
        IUICollectionViewDataSource
    {
        [SerializeField] UICollectionView _collectionView;
        [SerializeField] ChildScaler _childScaler;

        IReadOnlyList<PartyFormationUnitListCellViewModel> _viewModels;

        public IPartyFormationUnitSelectDelegate SelectDelegate { get; set; }

        void Awake()
        {
            _collectionView.DataSource = this;
            _collectionView.Delegate = this;
        }

        public void Setup(IReadOnlyList<PartyFormationUnitListCellViewModel> unitListViewModels)
        {
            _viewModels = unitListViewModels;
            _collectionView.ReloadData();
        }

        public void PlayCellAppearanceAnimation()
        {
            _childScaler.Play();
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _viewModels?.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var viewModel = _viewModels[indexPath.Row];
            var cell = collectionView.DequeueReusableCell<PartyFormationUnitListCell>(item => item.UserUnitId == viewModel.UserUnitId);
            cell.Setup(viewModel);
            cell.LongPress.PointerDown.RemoveAllListeners();

            cell.LongPress.PointerDown.AddListener(() =>
            {
                SelectDelegate?.ShowUnitEnhanceView(viewModel.UserUnitId);
            });
            return cell;
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var viewModel = _viewModels[indexPath.Row];
            if (viewModel.IsAssigned.Value)
            {
                SelectDelegate?.SelectUnassignUnit(viewModel.UserUnitId);
            }
            else if(viewModel.IsSelectable && viewModel.IsAchievedSpecialRule)
            {
                SelectDelegate?.SelectAssignUnit(viewModel.UserUnitId, viewModel.IsAchievedSpecialRule);
            }
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier) { }
    }
}
