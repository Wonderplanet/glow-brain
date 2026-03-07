using System;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Modules.InAppReview.Domain.ValueObject;
using GLOW.Modules.InAppReview.Presentation;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.Home.Domain.UseCases;
using GLOW.Scenes.Home.Presentation.Translator;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.Home.Presentation.Views.HomeStageInfoView;
using GLOW.Scenes.HomePartyFormation.Presentation.Views;
using GLOW.Scenes.InGameSpecialRule.Presentation.ValueObjects;
using GLOW.Scenes.InGameSpecialRule.Presentation.Views;
using GLOW.Scenes.QuestSelect.Domain;
using GLOW.Scenes.QuestSelect.Presentation;
using GLOW.Scenes.QuestSelectList.Presentation;
using UnityEngine;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.MainQuestTop.Presentation
{
    public class MainQuestTopPresenter : IMainQuestTopViewDelegate, IHomeMainStageSelectViewDelegate
    {
        [Inject] HomeViewController HomeViewController{ get; }
        [Inject] MainQuestTopViewController ViewController { get; }
        [Inject] HomeStageSelectUseCases StageSelectUseCases { get; }
        [Inject] QuestSelectUseCase QuestSelectUseCase { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] GetCurrentPartyNameUseCase GetCurrentPartyNameUseCase { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IInAppReviewWireFrame InAppReviewWireFrame { get; }
        [Inject] HomeStageInfoUseCases HomeStageInfoUseCases { get; }
        [Inject] HomeStageInfoViewModelFactory HomeStageInfoViewModelFactory { get; }

        readonly HomeMainViewModelTranslator _viewModelTranslator = new();
        QuestSelectUseCaseModel _questSelectUseCaseModel;
        Difficulty _currentDifficulty;

        void IMainQuestTopViewDelegate.OnViewWillAppear()
        {
            // 選択パーティ表示
            UpdatePartyName();

            DoAsync.Invoke(ViewController.ActualView, async cancellationToken =>
            {
                //NOTE: クエスト・ステージ初期化
                var selectedStageDataModel = StageSelectUseCases.UpdateAndGetQuestUseCaseModel();
                var homeMainViewModel = _viewModelTranslator.TranslateToHomeMainQuestViewModel(selectedStageDataModel);

                _questSelectUseCaseModel = QuestSelectUseCase.GetQuestSelectUseCaseModels(homeMainViewModel.MstQuestId);
                _currentDifficulty = _questSelectUseCaseModel.CurrentSelectDifficulty;
                var questSelectViewModel = QuestSelectViewModelTranslator.CreateQuestSelectViewModel(_questSelectUseCaseModel);

                await ViewController.InitQuestViewModel(
                    homeMainViewModel,
                    questSelectViewModel,
                    cancellationToken);
            });
        }

        void IMainQuestTopViewDelegate.OnDeckButtonEdit(MasterDataId selectedMstStageId)
        {
            var tabArgument = new HomePartyTabViewController.Argument(selectedMstStageId,
                InGameContentType.Stage,
                EventBonusGroupId.Empty,
                MasterDataId.Empty);
            var tabController = ViewFactory
                .Create<HomePartyTabViewController, HomePartyTabViewController.Argument>(tabArgument);
            HomeViewNavigation.TryPush(tabController, HomeContentDisplayType.BottomOverlap);
        }

        // 副作用があることに注意
        void IMainQuestTopViewDelegate.OnDifficultySelectedAndUpdateRepository(MasterDataId mstGroupQuestId, Difficulty difficulty)
        {
            var difficultyModel = GetDifficultyModel(mstGroupQuestId, difficulty);
            if (difficulty == _currentDifficulty)
            {
                // すでに選択されている難易度が選択された場合は何もしない
                return;
            }

            if (difficultyModel.DifficultyOpenStatus != QuestDifficultyOpenStatus.Released)
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_013);
                CommonToastWireFrame.ShowScreenCenterToast(difficultyModel.ReleaseRequiredSentence.Value);
                return;
            }

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);

            // ---- 難易度ボタン周り
            // 副作用。順番依存1
            StageSelectUseCases.UpdateCurrentMstQuestId(difficultyModel.MstQuestId);
            _currentDifficulty = difficulty;
            // 副作用ここまで

            // 難易度ボタン更新
            ViewController.SelectDifficulty(difficulty);

            // 順番依存2
            UpdateSelectedStageFromUserProperty();
        }

        QuestSelectDifficultyUseCaseModel GetDifficultyModel(MasterDataId mstQuestGroupId, Difficulty difficulty)
        {
            var contentModel = _questSelectUseCaseModel.Items.FirstOrDefault(
                item => item.GroupId == mstQuestGroupId,
                QuestSelectContentUseCaseModel.Empty);

            var difficultyModel = contentModel.DifficultyItems.FirstOrDefault(
                difficultyModel => difficultyModel.Difficulty == difficulty,
                QuestSelectDifficultyUseCaseModel.Empty);

            return difficultyModel;
        }

        void IMainQuestTopViewDelegate.OnQuestSelected()
        {
            var controller = ViewFactory.Create<QuestSelectListViewController, QuestSelectListViewController.Argument>(
                new QuestSelectListViewController.Argument(UpdateSelectedStageFromUserProperty, MasterDataId.Empty));
            HomeViewNavigation.TryPush(controller, HomeContentDisplayType.BottomOverlap);

        }
        void UpdateSelectedStageFromUserProperty()
        {
            // NOTE: ユーザーの選択情報からステージ情報を取得して画面に反映する
            var selectedStageDataModel = StageSelectUseCases.UpdateAndGetQuestUseCaseModel();
            ViewController.SetQuestViewModel(
                _viewModelTranslator.TranslateToHomeMainQuestViewModel(selectedStageDataModel));
        }

        async UniTask IMainQuestTopViewDelegate.ShowQuestReleaseView(
            ShowQuestReleaseAnimation showQuestReleaseAnimation,
            InAppReviewFlag isInAppReviewDisplay,
            CancellationToken cancellationToken)
        {
            await UniTask.Delay(TimeSpan.FromSeconds(0.5f), cancellationToken: cancellationToken);
            var controller = ViewFactory.Create<QuestReleaseViewController, QuestReleaseViewController.Argument>(
                new QuestReleaseViewController.Argument(
                    showQuestReleaseAnimation.QuestImageAssetPath,
                    showQuestReleaseAnimation.QuestName,
                    showQuestReleaseAnimation.FlavorText));

            controller.OnAnimationCompletion = () =>
            {
                if (!isInAppReviewDisplay) return;

                InAppReviewWireFrame.RequestStoreReview(() => { });
            };

            // 閉じて完了としたいのでUniTaskCompletionSourceを使う
            var completionSource = new UniTaskCompletionSource();
            await using var _ = cancellationToken.Register(() => completionSource.TrySetCanceled());
            controller.OnCloseCompletion = () => { completionSource.TrySetResult(); };

            // IEscapeResponderの関係で、PresentModallyにしてない
            // 別にcontext管理しなくて良いのでShowで表示する
            HomeViewController.Show(controller);

            // 閉じるまで完了を待つ
            await completionSource.Task;
        }


        void IMainQuestTopViewDelegate.OnInGameSpecialRuleTapped(MasterDataId selectedMstStageId)
        {
            var controller = ViewFactory
                .Create<InGameSpecialRuleViewController, InGameSpecialRuleViewController.Argument>(
                    new InGameSpecialRuleViewController.Argument(
                        selectedMstStageId,
                        InGameContentType.Stage,
                        InGameSpecialRuleFromUnitSelectFlag.False));
            ViewController.PresentModally(controller);
        }

        void IMainQuestTopViewDelegate.OnClose()
        {
            HomeViewNavigation.TryPop();
        }

        void UpdatePartyName()
        {
            var partyName = GetCurrentPartyNameUseCase.GetCurrentPartyName();
            ViewController.SetCurrentPartyName(partyName);
        }

        void IHomeMainStageSelectViewDelegate.OnQuestInfoClicked(MasterDataId stageId)
        {
            //EventStageSelectPresenterと重複。必要あれば統合
            ShowHomeStageInfoView(stageId);
        }

        void ShowHomeStageInfoView(MasterDataId stageId)
        {
            DoAsync.Invoke(ViewController.ActualView, async ct =>
            {
                var homeStageInfoUseCaseModel = HomeStageInfoUseCases.GetHomeStageInfoUseCasesModel(stageId);
                var viewModel = HomeStageInfoViewModelFactory.ToHomeStageInfoViewModel(homeStageInfoUseCaseModel);

                // 画面がチラつくので1フレーム待つ
                await UniTask.Delay(1, cancellationToken: ct);

                var controller = ViewFactory.Create<HomeStageInfoViewController, HomeStageInfoViewController.Argument>(
                    new HomeStageInfoViewController.Argument(viewModel));
                controller.ReopenStageInfoAction = () =>
                {
                    if (HomeViewController.ViewContextController.CurrentContentType == HomeContentTypes.Main)
                    {
                        ShowHomeStageInfoView(stageId);
                    }
                };
                ViewController.PresentModally(controller);
            });
        }

        void IHomeMainStageSelectViewDelegate.OnQuestUnReleasedClicked(StageReleaseRequireSentence releaseRequireSentence)
        {
            CommonToastWireFrame.ShowScreenCenterToast(releaseRequireSentence.Value);
        }
    }
}
