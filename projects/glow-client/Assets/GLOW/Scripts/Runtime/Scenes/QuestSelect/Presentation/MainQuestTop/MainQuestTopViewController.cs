using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Presenters;
using GLOW.Scenes.Home.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.QuestSelect.Presentation;
using GLOW.Scenes.QuestSelect.Presentation.QuestDifficultySelect;
using UIKit;
using UnityEngine;
using Wonderplanet.UIHaptics.Presentation;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.MainQuestTop.Presentation
{
    public interface IMainQuestTopViewFactory
    {
        void VisibleMainQuestTop(bool isVisible);
        void ShowQuestSelectView();
    }
    public class MainQuestTopViewController : UIViewController<MainQuestTopView>, IMainQuestTopViewFactory
    {
        [Inject] IMainQuestTopViewDelegate ViewDelegate { get; }
        [Inject] IHomeViewDelegate HomeViewDelegate { get; }
        [Inject] IStageSelectViewDelegate StageSelectViewDelegate { get; }
        [Inject] IHapticsPresenter HapticsPresenter { get; }
        [Inject] IHomeMainStageSelectViewDelegate HomeMainStageSelectViewDelegate { get; }

        HomeMainStageSelectControl _stageSelectControl;
        HomeMainStageSelectControl StageSelectControl => _stageSelectControl;

        MainQuestDifficultyControl _difficultyControl;

        HomeMainStageViewModel _selectedStageModel;
        QuestSelectContentViewModel _selectingContentViewModel;

        public override void ViewDidLoad()
        {
            ActualView.InitializeView();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);

            //homeMainViewが非表示、かつ一度アプリが非アクティブになると触覚FBが止まるので再開させる
            HapticsPresenter.SyncRestartEngine();

            ViewDelegate.OnViewWillAppear();
        }

        public async UniTask InitQuestViewModel(
            HomeMainQuestViewModel viewModel,
            QuestSelectViewModel questSelectViewModel,
            CancellationToken cancellationToken)
        {
            InitStageSelectCollectionView(viewModel);
            InitializeMainQuestDifficultyControl(viewModel.MstGroupQuestId, questSelectViewModel);
            RefreshQuest(viewModel);

            // チュートリアル後のステージ未挑戦時の吹き出し表示
            SetActiveStageTryText(viewModel.IsDisplayTryStageText);

            //ステージ開放演出
            //順番依存：CarouselViewのbuild(InitStageSelectCollectionView().CarouselView.DataSource)が呼ばれた後に処理
            await TryShowStageReleaseAnimation(viewModel, cancellationToken);

            //クエスト開放演出
            if (viewModel.ShowQuestReleaseAnimation.ShouldShow)
            {
                await ViewDelegate.ShowQuestReleaseView(
                    viewModel.ShowQuestReleaseAnimation,
                    viewModel.IsInAppReviewDisplay,
                    cancellationToken);
                ActualView.CloseQuestReleaseAnimation = true;
            }

            await ViewDelegate.DoIfTutorial();
        }
        void RefreshQuest(HomeMainQuestViewModel viewModel)
        {
            ActualView.HomeMainQuestView.QuestImage.AssetPath = viewModel.QuestImageAssetPath.Value;
            ActualView.SetQuestBackGroundImage(viewModel.QuestImageAssetPath);

            ActualView.HomeMainQuestView.QuestName.SetText(viewModel.QuestName.Value);
            ActualView.HomeMainQuestView.DifficultyLabelComponent.SetDifficulty(viewModel.CurrentDifficulty);
            ActualView.HomeMainQuestView.SetupQuestTimeLimit(viewModel.QuestLimitTime);
        }



        void SetActiveStageTryText(DisplayTryStageTextFlag isDisplayTryStageText)
        {
            ActualView.OverlappingUIComponent.IsTryStageTextVisible = isDisplayTryStageText;
        }

        # region 解放演出
        async UniTask TryShowStageReleaseAnimation(HomeMainQuestViewModel viewModel, CancellationToken cancellationToken)
        {
            var shouldShowStageReleaseAnimation = !viewModel.ShowQuestReleaseAnimation.ShouldShow &&
                                                  viewModel.ShowStageReleaseAnimation.ShouldShow;
            ActualView.ReleaseAnimation.gameObject.SetActive(shouldShowStageReleaseAnimation);

            if (!shouldShowStageReleaseAnimation) return;

            var cell = ActualView.HomeMainQuestView.CarouselView.SelectedCell as HomeMainStageSelectCell;
            if (cell == null) throw new Exception("Trying show stage release animation but cell is null.");

            ActualView.ReleaseAnimation.OnStageReleaseEventAction = () => cell.ReleasedGameObject.SetActive(true);
            cell.ReleasedGameObject.SetActive(false);
            await StartStageReleaseAnimation(cancellationToken);
        }

        async UniTask StartStageReleaseAnimation(CancellationToken cancellationToken)
        {
            HomeViewDelegate.ShowTapBlock(true, ActualView.InvertMaskRect, 0f);
            var animationTime = 2.2f;
            var grayOutTransitionStartTime = 1.2f;
            var startDelay = 0.5f;
            await UniTask.Delay(TimeSpan.FromSeconds(startDelay), cancellationToken:cancellationToken);
            ActualView.ReleaseAnimation.ShowAnimation();

            await UniTask.Delay(TimeSpan.FromSeconds(grayOutTransitionStartTime), cancellationToken:cancellationToken);

            var duration = animationTime - grayOutTransitionStartTime;
            HomeViewDelegate.HideTapBlock(true, duration);

            var endDelay = animationTime - startDelay - grayOutTransitionStartTime;
            await UniTask.Delay(TimeSpan.FromSeconds(endDelay), cancellationToken:cancellationToken);
            ActualView.ReleaseAnimation.gameObject.SetActive(false);
        }
        # endregion

        void InitStageSelectCollectionView(HomeMainQuestViewModel viewModel)
        {
            var initSelectModel = viewModel.GetInitSelectViewModel();
            _stageSelectControl = new HomeMainStageSelectControl(
                ActualView.HomeMainQuestView,
                ActualView.HomeMainQuestView.CarouselView,
                viewModel.Stages,
                initSelectModel,
                OnSelect,
                HomeMainStageSelectViewDelegate
            );

            ActualView.InitializeCarousel(StageSelectControl, StageSelectControl, HapticsPresenter);

            ActualView.SetUpView(viewModel.QuestName, initSelectModel.CampaignViewModels);


            var shouldShowButton = GetShowButtonStatus(initSelectModel, viewModel.Stages);
            OnSelect(
                initSelectModel,shouldShowButton.shouldShowLeftButton,
                shouldShowButton.shouldShowRightButton);
        }

        void InitializeMainQuestDifficultyControl(
            MasterDataId mstGroupQuestId,
            QuestSelectViewModel questSelectViewModel)
        {
            _difficultyControl = new MainQuestDifficultyControl(
                mstGroupQuestId,
                ActualView.DifficultyButtonListComponent,
                ActualView.NormalCampaignBalloonSwitcher,
                ActualView.HardCampaignBalloonSwitcher,
                ActualView.ExtraCampaignBalloonSwitcher
                );

            _selectingContentViewModel = questSelectViewModel.Items.ElementAtOrDefault(questSelectViewModel.CurrentIndex.Value)
                                         ?? QuestSelectContentViewModel.Empty;

            _difficultyControl.InitializeDifficultyButtons(
                _selectingContentViewModel.CurrentDifficulty,
                _selectingContentViewModel.QuestDifficultyItemViewModels,
                _selectingContentViewModel.NormalCampaignViewModels,
                _selectingContentViewModel.HardCampaignViewModels,
                _selectingContentViewModel.ExtraCampaignViewModels);
        }

        // カルーセルでステージが変更されたとき処理が流れる
        void OnSelect(HomeMainStageViewModel selected, bool shouldShowLeftButton, bool shouldShowRightButton)
        {
            _selectedStageModel = selected;

            ActualView.SetUpStageSelect(
                selected.RecommendedLevel,
                selected.PlayableFlag,
                shouldShowLeftButton,
                shouldShowRightButton,
                selected.StageConsumeStamina,
                selected.DailyPlayableCount,
                selected.DailyClearCount,
                selected.ExistsSpecialRule,
                selected.StaminaBoostBalloonType,
                selected.SpeedAttackViewModel,
                selected.CampaignViewModels
                );
        }

        public void SetCurrentPartyName(PartyName partyName)
        {
            ActualView.SetCurrentPartyName(partyName);
        }

        public void SetQuestViewModel(HomeMainQuestViewModel viewModel)
        {
            RefreshQuest(viewModel);
            RefreshStageCells(viewModel.Stages, viewModel.InitialSelectStageMstStageId);


            void RefreshStageCells(IReadOnlyList<HomeMainStageViewModel> stages, MasterDataId selectedStageId)
            {
                var initSelectModel = stages.First(s => s.MstStageId == selectedStageId);

                _stageSelectControl.SetData(stages, initSelectModel);
                ActualView.HomeMainQuestView.CarouselView.ReloadData();

                var shouldShowButton = GetShowButtonStatus(initSelectModel, stages);
                OnSelect(
                    initSelectModel,
                    shouldShowButton.shouldShowLeftButton,
                    shouldShowButton.shouldShowRightButton);
            }
        }


        // 難易度ボタン押したら発火
        public void SelectDifficulty(Difficulty selectedDifficulty)
        {
            DifficultyButtonScaleAnimation(selectedDifficulty);

            // var cell = ActualView.CarouselView.SelectedCell as QuestSelectCell;
            ActualView.HomeMainQuestView.DifficultyLabelComponent.SetDifficulty(selectedDifficulty);

            ApplySelectStageDifficulty(selectedDifficulty);


            void DifficultyButtonScaleAnimation(Difficulty selectDifficulty)
            {
                ActualView.DifficultyButtonListComponent.NormalButton.PlayScaleAnimation(selectDifficulty);
                ActualView.DifficultyButtonListComponent.HardButton.PlayScaleAnimation(selectDifficulty);
                ActualView.DifficultyButtonListComponent.ExtraButton.PlayScaleAnimation(selectDifficulty);
            }

            void ApplySelectStageDifficulty(Difficulty selectDifficulty)
            {
                // Itemsの中で、初期状態から難易度が変更されたものをここで更新する
                var viewModel = _selectingContentViewModel.CopyWithUpdatedCurrentDifficulty(selectDifficulty);
                _selectingContentViewModel = viewModel;
            }
        }

        (bool shouldShowLeftButton, bool shouldShowRightButton) GetShowButtonStatus(
            HomeMainStageViewModel selected,
            IReadOnlyList<HomeMainStageViewModel> stages)
        {
            var index = stages.IndexOf(selected);
            return (0 < index, index < stages.Count - 1);
        }


        void IMainQuestTopViewFactory.VisibleMainQuestTop(bool isVisible)
        {
            ActualView.Hidden = !isVisible;
        }

        void IMainQuestTopViewFactory.ShowQuestSelectView()
        {
            ViewDelegate.OnQuestSelected();
        }

        [UIAction]
        void OnQuestSelectButton()
        {
            ViewDelegate.OnQuestSelected();
        }
        [UIAction]
        void OnCloseButton()
        {
            ViewDelegate.OnClose();
        }

        #region 難易度ボタン
        [UIAction]
        void OnNormalDifficultyButton()
        {
            ViewDelegate.OnDifficultySelectedAndUpdateRepository(_difficultyControl.MstGroupQuestId, Difficulty.Normal);
        }

        [UIAction]
        void OnHardDifficultyButton()
        {
            ViewDelegate.OnDifficultySelectedAndUpdateRepository(_difficultyControl.MstGroupQuestId, Difficulty.Hard);
        }

        [UIAction]
        void OnExtraDifficultyButton()
        {
            ViewDelegate.OnDifficultySelectedAndUpdateRepository(_difficultyControl.MstGroupQuestId, Difficulty.Extra);
        }
        #endregion

        #region ステージ左右ボタン
        [UIAction]
        void OnRightStageCell()
        {
            if (StageSelectControl.OnMoveButtonIfNeed(CarouselDirection.Right))
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
                StageSelectControl?.MoveRight();
            }
        }
        [UIAction]
        void OnLeftStageCell()
        {
            if (StageSelectControl.OnMoveButtonIfNeed(CarouselDirection.Left))
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
                StageSelectControl?.MoveLeft();
            }
        }
        #endregion

        #region 編成・Start
        [UIAction]
        void OnClickDeckEditButton()
        {
            ViewDelegate.OnDeckButtonEdit(_selectedStageModel.MstStageId);
        }
        [UIAction]
        void OnInGameSpecialRuleButton()
        {
            ViewDelegate.OnInGameSpecialRuleTapped(_selectedStageModel.MstStageId);
        }

        [UIAction]
        void OnClickStageStartButton()
        {
            StageSelectViewDelegate.OnStartStageSelected(
                this,
                _selectedStageModel.MstStageId,
                _selectedStageModel.EndAt,
                _selectedStageModel.PlayableFlag,
                _selectedStageModel.StageConsumeStamina,
                _selectedStageModel.DailyClearCount,
                _selectedStageModel.DailyPlayableCount,
                null);
        }
        #endregion
    }
}
