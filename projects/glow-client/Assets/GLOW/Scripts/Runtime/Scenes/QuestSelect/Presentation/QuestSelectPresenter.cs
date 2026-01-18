using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.QuestSelect.Domain;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.QuestSelect.Presentation
{
    public sealed class QuestSelectPresenter : IQuestSelectViewDelegate
    {
        IQuestSelectViewDelegate _questSelectViewDelegateImplementation;
        [Inject] QuestSelectUseCase UseCases { get; }
        [Inject] SelectQuestUseCase SelectQuestUseCase { get; }
        [Inject] QuestSelectViewController ViewController { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] QuestSelectViewController.Argument Arg { get; }

        QuestSelectUseCaseModel _questSelectUseCaseModel;

        void IQuestSelectViewDelegate.OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(QuestSelectPresenter), nameof(IQuestSelectViewDelegate.OnViewDidLoad));

            _questSelectUseCaseModel = UseCases.GetQuestSelectUseCaseModels(Arg.InitialSelectedQuestId);

            var stageDataListViewModel = QuestSelectViewModelTranslator.CreateQuestSelectViewModel(_questSelectUseCaseModel);
            ViewController.Initialize(stageDataListViewModel);
        }

        void IQuestSelectViewDelegate.OnDifficultySelected(MasterDataId mstGroupQuestId, Difficulty difficulty)
        {
            var difficultyModel = GetDifficultyModel(mstGroupQuestId, difficulty);

            if (difficultyModel.DifficultyOpenStatus != QuestDifficultyOpenStatus.Released)
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_013);
                CommonToastWireFrame.ShowScreenCenterToast(difficultyModel.ReleaseRequiredSentence.Value);
                return;
            }

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            ViewController.SelectDifficulty(difficulty);
        }

        void IQuestSelectViewDelegate.ApplySelectedQuest(MasterDataId mstQuestId)
        {
            // クエストが開放されてなければ何もせず閉じる
            var selectedDifficultyModel = GetDifficultyModel(mstQuestId);

            if (selectedDifficultyModel.IsEmpty() ||
                selectedDifficultyModel.DifficultyOpenStatus == QuestDifficultyOpenStatus.NotRelease)
            {
                HomeViewNavigation.TryPop();
                return;
            }

            // クエストを選択して閉じる
            var isSelectedQuestChanged = SelectQuestUseCase.SelectQuest(selectedDifficultyModel.MstQuestId);
            HomeViewNavigation.TryPop();

            // コールバック
            if (isSelectedQuestChanged)
            {
                Arg.StageSelected?.Invoke();
            }
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

        QuestSelectDifficultyUseCaseModel GetDifficultyModel(MasterDataId mstQuestId)
        {
            var selectedDifficultyModel = _questSelectUseCaseModel.Items
                .SelectMany(item => item.DifficultyItems)
                .FirstOrDefault(
                    difficultyItem => difficultyItem.MstQuestId == mstQuestId,
                    QuestSelectDifficultyUseCaseModel.Empty);

            return selectedDifficultyModel;
        }
    }
}
