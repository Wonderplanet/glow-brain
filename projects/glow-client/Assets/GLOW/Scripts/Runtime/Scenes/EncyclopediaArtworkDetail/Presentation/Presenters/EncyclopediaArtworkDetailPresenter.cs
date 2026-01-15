using System;
using System.Linq;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Translators;
using GLOW.Modules.CommonReceiveView.Presentation.Presenters;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.ArtworkExpandDialog.Presentation.ValueObject;
using GLOW.Scenes.ArtworkExpandDialog.Presentation.Views;
using GLOW.Scenes.ArtworkFragment.Presentation.Translator;
using GLOW.Scenes.EncyclopediaArtworkDetail.Domain.Models;
using GLOW.Scenes.EncyclopediaArtworkDetail.Domain.UseCases;
using GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.ViewModels;
using GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.Views;
using GLOW.Scenes.EncyclopediaSeries.Domain.UseCases;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.OutpostArtworkChangeConfirm.Presentation.Views;
using GLOW.Scenes.OutpostEnhance.Domain.UseCases;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.EncyclopediaArtworkDetail.Presentation.Presenters
{
    /// <summary>
    /// 91_図鑑
    /// 　91-4_作品別原画表示
    /// 　　91-4-1_原画画面
    /// </summary>
    public class EncyclopediaArtworkDetailPresenter : IEncyclopediaArtworkDetailViewDelegate
    {
        [Inject] EncyclopediaArtworkDetailViewController ViewController { get; }
        [Inject] EncyclopediaArtworkDetailViewController.Argument Argument { get; }
        [Inject] GetEncyclopediaArtworkDetailUseCase GetEncyclopediaArtworkDetailUseCase { get; }
        [Inject] SetArtworkFragmentDropQuestUseCase SetArtworkFragmentDropQuestUseCase { get; }
        [Inject] InitializeEncyclopediaArtworkCacheUseCase InitializeEncyclopediaArtworkCacheUseCase { get; }
        [Inject] ApplyUpdatedOutpostArtworkUseCase ApplyUpdatedOutpostArtworkUseCase { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] ReceiveEncyclopediaFirstCollectionRewardUseCase ReceiveEncyclopediaFirstCollectionRewardUseCase { get; }
        [Inject] CommonReceiveWireFrame CommonReceiveWireFrame { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] IHomeViewControl HomeViewControl { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }

        EncyclopediaArtworkDetailViewModel _selectedArtworkViewModel;

        void IEncyclopediaArtworkDetailViewDelegate.OnViewDidLoad()
        {
            InitializeEncyclopediaArtworkCacheUseCase.InitializeOutpostArtworkCache(Argument.MstArtworkIds);
            ViewController.InitializePageView(Argument.MstArtworkIds, Argument.SelectedMstArtworkId);
            UpdateView(Argument.SelectedMstArtworkId);
        }

        void UpdateView(MasterDataId mstArtworkId)
        {
            var model = GetEncyclopediaArtworkDetailUseCase.GetArtworkDetail(mstArtworkId);
            _selectedArtworkViewModel = TranslateArtworkDetail(model);
            bool isHiddenArrowButton = Argument.MstArtworkIds.Count <= 1;
            ViewController.Setup(_selectedArtworkViewModel, isHiddenArrowButton);

            ReceiveFirstCollectionReward(mstArtworkId);
        }

        void ReceiveFirstCollectionReward(MasterDataId mstArtworkId)
        {
            DoAsync.Invoke(ViewController.View, ScreenInteractionControl, async cancellationToken =>
            {
                var result = await ReceiveEncyclopediaFirstCollectionRewardUseCase.ReceiveReward(
                    cancellationToken,
                    mstArtworkId,
                    EncyclopediaType.Artwork
                    );

                if(result.IsEmpty()) return;

                var rewards = result
                    .Select(r => CommonReceiveResourceViewModelTranslator.TranslateToCommonReceiveViewModel(r))
                    .ToList();
                CommonReceiveWireFrame.Show(rewards);
                HomeHeaderDelegate.UpdateStatus();
            });
        }

        void IEncyclopediaArtworkDetailViewDelegate.OnSwitchOutpostArtworkButtonTapped()
        {
            var argument = new OutpostArtworkChangeConfirmViewController.Argument(_selectedArtworkViewModel.MstArtworkId);
            var viewController = ViewFactory.Create<OutpostArtworkChangeConfirmViewController, OutpostArtworkChangeConfirmViewController.Argument>(argument);
            ViewController.PresentModally(viewController);

            DoAsync.Invoke(ViewController.View, async cancellationToken =>
            {
                await UniTask.WaitUntil(viewController.View.IsDestroyed, cancellationToken: cancellationToken);
                UpdateView(_selectedArtworkViewModel.MstArtworkId);
            });
        }

        void IEncyclopediaArtworkDetailViewDelegate.OnSelectArtworkExpand(MasterDataId mstArtworkId)
        {
            var argument = new ArtworkExpandDialogViewController.Argument(
                mstArtworkId, ArtworkDetailDisplayType.Normal);
            var viewController = ViewFactory.Create<ArtworkExpandDialogViewController, ArtworkExpandDialogViewController.Argument>(argument);
            ViewController.PresentModally(viewController);
        }

        void IEncyclopediaArtworkDetailViewDelegate.OnSwitchArtwork(MasterDataId mstArtworkId)
        {
            UpdateView(mstArtworkId);
        }

        void IEncyclopediaArtworkDetailViewDelegate.OnSelectFragmentDropQuest(EncyclopediaArtworkFragmentListCellViewModel viewModel)
        {
            var fragment = _selectedArtworkViewModel.ArtworkFragmentList
                .Find(fragment => fragment.MstArtworkFragmentId == viewModel.MstArtworkFragmentId);
            if (fragment.StatusFlags.IsOutOfTermQuest)
            {
                CommonToastWireFrame.ShowScreenCenterToast("開催していないクエストです\n次回開催をお待ちください");
                return;
            }

            if (!fragment.StatusFlags.IsEnableChallenge)
            {
                CommonToastWireFrame.ShowScreenCenterToast("ステージに挑戦できません");
                return;
            }

            if (QuestType.Normal == viewModel.DropQuestType)
            {
                SetArtworkFragmentDropQuestUseCase.SetSelectedStage(viewModel.MstArtworkFragmentId);
                ApplyUpdatedOutpostArtworkUseCase.Apply().Forget(); // 画面遷移と同時に走らせるのでForgetで実行させる
                HomeViewNavigation.TryPopToRoot(completion: () => HomeViewControl.OnQuestSelected());
                return;
            }

            if (QuestType.Event == viewModel.DropQuestType)
            {
                SetArtworkFragmentDropQuestUseCase.SetSelectedStage(viewModel.MstArtworkFragmentId);
                ApplyUpdatedOutpostArtworkUseCase.Apply().Forget(); // 画面遷移と同時に走らせるのでForgetで実行させる
                HomeViewControl.OnContentTopSelected();
                return;
            }
        }

        void IEncyclopediaArtworkDetailViewDelegate.OnBackButtonTapped()
        {
            ViewController.OnClosed?.Invoke();
            DismissView();
        }

        void DismissView()
        {
            DoAsync.Invoke(ViewController.View, ScreenInteractionControl, async cancellationToken =>
            {
                await ApplyUpdatedOutpostArtworkUseCase.Apply();
                HomeViewNavigation.TryPop();
            });
        }

        EncyclopediaArtworkDetailViewModel TranslateArtworkDetail(EncyclopediaArtworkDetailModel model)
        {
            var fragmentListCells = model.ArtworkFragmentList
                .Select(TranslateFragmentListCell)
                .ToList();

            return new EncyclopediaArtworkDetailViewModel(
                model.MstArtworkId,
                model.Name,
                model.EffectDescription,
                fragmentListCells,
                model.ArtworkUnlockFlag,
                model.IsEnableSwitchOutpostArtwork
            );
        }

        EncyclopediaArtworkFragmentListCellViewModel TranslateFragmentListCell(EncyclopediaArtworkFragmentListCellModel model)
        {
            return new EncyclopediaArtworkFragmentListCellViewModel(
                model.MstArtworkFragmentId,
                model.QuestType,
                model.AssetPath,
                model.Num,
                model.FragmentName,
                model.FragmentRarity,
                model.DropConditionText,
                model.StatusFlags
            );
        }
    }
}
