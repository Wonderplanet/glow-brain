using System;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.Home.Domain.AssetLoader;
using GLOW.Scenes.Home.Domain.ValueObjects;
using GLOW.Scenes.Home.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Views.HomeMainKomaSetting
{
    public class HomeMainKomaSettingItemViewController : UIViewController<HomeMainKomaSettingItemView>
    {
        [Inject] IHomeMainKomaPatternContainer HomeMainKomaPatternContainer { get; }
        // [Inject] IHomeMainKomaSettingItemViewDelegate ViewDelegate { get; }

        IHomeMainKomaSettingViewControl _homeMainKomaSettingViewControl;
        HomeMainKomaPatternViewModel _viewModel;

        public MasterDataId MstHomeMainKomaPatternId => _viewModel.MstHomeMainKomaPatternId;

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            // UIPageViewでAddChildされたのち、そのライフサイクルを利用して上の画面を更新する
            _homeMainKomaSettingViewControl?.SetKomaPatternName(_viewModel.Name);
        }

        public void InitializeView(
            HomeMainKomaPatternViewModel viewModel,
            IHomeMainKomaSettingViewControl homeMainKomaSettingViewControl)
        {
            _viewModel = viewModel;
            _homeMainKomaSettingViewControl = homeMainKomaSettingViewControl;
            var pattern = HomeMainKomaPatternContainer.Get(viewModel.AssetPath);
            ActualView.InitializeView(pattern, OnUnitEditButtonTapped);
        }

        public void SetUpView(HomeMainKomaPatternViewModel viewModel)
        {
            // 配置ユニット更新
            ActualView.SetUpView(viewModel.HomeMainKomaUnitViewModels);
        }

        void OnUnitEditButtonTapped(HomeMainKomaUnitAssetSetPlaceIndex placeIndex)
        {
            var unitViewModel = _viewModel.HomeMainKomaUnitViewModels
                .FirstOrDefault(vm => vm.PlaceIndex == placeIndex,
                    HomeMainKomaUnitViewModel.CreateEmpty(placeIndex));

            var otherSettingMstUnitIds = _viewModel.HomeMainKomaUnitViewModels
                .Where(vm => vm.PlaceIndex != placeIndex)
                .Select(vm => vm.MstUnitId)
                .ToList();

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);

            _homeMainKomaSettingViewControl.OnUnitEditButtonTapped(
                _viewModel.MstHomeMainKomaPatternId,
                placeIndex,
                unitViewModel.MstUnitId,
                otherSettingMstUnitIds,
                SetUpFromUnitSelect);//ユニット変更画面からユニット変更されたときに呼び出され、画面更新される
        }

        void SetUpFromUnitSelect(HomeMainKomaPatternViewModel viewModel)
        {
            _viewModel = viewModel;
            ActualView.SetUpView(viewModel.HomeMainKomaUnitViewModels);
        }
    }
}
