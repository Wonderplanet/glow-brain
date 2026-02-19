using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.QuestContentTop.Domain.enums;
using GLOW.Scenes.QuestContentTop.Domain.Factory;
using GLOW.Scenes.QuestContentTop.Domain.UseCaseModel;
using Zenject;

namespace GLOW.Scenes.QuestContentTop.Domain
{
    public class QuestContentTopUseCase
    {
        [Inject] IQuestContentTopModelFactory QuestContentTopModelFactory { get; }
        [Inject] IQuestContentTopPvpModelFactory QuestContentTopPvpModelFactory { get; }
        [Inject] IContentTopAccessPreferenceRepository ContentTopAccessPreferenceRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        public QuestContentTopUseCaseModel UpdateAndGetQuestContentTopUseCaseModel()
        {
            // アクセス日時を更新
            ContentTopAccessPreferenceRepository.SaveAccessTime(TimeProvider.Now);
            return new QuestContentTopUseCaseModel(
                CreateEventQuestContentTopSectionUseCaseModel(),
                CreateDailyQuestContentTopSectionUseCaseModel(),
                CreateEndContentQuestContentTopSectionUseCaseModel(),
                CreatePvPQuestContentTopSectionUseCaseModel());
        }
        QuestContentTopSectionUseCaseModel CreateEventQuestContentTopSectionUseCaseModel()
        {
            return new QuestContentTopSectionUseCaseModel(
                QuestContentTopSectionType.Event,
                QuestContentTopModelFactory.CreateEventQuestContentTopItemUseCaseModels()
            );
        }
        QuestContentTopSectionUseCaseModel CreateDailyQuestContentTopSectionUseCaseModel()
        {
            return new QuestContentTopSectionUseCaseModel(
                QuestContentTopSectionType.Daily,
                QuestContentTopModelFactory.CreateEnhanceQuestContentTopItemUseCaseModels()
                );
        }

        QuestContentTopSectionUseCaseModel CreateEndContentQuestContentTopSectionUseCaseModel()
        {
            return new QuestContentTopSectionUseCaseModel(
                QuestContentTopSectionType.EndContent,
                QuestContentTopModelFactory.CreateAdventBattleUseCaseModelsWithBeforeOpen()
                );
        }

        QuestContentTopSectionUseCaseModel CreatePvPQuestContentTopSectionUseCaseModel()
        {
            return new QuestContentTopSectionUseCaseModel(
                QuestContentTopSectionType.Pvp,
                QuestContentTopPvpModelFactory.CreatePvpQuestContentTopElementUseCaseModels()
            );
        }
    }
}
