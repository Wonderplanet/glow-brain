using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using UIKit;

namespace GLOW.Scenes.Home.Presentation.Presenters
{
    public interface IStageSelectViewDelegate
    {
        void OnStartStageSelected(
            UIViewController viewController,
            MasterDataId mstStageId,
            UnlimitedCalculableDateTimeOffset mstStageEndAt,
            StagePlayableFlag playableFlag,
            StageConsumeStamina stageConsumeStamina,
            StageClearCount dailyClearCount,
            ClearableCount dailyPlayableCount,
            Action onStageStart);
    }
}
