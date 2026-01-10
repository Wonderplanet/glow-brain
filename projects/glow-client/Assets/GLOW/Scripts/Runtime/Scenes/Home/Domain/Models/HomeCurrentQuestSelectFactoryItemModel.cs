using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record HomeCurrentQuestSelectFactoryItemModel(
        MstStageModel MstStageModel,
        MstStageEventSettingModel MstStageEventSettingModel,
        UserStageEventModel UserStageEventModel,
        StagePlayableFlag StagePlayable);
}
