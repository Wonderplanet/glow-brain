using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Modules.InAppReview.Domain.ValueObject;
using GLOW.Scenes.QuestContentTop.Domain.ValueObject;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record HomePlayableQuestUseCaseModel(
        MasterDataId MstQuestId,
        QuestName QuestName,
        QuestImageAssetPath QuestImageAssetPath,
        QuestLimitTime QuestLimitTime,
        MasterDataId InitialSelectStageMstStageId,
        IReadOnlyList<HomePlayableStageUseCaseModel> Stages,
        ShowStageReleaseAnimation ShowStageReleaseAnimation,
        ShowQuestReleaseAnimation ShowQuestReleaseAnimation,
        Difficulty CurrentDifficulty,
        InAppReviewFlag IsInAppReviewDisplay,
        DisplayTryStageTextFlag IsDisplayTryStageText)
    {
        public static HomePlayableQuestUseCaseModel Empty { get; } = new(
            MasterDataId.Empty,
            QuestName.Empty,
            QuestImageAssetPath.Empty,
            QuestLimitTime.Empty,
            MasterDataId.Empty,
            new List<HomePlayableStageUseCaseModel>(),
            ShowStageReleaseAnimation.Empty,
            ShowQuestReleaseAnimation.Empty,
            Difficulty.Normal,
            InAppReviewFlag.False,
            DisplayTryStageTextFlag.False
        );

        public bool IsEmpty() => ReferenceEquals(this, Empty);
    }

}
