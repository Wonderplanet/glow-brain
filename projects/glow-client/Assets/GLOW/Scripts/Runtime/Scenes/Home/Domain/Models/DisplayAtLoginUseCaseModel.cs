using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.ValueObjects;
using GLOW.Scenes.Notice.Domain.Model;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record DisplayAtLoginUseCaseModel(
        DisplayAtLoginFlag DisplayAnnouncementFlag,
        IReadOnlyList<NoticeModel> ShowNotices,
        PlayingTutorialSequenceFlag PlayingTutorialSequenceFlag)
    {
        public static DisplayAtLoginUseCaseModel Empty { get; } = new DisplayAtLoginUseCaseModel(
            DisplayAtLoginFlag.False,
            new List<NoticeModel>(),
            PlayingTutorialSequenceFlag.False);
    }
}