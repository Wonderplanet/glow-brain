using System;
using System.Collections.Generic;
using System.Runtime.InteropServices;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Views.RotationBanner.HomeMain;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Presenters;
using GLOW.Scenes.Home.Presentation.Views.HomeMainKomaSetting;
using GLOW.Scenes.UnitList.Domain.Models;
using GLOW.Scenes.UnitSortAndFilterDialog.Domain.Models;
using Zenject;

namespace GLOW.Scenes.HomeMainKomaSettingFilter.Presentation
{
    public class HomeMainKomaSettingFilterPresenter : IHomeMainKomaSettingFilterViewDelegate
    {
        [Inject] HomeMainKomaSettingFilterViewController ViewController { get; }
        [Inject] HomeMainKomaSettingWireFrame WireFrame { get; }
        [Inject] HomeMainKomaSettingFilterUseCase UseCase { get; }
        [Inject] UpdateHomeMainKomaSettingFilterUseCase UpdateUseCase { get; }

        void IHomeMainKomaSettingFilterViewDelegate.OnViewDidLoad()
        {
            var viewModel = HomeMainKomaSettingFilterViewModelTranslator.Translate(UseCase.GetUseCaseModel());
            ViewController.InitializeView(viewModel);
        }


        void IHomeMainKomaSettingFilterViewDelegate.OnConfirm()
        {
            var targets = ViewController.ActualView.SeriesOnToggleMasterDataIds;
            UpdateUseCase.UpdateCacheRepository(targets);

            WireFrame.CloseFilterViewFromConfirmButton(
                ViewController,
                ViewController.OnConfirmAction);
        }

        void IHomeMainKomaSettingFilterViewDelegate.OnCancel()
        {
            WireFrame.CloseFilterViewFromCancelButton(ViewController, ViewController.OnCancelAction);
        }
    }
}
