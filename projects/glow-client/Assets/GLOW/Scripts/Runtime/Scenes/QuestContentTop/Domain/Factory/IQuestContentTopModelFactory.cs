using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.QuestContentTop.Domain.UseCaseModel;

namespace GLOW.Scenes.QuestContentTop.Domain
{
    public interface IQuestContentTopModelFactory
    {
        IReadOnlyList<QuestContentTopElementUseCaseModel> CreateEventQuestContentTopItemUseCaseModels();
        IReadOnlyList<QuestContentTopElementUseCaseModel> CreateEnhanceQuestContentTopItemUseCaseModels();
        IReadOnlyList<QuestContentTopElementUseCaseModel> CreateAdventBattleUseCaseModelsWithBeforeOpen();
        IReadOnlyDictionary<MasterDataId, NotificationBadge> CreateEventMissionBadgeDictionary();
    }
}
