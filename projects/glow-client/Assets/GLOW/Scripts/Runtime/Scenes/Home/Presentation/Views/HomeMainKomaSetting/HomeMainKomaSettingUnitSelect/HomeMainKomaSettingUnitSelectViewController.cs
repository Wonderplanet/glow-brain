using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.Home.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.HomeMainKomaSettingUnitSelect.Presentation
{
    public class HomeMainKomaSettingUnitSelectViewController :
        UIViewController<HomeMainKomaSettingUnitSelectView>,
        IUICollectionViewDataSource,
        IUICollectionViewDelegate,
        IEscapeResponder
    {
        public record Argument(
            MasterDataId CurrentSettingMstUnitId,
            IReadOnlyList<MasterDataId> OtherSettingMstUnitIds);

        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] IHomeMainKomaSettingUnitSelectViewDelegate ViewDelegate { get; }
        [Inject] Argument Args { get; }

        // 選択しているmstUnitIdを返す
        public Action<MasterDataId> OnCloseAction { get; set; }

        HomeMainKomaSettingUnitSelectViewModel _viewModel;
        MasterDataId _currentSelectingMstUnitId;
        MasterDataId CurrentSelectingMstUnitId => _currentSelectingMstUnitId;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            // 初期選択は引数のCurrentSettingMstUnitId
            _currentSelectingMstUnitId = Args.CurrentSettingMstUnitId;
            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }

        public override void ViewDidAppear()
        {
            base.ViewDidAppear();
            ActualView.PlayCellAppearanceAnimation();
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            EscapeResponderRegistry.Unregister(this);
        }

        public void InitializeView()
        {
            ActualView.InitializeView(this, this);
        }

        public void SetUpView(HomeMainKomaSettingUnitSelectViewModel viewModel)
        {
            _viewModel = viewModel;
            ActualView.ReloadData();
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _viewModel?.Units.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var viewModel = _viewModel.Units[indexPath.Row];
            var cell = collectionView.DequeueReusableCell<HomeMainKomaSettingUnitSelectCell>(item=> item.MstUnitId == viewModel.MstUnitId);

            cell.Setup(viewModel);

            return cell;
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var model = _viewModel.Units[indexPath.Row];
            SetCurrentSelectingMstUnitId(model.MstUnitId);
            ViewDelegate.UpdateSelectingUnit(CurrentSelectingMstUnitId);
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier)
        {
            //no use.
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden)
            {
                return false;
            }

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_003);
            OnClose();
            return true;
        }

        // 副作用
        void SetCurrentSelectingMstUnitId(MasterDataId mstUnitId)
        {
            if (Args.OtherSettingMstUnitIds.Contains(mstUnitId))
            {
                // 他で設定しているユニットIDに含まれていれば、何もしない
                return;
            }

            // もし同じユニットだったら外す(Emptyにする)
            if (CurrentSelectingMstUnitId == mstUnitId)
            {
                _currentSelectingMstUnitId = MasterDataId.Empty;
            }
            else
            {
                _currentSelectingMstUnitId = mstUnitId;
            }
        }

        void OnClose()
        {
            OnCloseAction?.Invoke(CurrentSelectingMstUnitId);
            Dismiss();
        }


        [UIAction]
        void OnFilterButtonTapped()
        {
            ViewDelegate.OnFilterButtonTapped(CurrentSelectingMstUnitId);
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            OnClose();
        }

    }
}
