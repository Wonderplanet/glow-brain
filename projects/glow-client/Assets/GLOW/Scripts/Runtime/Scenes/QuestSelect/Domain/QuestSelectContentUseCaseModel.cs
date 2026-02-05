using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models.Campaign;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Extensions;
using GLOW.Scenes.Home.Domain.ValueObjects;

namespace GLOW.Scenes.QuestSelect.Domain
{

    public record QuestSelectContentUseCaseModel(
        MasterDataId GroupId,
        QuestName QuestName,
        Difficulty Difficulty,
        QuestAssetKey AssetKey,
        QuestFlavorText FlavorText,
        QuestSelectContentUnlockDescriptionStatus RequiredSentenceStatus,
        QuestOpenStatus Status,
        NewQuestFlag NewQuestExists,
        IReadOnlyList<QuestSelectDifficultyUseCaseModel> DifficultyItems,
        IReadOnlyList<CampaignModel> NormalCampaignModels,
        IReadOnlyList<CampaignModel> HardCampaignModels,
        IReadOnlyList<CampaignModel> ExtraCampaignModels)
    {
        public static QuestSelectContentUseCaseModel Empty { get; } = new QuestSelectContentUseCaseModel(
            new MasterDataId(string.Empty),
            new QuestName(string.Empty),
            Difficulty.Normal,
            new QuestAssetKey(string.Empty),
            new QuestFlavorText(string.Empty),
            QuestSelectContentUnlockDescriptionStatus.Empty,
            QuestOpenStatus.NotOpenQuest,
            NewQuestFlag.False,
            new List<QuestSelectDifficultyUseCaseModel>(),
            new List<CampaignModel>(),
            new List<CampaignModel>(),
            new List<CampaignModel>());

        public bool IsEmpty => ReferenceEquals(this, Empty);

        public QuestSelectContentUseCaseModel CopyWithUpdatedCurrentDifficulty(Difficulty difficulty)
        {
            return this with { Difficulty = difficulty };
        }
        
        public QuestSelectDifficultyUseCaseModel GetCurrentDifficultyModel()
        {
            return DifficultyItems.FirstOrDefault(
                x => x.Difficulty == Difficulty,
                QuestSelectDifficultyUseCaseModel.Empty);
        }
    };
}
