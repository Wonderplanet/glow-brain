using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Quest;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Modules.InAppReview.Domain.ValueObject;
using GLOW.Scenes.QuestContentTop.Domain.ValueObject;
using QuestImageAssetPath = GLOW.Core.Domain.ValueObjects.Quest.QuestImageAssetPath;

namespace GLOW.Scenes.Home.Presentation.ViewModels
{
    public record HomeMainQuestViewModel(
        MasterDataId MstQuestId,
        MasterDataId MstGroupQuestId,
        QuestName QuestName,
        QuestImageAssetPath QuestImageAssetPath,
        QuestLimitTime QuestLimitTime,
        MasterDataId InitialSelectStageMstStageId,
        IReadOnlyList<HomeMainStageViewModel> Stages,
        ShowStageReleaseAnimation ShowStageReleaseAnimation,
        ShowQuestReleaseAnimation ShowQuestReleaseAnimation,
        Difficulty CurrentDifficulty,
        InAppReviewFlag IsInAppReviewDisplay,
        DisplayTryStageTextFlag IsDisplayTryStageText)
    {
        public static HomeMainQuestViewModel Empty { get; } = new(
            MasterDataId.Empty,
            MasterDataId.Empty,
            QuestName.Empty,
            QuestImageAssetPath.Empty,
            QuestLimitTime.Empty,
            MasterDataId.Empty,
            Array.Empty<HomeMainStageViewModel>(),
            ShowStageReleaseAnimation.Empty,
            ShowQuestReleaseAnimation.Empty,
            Difficulty.Normal,
            InAppReviewFlag.False,
            DisplayTryStageTextFlag.False
        );

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }

        public HomeMainStageViewModel GetInitSelectViewModel()
        {
            return Stages.First(s => s.MstStageId == InitialSelectStageMstStageId);
        }

    }
}
