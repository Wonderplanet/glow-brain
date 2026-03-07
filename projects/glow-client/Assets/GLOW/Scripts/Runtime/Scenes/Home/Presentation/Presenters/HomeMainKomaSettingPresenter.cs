using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Views.RotationBanner.HomeMain;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Modules.Tutorial.Domain.Context;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Modules.Tutorial.Domain.UseCases;
using GLOW.Modules.TutorialTipDialog.Domain.Definitions;
using GLOW.Modules.TutorialTipDialog.Presentation.View;
using GLOW.Scenes.AdventBattle.Presentation.View;
using GLOW.Scenes.Home.Domain.AssetLoader;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Domain.ValueObjects;
using GLOW.Scenes.Home.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Views.HomeMainKomaSetting;
using GLOW.Scenes.HomeMainKomaSettingUnitSelect.Presentation;
using UnityEngine;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Presenters
{
    public class HomeMainKomaSettingPresenter :
        IHomeMainKomaSettingViewDelegate
    {
        [Inject] HomeMainKomaSettingViewController ViewController { get; }
        [Inject] IHomeViewNavigation ViewNavigation { get; }
        [Inject] HomeMainKomaSettingUseCase UseCase { get; }
        [Inject] HomeMainKomaSettingApplyUseCase ApplyUseCase { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] SaveCurrentMstKomaPatternUseCase SaveCurrentMstKomaPatternUseCase { get; }
        [Inject] HomeMainKomaSettingWireFrame WireFrame { get; }
        [Inject] IHomeMainKomaPatternContainer HomeMainKomaPatternContainer { get; }
        [Inject] IHomeMainKomaPatternLoader HomeMainKomaPatternLoader { get; }
        [Inject] TutorialTipDialogViewWireFrame TutorialTipDialogViewWireFrame { get; }
        [Inject] CompleteFreePartTutorialUseCase CompleteFreePartTutorialUseCase { get; }
        [Inject] CheckFreePartTutorialCompletedUseCase CheckFreePartTutorialCompletedUseCase { get; }

        bool _isShowingTutorialDialog;

        void IHomeMainKomaSettingViewDelegate.OnViewDidLoad()
        {
            WireFrame.SetViewController(ViewController);

            DoAsync.Invoke(ViewController.ActualView.destroyCancellationToken, async cancellationToken =>
            {
                var model = UseCase.LoadAndGetHomeMainKomaSettingUseCaseModel();
                var viewModel = HomeMainKomaSettingViewModelTranslator.Translate(model);

                await SetUpHomeMainKomas(viewModel.HomeMainKomaPatternViewModels, cancellationToken);
                ViewController.InitializePageView(viewModel);

                // 初回遷移時チュートリアル
                await ShouldShowFirstTransitTutorial(cancellationToken);
            });

        }

        async UniTask SetUpHomeMainKomas(
            IReadOnlyList<HomeMainKomaPatternViewModel> viewModels,
            CancellationToken cancellationToken)
        {
            foreach (var viewModel in viewModels)
            {
                if(HomeMainKomaPatternContainer.Exists(viewModel.AssetPath)) continue;

                await HomeMainKomaPatternLoader.Load(
                    cancellationToken,
                    viewModel.AssetPath);
            }
        }

        void IHomeMainKomaSettingViewDelegate.OnUnitEditButtonTapped(
            MasterDataId targetMstHomeMainKomaPatternId,
            HomeMainKomaUnitAssetSetPlaceIndex targetUnitAssetSetPlaceIndex,
            MasterDataId currentSettingMstUnitId,
            IReadOnlyList<MasterDataId> otherSettingMstUnitIds,
            Action<HomeMainKomaPatternViewModel> onUpdate)
        {
            var argument = new HomeMainKomaSettingUnitSelectViewController.Argument(
                currentSettingMstUnitId,
                otherSettingMstUnitIds);

            Action<MasterDataId> onCloseAction = (selectedMstUnitId) =>
            {
                // 順番依存1: Repository更新
                ApplyUseCase.SaveUnit(
                    targetMstHomeMainKomaPatternId,
                    targetUnitAssetSetPlaceIndex,
                    selectedMstUnitId);

                // 順番依存2: Get(Repositoryから再取得)してViewModel更新
                var model = UseCase.LoadAndGetHomeMainKomaSettingUseCaseModel();
                var viewModel = HomeMainKomaSettingViewModelTranslator.Translate(model);

                var targetPatternViewModel = viewModel.HomeMainKomaPatternViewModels
                    .First(vm => vm.MstHomeMainKomaPatternId == targetMstHomeMainKomaPatternId);
                onUpdate?.Invoke(targetPatternViewModel);
            };

            WireFrame.ShowUnitSelectView(argument, onCloseAction);
        }

        void IHomeMainKomaSettingViewDelegate.OnHelpButtonTapped()
        {
            var functionName = HelpDialogIdDefinitions.HomeCreate;
            TutorialTipDialogViewWireFrame.ShowTutorialTipDialogs(ViewController, functionName);
        }


        void IHomeMainKomaSettingViewDelegate.OnClose(MasterDataId currentMstKomaPatternId)
        {
            WireFrame.UnsetViewController();
            SaveCurrentMstKomaPatternUseCase.Execute(currentMstKomaPatternId);
            ViewNavigation.TryPop();
        }

        void IHomeMainKomaSettingViewDelegate.OnEscape(MasterDataId currentMstKomaPatternId)
        {
            // チュートリアルダイアログ表示中は画面から出られなくする
            if (_isShowingTutorialDialog)
            {
                CommonToastWireFrame.ShowInvalidOperationMessage();
                return;
            }

            WireFrame.UnsetViewController();
            SaveCurrentMstKomaPatternUseCase.Execute(currentMstKomaPatternId);
            ViewNavigation.TryPop();

        }

        async UniTask ShouldShowFirstTransitTutorial(CancellationToken cancellationToken)
        {
            var tutorialFunctionName = TutorialFreePartIdDefinitions.TransitHomeCreate;
            if (CheckFreePartTutorialCompletedUseCase.CheckFreePartTutorialCompleted(tutorialFunctionName))
            {
                // チュートリアル完了済みの場合は何もしない
                return;
            }

            // チュートリアルダイアログ表示中はバックキーを無効にする
            _isShowingTutorialDialog = true;

            // 画面表示のため1f待つ
            await UniTask.DelayFrame(1, cancellationToken: cancellationToken);

            var completionSource = new UniTaskCompletionSource();

            var functionName = HelpDialogIdDefinitions.HomeCreate;
            TutorialTipDialogViewWireFrame.ShowTutorialTipDialogs(
                ViewController,
                functionName,
                () => completionSource.TrySetResult());
            // チュートリアルダイアログが閉じられるまで待機
            await completionSource.Task.AttachExternalCancellation(cancellationToken);

            // チュートリアル完了の更新処理
            await CompleteFreePartTutorialUseCase.CompleteFreePartTutorial(
                cancellationToken,
                TutorialFreePartIdDefinitions.TransitHomeCreate);

            _isShowingTutorialDialog = false;
        }
    }
}
