using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.QuestSelect.Domain;
using GLOW.Scenes.QuestSelect.Presentation;
using Zenject;

namespace GLOW.Scenes.QuestSelectList.Presentation
{
    public class QuestSelectListPresenter : IQuestSelectListViewDelegate
    {
        [Inject] QuestSelectListViewController.Argument Arg { get; }
        [Inject] QuestSelectListViewController ViewController { get; }
        [Inject] IHomeViewNavigation HomeViewNavigation { get; }
        [Inject] QuestSelectUseCase UseCases { get; }
        [Inject] SelectQuestUseCase SelectQuestUseCase { get; }

        QuestSelectUseCaseModel _questSelectUseCaseModel;

        void IQuestSelectListViewDelegate.OnViewDidLoad()
        {
            _questSelectUseCaseModel = UseCases.GetQuestSelectUseCaseModels(Arg.InitialSelectedQuestId);

            var stageDataListViewModel = QuestSelectViewModelTranslator.CreateQuestSelectViewModel(_questSelectUseCaseModel);
            ViewController.SetUpView(stageDataListViewModel);
        }

        void IQuestSelectListViewDelegate.ApplySelectedQuest(MasterDataId mstQuestId)
        {
            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            // クエストを選択して閉じる(副作用あり)
            var isSelectedQuestChanged = SelectQuestUseCase.SelectQuest(mstQuestId);
            HomeViewNavigation.TryPop();

            // コールバック
            if (isSelectedQuestChanged)
            {
                Arg.StageSelected?.Invoke();
            }
        }


        void IQuestSelectListViewDelegate.OnClose()
        {
            HomeViewNavigation.TryPop();
        }
    }
}
